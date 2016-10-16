<?php

namespace Woojin\Utility\Avenue;

use Woojin\StoreBundle\Entity\Auction;

class ShippingCalculator
{
    const MIN_COST = 30;

    public static $map = array(
        100001 => NULL,
        50001 => 300,
        20001 => 150,
        10001 => 120,
        5001 => 90,
        2001 => 60,
    ) ;

    public static function getCost($mixed)
    {
        return $mixed instanceof Auction ? static::getCostByAuction($mixed) : static::getCostByPrice($mixed);
    }

    protected static function getCostByAuction(Auction $auction)
    {
        return static::getCostByPrice($auction->getPrice());
    }

    protected static function getCostByPrice($price)
    {
        foreach (static::$map as $thread => $cost) {
            if ($thread <= $price) {
                return $cost;
            }
        }

        return static::MIN_COST;
    }
}
