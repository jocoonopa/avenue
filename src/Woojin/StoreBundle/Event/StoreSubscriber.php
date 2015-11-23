<?php

namespace Woojin\StoreBundle\Event;

use Woojin\StoreBundle\StoreEvents;
use Woojin\Utility\Factory\ProductFactory;
use Woojin\Utility\Factory\OrderFactory;
use Woojin\Utility\Factory\OpeFactory;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 使用ProductFactory , OrderFactory, OpeFactory 產生實體
 */
class StoreSubscriber implements EventSubscriberInterface
{
    public function __construct(ProductFactory $productFactory, OrderFactory $orderFactory, OpeFactory $opeFactory)
    {        
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->opeFactory = $opeFactory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            StoreEvents::STORE_PURCHASE_IN => array(
                array('onProductCreate', 0),
                array('onOrderCreate', 0),
                array('onOpeCreate', 0)
            ),
        );
    }

    public function onProductCreate(PurchaseEvent $event)
    {
        $this->productFactory->create($event->getOptions());
        $event->setProducts($this->productFactory->getStorage());

        return $this;
    }

    public function onOrderCreate(PurchaseEvent $event)
    {
        $event->setOption('products', $event->getProducts());
        $this->orderFactory->create($event->getOptions());

        $event->setOrders($this->orderFactory->getStorage());

        return $this;
    }

    public function onOpeCreate(PurchaseEvent $event)
    {
        $event->setOption('orders', $event->getOrders());
        $this->opeFactory->create($event->getOptions());

        $event->setOpes($this->opeFactory->getStorage());

        return $this;
    }
}
