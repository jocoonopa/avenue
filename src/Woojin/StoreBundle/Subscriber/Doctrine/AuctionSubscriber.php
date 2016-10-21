<?php

namespace Woojin\StoreBundle\Subscriber\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\Utility\Avenue\Avenue;

class AuctionSubscriber implements EventSubscriber
{
	protected $em;

    public function getSubscribedEvents()
    {
        return array(
            'postUpdate'
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
    	$auction = $args->getEntity();

        if (!$auction instanceof Auction) {
           return;
        }

        $this->em = $args->getEntityManager();

        if ($this->isNeedToUpdateFeedbackOrder($auction)) {
        	$this->updateProductFeedBackOrderToComplete($auction);
        }

        return $args;
    }

    protected function isNeedToUpdateFeedbackOrder(Auction $auction)
    {
    	return Auction::PROFIT_STATUS_ASSIGN_COMPLETE === $auction->getProfitStatus() && true === $auction->getProduct()->getIsAllowAuction();
    }

    protected function updateProductFeedBackOrderToComplete(Auction $auction)
    {
    	$order = $auction->getProduct()->getFeedBackOrder();

    	if (!$order instanceof Orders) {
    		return;
    	}

    	$order
            ->setRequired(0)
            ->setOrgRequired(0)
            ->setPaid(0)
            ->setOrgPaid(0)
            ->setStatus($this->em->getRepository('WoojinOrderBundle:OrdersStatus')->find(Avenue::OS_COMPLETE))
        ;

    	$this->em->persist($order);
    	$this->em->flush();
    }
}
