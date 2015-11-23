<?php

namespace Woojin\Service\Business;

use Doctrine\ORM\EntityManager; 

use Woojin\OrderBundle\Entity\Invoice;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\Utility\Avenue\Avenue;

/**
 * 一般進貨流程訂單處理
 */
class Stock
{
    protected $order;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function init(GoodsPassport $product)
    {
        $this->order = new Orders;
        $this->order
            ->setGoodsPassport($product)
            ->setPayType($this->em->find('WoojinOrderBundle:PayType', Avenue::OK_IN))
            ->setKind($this->em->find('WoojinOrderBundle:OrdersKind', Avenue::OS_COMPLETE))
            ->setStatus($this->em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE))
            ->setRequired($product->getCost())
            ->setPaid($product->getCost())
        ;

        $this->em->persist($this->order);
        $this->em->flush();

        return $this;
    }

    public function cancel()
    {

    }

    public function reverse()
    {

    }

    public function transToConsign()
    {

    }

    public function transToPurchase()
    {

    }

    public function getProduct()
    {

    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getCustom()
    {

    }

    protected function genSn()
    {

    }
}