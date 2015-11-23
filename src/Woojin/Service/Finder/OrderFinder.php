<?php

namespace Woojin\Service\Finder;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class OrderFinder extends Finder
{
    protected function initQbSelect()
    {
        $this->qb
            ->select(array('o', 'g'))
            ->from('WoojinOrderBundle:Orders', 'o')
            ->leftJoin('o.goods_passport', 'g')
            ->leftJoin('g.pattern', 'p')
            ->leftJoin('g.brand', 'b')
            ->leftJoin('g.custom', 'c')
            ->leftJoin('o.custom', 'co')
            ->leftJoin('o.opes', 'e')
        ;

        return $this;
    }
}