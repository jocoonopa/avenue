<?php

namespace Woojin\StoreBundle\Subscriber;

use Doctrine\ORM\EntityManager;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\Event;
use Woojin\StoreBundle\Event\AuctionEvent;

use Woojin\StoreBundle\AuctionEvents AS Events;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\Utility\Avenue\Avenue;

class AuctionSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $resolver;

    public function __construct(EntityManager $em)
    {
        $this->setEm($em)->setResolver(new OptionsResolver());
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::CREATE => array(
                array('onCreateAuction', 10),
                array('onUpdateProductStatus')
            ),
            Events::SOLD => array(
                array('onSold', 10),
                array('onUpdateProductStatus')
            ),
            Events::PAY => array(
                array('onPay')
            ),
            Events::ASSIGN => array(
                array('onAssign')
            ),
            Events::BACK => array(
                array('onBack', 10),
                array('onUpdateProductStatus')
            ),
            Events::CANCEL => array(
                array('onCancel', 10),
                array('onUpdateProductStatus')
            )
        );
    }

    public function onCreateAuction(Event $event)
    {
        $this->configureCreateOption();

        $auction = new Auction($this->getResolver()->resolve($event->getOptions()));

        $this->savePersistance($auction);

        $event->setAuction($auction);
    }

    public function onSold(Event $event)
    {
        $options = $event->getOptions();
        $auction = $event->getAuction();

        $this->configureSoldOption();

        $auction->sold($options);

        $this->savePersistance($auction);
    }

    public function onPay(Event $event)
    {
        $aution = $event->getAuction();

        $auction->setStatus(Auction::STATUS_PAYED);

        $this->savePersistance($auction);
    }

    public function onAssign(Event $event)
    {
        $aution = $event->getAuction();

        $auction->setStatus(Auction::STATUS_PROFIT_ASSIGN_COMPLETE);

        $this->savePersistance($auction);
    }

    public function onBack(Event $event)
    {
        $this->configureBackOption();
        $options = $this->getResolver()->resolve($event->getOptions());

        $auction = $event->getAuction();

        $auction->back($options);

        $this->savePersistance($auction);
    }

    public function onCancel(Event $event)
    {
        $this->configureCancelOption();
        $options = $this->getResolver()->resolve($event->getOptions());

        $auction = $event->getAuction();
        $auction->cancel($options);

        $this->savePersistance($auction);
    }

    public function onUpdateProductStatus(Event $event)
    {
        $product = $event->getAuction()->getProduct();
        $product->setStatus($this->fetchProductStatus($event->getAuction()->getStatus()));

        $this->savePersistance($product);
    }

    protected function savePersistance($object)
    {
        $this->getEm()->persist($object);
        $this->getEm()->flush();
    }

    protected function fetchProductStatus($statusCode)
    {
        return $this->getEm()->getRepository('WoojinGoodsBundle:GoodsStatus')->find(Auction::fetchProductStatusId($statusCode));
    }

    protected function setEm(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    public function getEm()
    {
        return $this->em;
    }

    protected function setResolver(OptionsResolver $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    public function getResolver()
    {
        return $this->resolver;
    }

    protected function configureCreateOption()
    {
        $this->getResolver()
            ->setRequired('product')
            ->setRequired('creater')
            ->setRequired('createStore')
            ->setRequired('bsoStore')
        ;

        $this->getResolver()->setDefaults(array(
            'status' => Auction::STATUS_ONBOARD,
            'customPercentage' => 0,
            'storePercentage' => 0.5,
            'bsoPercentage' => 0.5,
            'seller' => NULL
        ));
    }

    protected function configureBackOption()
    {
        $this->getResolver()
            ->setRequired('backer')
        ;
    }

    protected function configureSoldOption()
    {
        $this->getResolver()
            ->setRequired('price')
            ->setRequired('buyer')
            ->setRequired('bsser')
            ->setRequired('soldAt')
        ;
    }


    protected function configureCancelOption()
    {
        $this->getResolver()
            ->setRequired('canceller')
        ;
    }
}
