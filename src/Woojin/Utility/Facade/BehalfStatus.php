<?php

namespace Woojin\Utility\Facade;

use Doctrine\ORM\EntityManager;
use Woojin\Utility\Avenue\Avenue;

class BehalfStatus
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * 讓 Controller 不用和 $em 依賴的取得實體方法
     * 
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport
     */
    public function find($id)
    {
        return $this->em->find('WoojinGoodsBundle:BehalfStatus', $id);
    }

    public function getRepo()
    {
        return $this->em->getRepository('WoojinGoodsBundle:BehalfStatus');
    }

    public function findNotConfirm()
    {
        return $this->find(Avenue::BS_NOT_CONFIRM);
    }

    public function findFirstConfirm()
    {
        return $this->find(Avenue::BS_FIRST_CONFIRM);
    }

    public function findPaid()
    {
        return $this->find(Avenue::BS_PAID);
    }

    public function findPurIn()
    {
        return $this->find(Avenue::BS_PURIN);
    }

    public function findPurOut()
    {
        return $this->find(Avenue::BS_PUROUT);
    }

    public function findSecondConfirm()
    {
        return $this->find(Avenue::BS_SECOND_CONFIRM);
    }

    public function findCancel()
    {
        return $this->find(Avenue::BS_CANCEL);
    }

    public function findAvenueCancel()
    {
        return $this->find(Avenue::BS_AVENUE_CANCEL);
    }

    public function findChargeBack()
    {
        return $this->find(Avenue::BS_CHARGE_BACK);
    }
}