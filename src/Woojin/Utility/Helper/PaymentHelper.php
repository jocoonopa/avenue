<?php

namespace Woojin\Utility\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Woojin\OrderBundle\Entity\Invoice;

use Woojin\Utility\Helper\BenefitHelper;

class PaymentHelper implements IHelper
{
    protected $benefitHelper;

    public function setBenefitHelper(BenefitHelper $benefitHelper)
    {
        $this->benefitHelper = $benefitHelper;
    }

    public function getCustomIdFromSession(Session $session)
    {
        return json_decode($session->get('custom'))->id;
    }

    public function getValidCustom($em, $request, $id)
    {
        /**
         * Custom
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $em->find('WoojinOrderBundle:Custom', $id);
        // return $custom;
        return ($custom);
    }

    public function getValidInvoice($em, $id, $customId)
    {
        $invoice = $em->getRepository('WoojinOrderBundle:Invoice')->findNotYeyToAllPay($id, $customId);

        if (!$invoice) {
            throw new \Exception('沒有需要處理的訂單!');
        }

        return $invoice;
    }

    public function buildAllPayInfo(Invoice $invoice, array &$items, &$price)
    {
        foreach ($invoice->getOrders() as $order) {
            $product = $order->getGoodsPassport();

            $item = $product->genItem();
            $item['Price'] = $order->getRequired();
            $items[] = $item;
            
            // 增加訂單總金額
            $price += $order->getRequired();
        }

        return $this;
    }

    public function dropFail(array &$products, array &$failedProducts)
    {
        foreach ($products as $key => $product) {
            if (!$product->isValidToShow() || !$product->getIsAllowCreditCard()) {
                $failedProducts[] = $product;

                unset($products[$key]);
            }
        }

        return $this;
    }

    public function getCartTotalCost(array $products)
    {
        $total = 0;

        foreach ($products as $product) {
            $total += $product->getPromotionPrice(true);
        }

        return $total;
    }
}