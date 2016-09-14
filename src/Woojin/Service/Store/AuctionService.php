<?php

namespace Woojin\Service\Store;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\Utility\Avenue;
use Woojin\StoreBundle\Event\AuctionCreateEvent;
use Woojin\StoreBundle\AuctionEvents;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AuctionService
{
    protected $auction;
    protected $dispatcher;
    protected $container;
    protected $security;
    protected $em;
    protected $connection;
    
    public function __construct(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        $this
            ->setContainer($container)
            ->setDispatcher($dispatcher)
            ->setConnection($container->get('doctrine.dbal.default_connection'))
        ;
    }

    /**
     * 新增新的競拍實體
     * 
     * @param array $options
     */
    public function create(array $options)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->setOptionPercentage($options);

            $event = new AuctionCreateEvent();
            $event->setOptions($options);

            $event = $this->getDispatcher()->dispatch(AuctionEvents::CREATE, $event);
            $this->getConnection()->commit();

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            echo $e->getMessage();
        }
    }

    /**
     * 商品返回門市(BSO自己也一樣要做此動作)
     *
     * @return Woojin\StoreBundle\Entity\Auction
     */
    public function back(array $options)
    {
        $event = new AuctionBackEvent();
        $event->setAuction($this->getAuction())->setOptions($options);

        $this->getDispatcher()->dispatch(AuctionEvents::BACK, $event);

        return $event->getAuction();
    }

    /**
     * 售出
     * 
     * @param array $options
     */
    public function sold(array $options)
    {
        $event = new AuctionSoldEvent();
        $event->setAuction($this->getAuction())->setOptions($options);

        $this->getDispatcher()->dispatch(AuctionEvents::SOLD, $event);
    }

    /**
     * 客戶付費
     */
    public function pay()
    {
        $event = new AuctionPayEvent();
        $event->setAuction($this->getAuction())->setOptions($options);

        $this->getDispatcher()->dispatch(AuctionEvents::PAY, $event);
    }

    /**
     * 毛利分配
     */
    public function assign()
    {
        $event = new AuctionAssignEvent();
        $event->setAuction($this->getAuction())->setOptions($options);

        $this->getDispatcher()->dispatch(AuctionEvents::ASSIGN, $event);
    }

    /**
     * 售出取消
     */
    public function cancel()
    {
        $event = new AuctionCancelEvent();
        $event->setAuction($this->getAuction())->setOptions($options);

        $this->getDispatcher()->dispatch(AuctionEvents::CANCEL, $event);
    }

    public function updateSeller(Custom $custom)
    {
        $this->getAuction()->setSeller($custom);
        $this->getEm()->flush();

        return $this;
    }

    public function updateBuyer(Custom $custom)
    {
        $this->getAuction()->setBuyer($custom);
        $this->getEm()->flush();

        return $this;
    }

    public function setAuction(Auction $auction)
    {
        $this->setAuction($auction);

        return $this;
    }

    public function getAuction()
    {
        return $this->auction;
    }

    protected function setSecurity(TokenStorage $security)
    {
        $this->security = $security;

        return $this;
    }

    public function getSecurity()
    {
        return $this->security;
    }

    protected function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    protected function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    protected function setOptionPercentage(array &$options)
    {
        if (!array_key_exists('product', $options)) {
            throw new \Exception('Options doesnt include product');
        }

        if (true === $options['product']->getIsAllowAuction() && true === $options['product']->getIsAlanIn()) {
            $options['customPercentage'] = 0.8;
            $options['storePercentage'] = 0;
            $options['bsoPercentage'] = 0.2;
        } 

        if (false === $options['product']->getIsAllowAuction() && true === $options['product']->getIsAlanIn()) {
            $options['customPercentage'] = 0;
            $options['storePercentage'] = 0;
            $options['bsoPercentage'] = 1.0;
        } 

        if (true === $options['product']->getIsAllowAuction() && false === $options['product']->getIsAlanIn()) {
            $options['customPercentage'] = 0.8;
            $options['storePercentage'] = 0.1;
            $options['bsoPercentage'] = 0.1;
        } 

        if (false === $options['product']->getIsAllowAuction() && false === $options['product']->getIsAlanIn()) {
            $options['customPercentage'] = 0;
            $options['storePercentage'] = 0.5;
            $options['bsoPercentage'] = 0.5;
        } 
    }
}