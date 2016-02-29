<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\Helper\PaymentHelper;
use Woojin\OrderBundle\Entity\Invoice;

use Avenue\Adapter\Adapter;

class InvoiceController extends Controller
{
    /**
     * 退款
     * 
     * @Route("/invoice/{id}/chargeback", requirements={"id"="\d+"}, name="front_invoice_chargeback")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     */
    public function chargeBackAction(Invoice $invoice, Request $request)
    {
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('invoice', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $em = $this->getDoctrine()->getManager();

        $this->checkOwnInvoice($invoice, $request, $em);

        $em->getConnection()->beginTransaction();

        try {
            $invoice->setStatus(Avenue::IV_NOTIFY_CANCEL);
            $em->persist($invoice);

            $orders = $invoice->getOrders();

            //$orderCancel = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_CANCEL);

            //$productMoving = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_MOVING);

            // $iterator = $orders->getIterator();
            // while ($iterator->valid()) {
            //     $order = $iterator->current();
            //     $order->setStatus($orderCancel);

            //     $em->persist($order);

            //     $iterator->next();
            // }

            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            // Rollback the failed transaction attempt
            $em->getConnection()->rollback();

            throw $e;
        }

        $logger = $this->get('logger.custom');
        $logger->write($invoice->getCustom(), array(
            'entity' => 'invoice',
            'id' => $invoice->getId(),
            'method' => 'cancel',
            'url' => 'front_invoice_cancel'
        ));

        $notifier = $this->get('avenue.notifier');
        $notifier->back($invoice);

        $this->get('session')->getFlashBag()->add('success', '訂單編號: ' . $invoice->getSn() . '退款請求已發送，我們會儘速為您處理!');

        return $this->redirect($this->get('router')->generate('front_profile_orders'));
    }

    /**
     * 取消訂單
     * 
     * @Route("/invoice/{id}/cancel", requirements={"id"="\d+"}, name="front_invoice_cancel")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     */
    public function cancelAction(Invoice $invoice, Request $request)
    {
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('invoice', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $em = $this->getDoctrine()->getManager();

        $this->checkOwnInvoice($invoice, $request, $em);

        $em->getConnection()->beginTransaction();

        try {
            $invoice->setStatus(Avenue::IV_BACK_DONE);
            $em->persist($invoice);

            $orders = $invoice->getOrders();

            $orderCancel = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_CANCEL);

            $productOnsale = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE);

            $iterator = $orders->getIterator();
            while ($iterator->valid()) {
                $order = $iterator->current();
                $order->setStatus($orderCancel);
                $em->persist($order);

                $product = $order->getGoodsPassport();
                $product->setStatus($productOnsale);
                
                $em->persist($product);

                $iterator->next();
            }

            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            // Rollback the failed transaction attempt
            $em->getConnection()->rollback();

            throw $e;
        }

        $logger = $this->get('logger.custom');
        $logger->write($invoice->getCustom(), array(
            'entity' => 'invoice',
            'id' => $invoice->getId(),
            'method' => 'cancel',
            'url' => 'front_invoice_cancel'
        ));

        $this->get('session')->getFlashBag()->add('success', '訂單編號: ' . $invoice->getSn() . '已經取消!');

        // 發送訂單取消通知
        $notifier = $this->get('avenue.notifier');
        $notifier->cancelOrder($invoice);

        return $this->redirect($this->get('router')->generate('front_profile_orders'));
    }

    protected function checkOwnInvoice(Invoice $invoice, Request $request, $em)
    {
        $paymentHelper = new PaymentHelper;

        $id = $this->getCurrentCustomId($paymentHelper);

        if (!$invoice->isOwnInvoice($id)) {
            throw new AccessDeniedHttpException('Not your own invoice');
        }

        return true;
    }

    protected function getCurrentCustomId(PaymentHelper $paymentHelper)
    {
        $session = $this->get('session');
        
        return $paymentHelper->getCustomIdFromSession($session); 
    }
}