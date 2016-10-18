<?php

namespace Woojin\StoreBundle\Subscriber\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class AuctionSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'postPersist'
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
    }
}
