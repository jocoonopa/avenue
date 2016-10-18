<?php

namespace Woojin\StoreBundle\Subscriber\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\StoreBundle\Entity\AuctionPayment;

class AuctionPaymentSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateAuctionProfitStatus($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateAuctionProfitStatus($args);
    }

    protected function updateAuctionProfitStatus(LifecycleEventArgs $args)
    {
        $payment = $args->getEntity();

        if (!$payment instanceof AuctionPayment) {
           return;
        }

        $em = $args->getEntityManager();
        $auction = $payment->getAuction();

        $profitStatus = 0 === $auction->getOwe() ? Auction::PROFIT_STATUS_PAY_COMPLETE : Auction::PROFIT_STATUS_NOT_PAID_YET;

        $auction->setProfitStatus($profitStatus);

        $em->persist($auction);
        $em->flush();
    }
}
