<?php

namespace Woojin\StoreBundle\Tests\Entity;

use Mockery as m;

use Woojin\StoreBundle\Entity\Auction;

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

        $this->assertSame(array(0, 0.5, 0.5), Auction::calculatePercentage($this->product));

        $this->product->shouldReceive('getCustom')->once()->andReturn('1');
        $this->product->shouldReceive('getIsAllowAuction')->once()->andReturn(true);
        $this->product->shouldReceive('getIsAlanIn')->once()->andReturn(true);

        $this->assertSame(array(0.8, 0, 0.2), Auction::calculatePercentage($this->product));

        $this->product->shouldReceive('getCustom')->once()->andReturn('1');
        $this->product->shouldReceive('getIsAllowAuction')->once()->andReturn(true);
        $this->product->shouldReceive('getIsAlanIn')->once()->andReturn(false);

        $this->assertSame(array(0.8, 0.1, 0.1), Auction::calculatePercentage($this->product));
    }

    public function tearDown()
    {
        m::close();
    }
}
