<?php

namespace Woojin\Service\Finder;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class ProductFinder extends Finder
{
    protected function initQbSelect()
    {
        $this->qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.custom', 'c')
            ->leftJoin('g.pattern', 'p')
            ->leftJoin('g.brand', 'b')
            ->leftJoin('g.orders', 'o')
            ->leftJoin('o.custom', 'co')
            ->leftJoin('o.opes', 'e')
        ;

        return $this;
    }
}