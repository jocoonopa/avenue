<?php

namespace Woojin\Utility\Que\Sell;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\OrdersStatus;
use Woojin\OrderBundle\Entity\OrdersKind;
use Woojin\OrderBundle\Entity\PayType;
use Woojin\OrderBundle\Entity\Custom;
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\OrderBundle\Entity\Ope;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Entity\GoodsStatus;

use Woojin\Utility\Avenue\Avenue;

class Seller
{
    protected $container;

    protected $context;

    protected $benefitHelper;

    protected $em;

    public function __construct(ContainerInterface $container, EntityManager $em, SecurityContext $context) 
    {
        $this->context = $context;

        $this->container = $container;

        $this->em = $em;
    }

    /**
     * 傳入欲販售的商品，開始販售流程
     *
     * @param  \Woojin\OrderBundle\Entity\Custom $custom
     * @param  array $products
     * 
     * @return  $this
     */
    public function sell(Custom $custom, $products, Request $request)
    {        
        /**
         * 官網店長
         * 
         * @var User
         */
        $this->user = $this->em->find('WoojinUserBundle:User', Avenue::WEB_CHIEF);

        /**
         * 客戶
         * 
         * @var Custom
         */
        $this->custom = $custom;

        $this->em->getConnection()->beginTransaction();

        try {
            /**
             * 新開發票
             * 
             * @var Invoice
             */
            $this->invoice = $this->createInvoice();

            $this->em->persist($this->invoice); 

            $this->checkout($products);    

            $this->em->flush();

            $this->invoice
                ->setSn($this->invoice->genSn($this->user))
                ->setIsAllPay(true)
            ;

            // 客戶資料處理流程:
            // 
            // 先判斷 isDiffAddress 
            // 若為真:
            //    客戶地址不要更新
            //
            // 若為否:
            //    客戶地址更新
            // 
            // 其他資訊塞入客戶實體
            // flush()
            $post = $request->request;

            // 判斷有無更改地址，有的話用新地址
            $this->custom
                ->setName($post->get('name'))
                ->setEmail($post->get('email'))
                ->setMobil($post->get('phone'))
            ;

            if ($post->get('isDiffAddress') != 1 || $this->custom->getCounty() == '未填寫') {
                $this->custom
                    ->setCounty($post->get('county'))
                    ->setDistrict($post->get('district'))
                    ->setAddress($post->get('address'))
                ;
            }

            $this->em->persist($this->custom);

            $this->invoice
                ->setCounty($post->get('county'))
                ->setDistrict($post->get('district'))
                ->setAddress($post->get('address'))
                ->setIsDiffAddress(true)
            ;

            $this->em->persist($this->invoice);
            $this->em->flush();

            // 購物金處理
            $this->benefitHelper = $this->container->get('helper.benefit');
            $this->benefitHelper->carve($this->invoice);

            // Yahoo同步刪除
            $adapter = $this->container->get('yahoo.syncer');
            $adapter->delete($products);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();

            throw $e;
        }
        
        return $this->invoice;
    }

    protected function createInvoice()
    {
        $this->invoice = new Invoice();
        $this->invoice
            ->setCreditInstallment(0)
            ->setCustom($this->custom)
            ->setStore($this->user->getStore())
            ->setHasPrint(false)
        ;
        
        return $this->invoice;
    }

    protected function checkout($products)
    {
        $this->saleStatus = $this->em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_SOLDOUT);
        $this->completeStatus = $this->em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE);
        $this->kind = $this->em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_OFFICIAL);
        $this->paytype = $this->em->find('WoojinOrderBundle:PayType', Avenue::PT_CASH);
        
        foreach ($products as $product) {
            $this->pick($product);
        }
    }

    protected function pick($product)
    {
        // 更新商品狀態
        $product->setStatus($this->saleStatus);

        $per = (100 - $this->invoice->getInvoiceSurcharge())/100;

        $order = new Orders;
        $order
            ->setPaid(round($product->getPromotionPrice(true) * $per))
            ->setRequired(round($product->getPromotionPrice(true) * $per))
            ->setOrgPaid($product->getPromotionPrice(true))
            ->setOrgRequired($product->getPromotionPrice(true))
            ->setPayType($this->paytype)
            ->setKind($this->kind)
            ->setStatus($this->completeStatus)
            ->setGoodsPassport($product)
            ->setInvoice($this->invoice)
            ->setCustom($this->custom)
        ;

        $ope = new Ope;
        $ope
            ->setOrders($order)
            ->setUser($this->user)
            ->setAct('官網售出')
            ->setDatetime(new \DateTime())
        ;

        $this->em->persist($product);
        $this->em->persist($order);
        $this->em->persist($ope);
    }
}