<?php

namespace Woojin\StoreBundle\Subscriber\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\StoreBundle\Entity\AuctionPayment;

class AuctionPaymentSubscriber implements EventSubscriber
{
    protected $em;

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateAuctionProfitStatus($args, true);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateAuctionProfitStatus($args);
    }

    protected function updateAuctionProfitStatus(LifecycleEventArgs $args, $isCreate = false)
    {
        $payment = $args->getEntity();

        if (!$payment instanceof AuctionPayment) {
           return;
        }

        $this->em = $args->getEntityManager();

        $this->updateAuctionProfitStatusAndPaidCompleteAt($payment, $isCreate);
    }

    protected function updateAuctionProfitStatusAndPaidCompleteAt(AuctionPayment $payment, $isCreate = false)
    {
        /**
         * @var \Woojin\StoreBundle\Entity\Auction
         */
        $auction = $payment->getAuction();

        /**
         * 客戶尚未結清的餘額
         *
         * @var integer
         */
        $owe = $auction->getOwe() - (true === $isCreate ? $payment->getAmount() : 0);

        /**
         * Auction 的毛利狀態
         *
         * @var integer
         */
        $profitStatus = 0 === $owe ? Auction::PROFIT_STATUS_PAY_COMPLETE : Auction::PROFIT_STATUS_NOT_PAID_YET;

        $auction->setProfitStatus($profitStatus);

        if (Auction::PROFIT_STATUS_NOT_PAID_YET === $auction->getProfitStatus()) {
            $auction->setPaidCompleteAt(NULL);
        } else {
            $orgPayment = $this->em->getRepository('WoojinStoreBundle:Auction')->fetchLatestPaymentByAuction($auction);
            $paidCompleteAt = $orgPayment->getPaidAt() >= $payment->getPaidAt() ? $orgPayment->getPaidAt() : $payment->getPaidAt();

            $auction->setPaidCompleteAt($paidCompleteAt);
        }

        $this->em->persist($auction);
        $this->em->flush();
    }
}
