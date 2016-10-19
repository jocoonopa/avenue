<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\QueryBuilder;

class AuctionCriteria
{
    protected $criteria;
    protected $qb;

    public function __construct(array $criteria, QueryBuilder $qb)
    {
        $this->setCriteria($criteria)->setQb($qb);

        $this->proc();
    }

    protected function proc()
    {
        $this
            ->handleCreateStore()
            ->handleAuctionStatus()
            ->handleAuctionProfitStatus()
            ->handleBuyerMobil()
            ->handleSellerMobil()
            ->handleCreaterUsername()
            ->handleBsserUsername()
            ->handleCreateAt()
            ->handleSoldAt()
            ->handlePaidCompleteAt()
        ;
    }

    protected function handleAuctionStatus()
    {
        if (!array_key_exists('auction_statuses', $this->criteria) || empty($this->criteria['auction_statuses'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('a.status', $this->criteria['auction_statuses']));

        return $this;
    }

    protected function handleAuctionProfitStatus()
    {
        if (!array_key_exists('auction_profit_statuses', $this->criteria) || empty($this->criteria['auction_profit_statuses'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('a.profitStatus', $this->criteria['auction_profit_statuses']));

        return $this;
    }

    protected function handleCreateStore()
    {
        if (!array_key_exists('stores', $this->criteria) || empty($this->criteria['stores'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('a.createStore', $this->criteria['stores']));

        return $this;
    }

    protected function handleBuyerMobil()
    {
        if (!array_key_exists('buyer_mobil', $this->criteria) || empty($this->criteria['buyer_mobil'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('buyer.mobil', '?1'));
        $this->qb->setParameter(1, $this->convert($this->criteria['buyer_mobil']));

        return $this;
    }

    protected function handleSellerMobil()
    {
        if (!array_key_exists('seller_mobil', $this->criteria) || empty($this->criteria['seller_mobil'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('seller.mobil', '?2'));
        $this->qb->setParameter(2, $this->convert($this->criteria['seller_mobil']));

        return $this;
    }

    protected function handleCreaterUsername()
    {
        if (!array_key_exists('creater_username', $this->criteria) || empty($this->criteria['creater_username'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('creater.username', '?8'));
        $this->qb->setParameter(8, $this->convert($this->criteria['creater_username']));

        return $this;
    }

    protected function handleBsserUsername()
    {
        if (!array_key_exists('bsser_username', $this->criteria) || empty($this->criteria['bsser_username'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->in('bsser.username', '?3'));
        $this->qb->setParameter(3, $this->convert($this->criteria['bsser_username']));

        return $this;
    }

    protected function handleCreateAt()
    {
        if (!array_key_exists('create_at', $this->criteria) || empty($this->criteria['create_at'])) {
            return $this;
        }

        return $this->handleCreateAtStart()->handleCreateAtEnd();
    }

    protected function handleCreateAtStart()
    {
        if (!array_key_exists('start', $this->criteria['create_at']) || empty($this->criteria['create_at']['start'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->gte('a.createAt', '?4'));
        $this->qb->setParameter(4, "{$this->criteria['create_at']['start']} 00:00:00");

        return $this;
    }

    protected function handleCreateAtEnd()
    {
        if (!array_key_exists('end', $this->criteria['create_at']) || empty($this->criteria['create_at']['end'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->lte('a.createAt', '?5'));
        $this->qb->setParameter(5, "{$this->criteria['create_at']['end']} 24:00:00");

        return $this;
    }

    protected function handleSoldAt()
    {
        if (!array_key_exists('sold_at', $this->criteria) || empty($this->criteria['sold_at'])) {
            return $this;
        }

        return $this->handleSoldAtStart()->handleSoldAtEnd();
    }

    protected function handleSoldAtStart()
    {
        if (!array_key_exists('start', $this->criteria['sold_at']) || empty($this->criteria['sold_at']['start'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->gte('a.soldAt', '?6'));
        $this->qb->setParameter(6, "{$this->criteria['sold_at']['start']} 00:00:00");

        return $this;
    }

    protected function handleSoldAtEnd()
    {
        if (!array_key_exists('end', $this->criteria['sold_at']) || empty($this->criteria['sold_at']['end'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->lte('a.soldAt', '?7'));
        $this->qb->setParameter(7, "{$this->criteria['sold_at']['end']} 24:00:00");

        return $this;
    }

    protected function handlePaidCompleteAt()
    {
        if (!array_key_exists('paid_complete_at', $this->criteria) || empty($this->criteria['paid_complete_at'])) {
            return $this;
        }

        return $this->handlePaidCompleteAtStart()->handlePaidCompleteAtEnd();
    }

    protected function handlePaidCompleteAtStart()
    {
        if (!array_key_exists('start', $this->criteria['paid_complete_at']) || empty($this->criteria['paid_complete_at']['start'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->gte('a.paidCompleteAt', '?9'));
        $this->qb->setParameter(9, "{$this->criteria['paid_complete_at']['start']} 00:00:00");

        return $this;
    }

    protected function handlePaidCompleteAtEnd()
    {
        if (!array_key_exists('end', $this->criteria['paid_complete_at']) || empty($this->criteria['paid_complete_at']['end'])) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->lte('a.paidCompleteAt', '?10'));
        $this->qb->setParameter(10, "{$this->criteria['paid_complete_at']['end']} 24:00:00");

        return $this;
    }

    protected function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    protected function setQb(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }

    public function getQb()
    {
        return $this->qb;
    }

    protected function convert($str, $deli = ',')
    {
        return is_string($str) ? explode($deli, $str) : array();
    }
}
