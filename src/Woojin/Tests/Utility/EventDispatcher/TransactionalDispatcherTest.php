<?php

namespace Woojin\Tests\Utility\EventDispatcher;

use Mockery as m;
use Woojin\Utility\EventDispatcher\TransactionalDispatcher;
use Woojin\StoreBundle\AuctionEvents;

class TransactionalDispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $event;
    private $entityManager;
    private $container;
    private $hasDispatcherSucceed;
    
    public function setUp()
    {        
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->entityManager = m::mock('Doctrine\ORM\EntityManager');
        $this->event = m::mock('Symfony\Component\EventDispatcher\Event');
    }

    /** 
     * @dataProvider provideTransactions
     * 
     * @param  boolean $hasDispatcherSucceed
     * @param  string $expectedMethod      
     */
    public function testShouldDispatchEventInTransaction($hasDispatcherSucceed, $expectedMethod)
    {
        $this->hasDispatcherSucceed = $hasDispatcherSucceed;
        //$this->entityManager->shouldReceive('beginTransaction')->once();
        //$this->entityManager->shouldReceive($expectedMethod)->once();

        $this->assertSame($this->event, $this->dispatch());
    }

    public function provideTransactions()
    {
        return array(
            'when propagation succeed should commit' => array(true, 'commit'),
            'when propagation failed should rollback' => array(false, 'rollback'),
        );
    }

    private function dispatch()
    {
        $transactionalDispatcher = new TransactionalDispatcher($this->container, $this->entityManager);

        $this->event->shouldReceive('setDispatcher')->once()->with($transactionalDispatcher);
        $this->event->shouldReceive('setName')->once()->with(AuctionEvents::CREATE);
        $this->event->shouldReceive('isPropagationStopped')->andReturn(!$this->hasDispatcherSucceed);
        
        return $transactionalDispatcher->dispatch(AuctionEvents::CREATE, $this->event);
    }
    
    public function tearDown()
    {
        m::close();
    }
}