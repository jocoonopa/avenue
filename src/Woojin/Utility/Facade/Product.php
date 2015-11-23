<?php

namespace Woojin\Utility\Facade;

use Doctrine\ORM\EntityManager;

use Woojin\GoodsBundle\Entity\GoodsPassport;

class Product
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
        return $this->em->find('WoojinGoodsBundle:GoodsPassport', $id);
    }

    public function getRepo()
    {
        return $this->em->getRepository('WoojinGoodsBundle');
    }
}