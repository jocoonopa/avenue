<?php

namespace Woojin\StoreBundle\Tests\Entity;

use Mockery as m;

use Woojin\StoreBundle\Entity\Auction;
use Woojin\Utility\Avenue\ShippingCalculator;

/**
 * It's highly recommended that a functional test only tests the response
 */
class AuctionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->product = m::mock('Woojin\GoodsBundle\Entity\GoodsPassport')->makePartial();
    }

    public function testCalculatePercentage()
    {
        $this->product->shouldReceive('getCustom')->once()->andReturn(NULL);
        $this->product->shouldReceive('getIsAlanIn')->once()->andReturn(false);
        $this->product->shouldReceive('getBsoCustomPercentage')->andReturn(Auction::DEFAULT_CUSTOM_PERCENTAGE);

        $this->assertSame(array(0, 0.5, 0.5), Auction::calculatePercentage($this->product));

        $this->product->shouldReceive('getCustom')->once()->andReturn('1');
        $this->product->shouldReceive('getIsAllowAuction')->once()->andReturn(true);
        $this->product->shouldReceive('getIsAlanIn')->once()->andReturn(true);
        $this->product->shouldReceive('getBsoCustomPercentage')->andReturn(Auction::DEFAULT_CUSTOM_PERCENTAGE);

        $this->assertSame(array(0.8, 0, 0.2), Auction::calculatePercentage($this->product));

        $this->product->shouldReceive('getCustom')->once()->andReturn('1');
        $this->product->shouldReceive('getIsAllowAuction')->once()->andReturn(true);
        $this->product->shouldReceive('getIsAlanIn')->once()->andReturn(false);
        $this->product->shouldReceive('getBsoCustomPercentage')->andReturn(Auction::DEFAULT_CUSTOM_PERCENTAGE);

        $this->assertSame(array(0.8, 0.1, 0.1), Auction::calculatePercentage($this->product));
    }

    public function testGetShippingCost()
    {
        $this->assertSame(30, ShippingCalculator::getCost(1000));
    }

    public function tearDown()
    {
        m::close();
    }
}
