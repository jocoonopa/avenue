<?php

namespace Woojin\StoreBundle\Tests\Subscriber;

use Mockery as m;

use Woojin\GoodsBundle\Entity\GoodsStatus;
use Woojin\StoreBundle\Subscriber\AuctionSubscriber;
use Woojin\StoreBundle\Entity\Auction;

use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class AuctionSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private $event;
    private $entityManager;
    private $container;
    
    public function setUp()
    {        
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->entityManager = m::mock('Doctrine\ORM\EntityManager');
        $this->event = m::mock('Woojin\StoreBundle\Event\AuctionEvent')->makePartial();
        $this->product = m::mock('Woojin\GoodsBundle\Entity\GoodsPassport')->makePartial();
        $this->user = m::mock('Woojin\UserBundle\Entity\User');
        $this->custom = m::mock('Woojin\OrderBundle\Entity\Custom');
        $this->auction = m::mock('Woojin\StoreBundle\Entity\Auction')->makePartial();
    }

    public function testOnCreateAuction()
    {
        $this->event->setOptions(array(
            'product' => $this->product,
            'creater' => true,
            'createStore' => true,
            'bsoStore' => true
        ));
        
        $this->entityManager->shouldReceive('getRepository->find')->atMost(1)->andReturn(new GoodsStatus);
        $this->entityManager->shouldReceive('persist', 'flush')->once();

        $subscriber = new AuctionSubscriber($this->entityManager);
        $subscriber->onCreateAuction($this->event);

        $this->assertTrue($this->event->getAuction() instanceof \Woojin\StoreBundle\Entity\Auction);

        return array($subscriber, $this->event);
    }

    /**
     * It seems the Mock object was defined in the previous method, 
     * but contain data doesn't keep
     * 
     * @depends testOnCreateAuction
     */
    public function testOnUpdateProductStatus(array $compose)
    {
        list($subscriber, $event) = $compose;
        
        $subscriber->onUpdateProductStatus($event);

        $this->assertTrue($event->getAuction()->getProduct()->getStatus() instanceof GoodsStatus);
    }
    
    public function testOnBackAuction()
    {    
        $this->entityManager->shouldReceive('persist', 'flush')->once();

        $this->event->setAuction($this->auction)->setOptions(array('backer' => $this->user));

        $subscriber = new AuctionSubscriber($this->entityManager);
        $subscriber->onBack($this->event);

        $this->assertTrue($this->event->getAuction()->getBacker() instanceof \Woojin\UserBundle\Entity\User);
        $this->assertSame(Auction::STATUS_BACK_TO_STORE, $this->event->getAuction()->getStatus());

        return array($subscriber, $this->event);
    }

    public function testOnSoldAuction()
    {    
        $mockPrice = 100;
        $options = array(
            'buyer' => $this->custom,
            'bsser' => $this->user, 
            'soldAt' => new \DateTime,
            'price' => $mockPrice
        );

        $this->entityManager->shouldReceive('persist', 'flush')->once();
        $this->auction->shouldReceive('sold')->once()->with($options)->passthru();
        $this->event->setAuction($this->auction)->setOptions($options);

        $subscriber = new AuctionSubscriber($this->entityManager);
        $subscriber->onSold($this->event);

        $this->assertEquals($mockPrice, $this->event->getAuction()->getPrice());
        $this->assertTrue($this->event->getAuction()->getBsser() instanceof  \Woojin\UserBundle\Entity\User);
        $this->assertTrue($this->event->getAuction()->getSoldAt() instanceof \DateTime);
        $this->assertTrue($this->event->getAuction()->getBuyer() instanceof \Woojin\OrderBundle\Entity\Custom);
        $this->assertSame(Auction::STATUS_SOLD, $this->event->getAuction()->getStatus());
    }

    /**
     * - The status of event's auction is setted to what we expect
     * - Auction's canceller is setted to what we expect 
     */
    public function testOnCancelAuction()
    {    
        $this->event->setAuction($this->auction);
        $this->entityManager->shouldReceive('persist', 'flush')->once();
        $subscriber = new AuctionSubscriber($this->entityManager);

        $this->event->setOptions(array('canceller' => $this->user));
        $subscriber->onCancel($this->event);

        $this->assertSame(Auction::STATUS_ORDER_CANCEL, $this->event->getAuction()->getStatus());
        $this->assertTrue($this->event->getAuction()->getCanceller() instanceof \Woojin\UserBundle\Entity\User);
    }

    /**
     * @expectedException Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testOnCancelMissingOptionsException()
    {        
        $subscriber = new AuctionSubscriber($this->entityManager);
        $this->event->setOptions(array());
        $subscriber->onCancel($this->event);
    }

    /**
     * @expectedException Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function testOnCancelUndefinedOptionsException()
    {
        $subscriber = new AuctionSubscriber($this->entityManager);
        $this->event->setOptions(array('__canceller__' => 'x'));
        $subscriber->onCancel($this->event);
    }
    
    public function tearDown()
    {
        m::close();
    }
}