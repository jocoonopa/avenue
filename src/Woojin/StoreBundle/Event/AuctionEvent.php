<?php

namespace Woojin\StoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Woojin\StoreBundle\Entity\Auction;

abstract class AuctionEvent extends Event
{
    protected $auction;
    protected $options;

    public function setAuction(Auction $auction)
    {
        $this->auction = $auction;

        return $this;
    }

    public function getAuction()
    {
        return $this->auction;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}