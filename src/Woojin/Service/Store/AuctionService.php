<?php

namespace Woojin\Service\Store;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\OrderBundle\Entity\Custom;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\StoreBundle\Event\AuctionBackEvent;
use Woojin\StoreBundle\Event\AuctionCreateEvent;
use Woojin\StoreBundle\Event\AuctionSoldEvent;
use Woojin\StoreBundle\Event\AuctionCancelEvent;
use Woojin\StoreBundle\AuctionEvents;
use Woojin\Utility\Avenue;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AuctionService
{
    protected $auction;
    protected $dispatcher;
    protected $container;
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

            $this->setAuction($event->getAuction());

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            return $e;
        }
    }

    /**
     * 商品返回門市(BSO自己也一樣要做此動作)
     *
     * @return Woojin\StoreBundle\Entity\Auction
     */
    public function back(array $options)
    {
        $this->getConnection()->beginTransaction();
        try {
            $event = new AuctionBackEvent();

            $event->setAuction($this->getAuction())->setOptions($options);

            $this->getDispatcher()->dispatch(AuctionEvents::BACK, $event);
            $this->getConnection()->commit();

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            return $e;
        }
    }

    /**
     * 售出
     *
     * @param array $options
     */
    public function sold(array $options)
    {
        $this->getConnection()->beginTransaction();
        try {
            $event = new AuctionSoldEvent();
            $event->setAuction($this->getAuction())->setOptions($options);

            $this->getDispatcher()->dispatch(AuctionEvents::SOLD, $event);
            $this->getConnection()->commit();

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            return $e;
        }
    }

    /**
     * 售出取消
     */
    public function cancel(array $options)
    {
        $this->getConnection()->beginTransaction();
        try {
            $event = new AuctionCancelEvent();
            $event->setAuction($this->getAuction())->setOptions($options);

            $this->getDispatcher()->dispatch(AuctionEvents::CANCEL, $event);
            $this->getConnection()->commit();

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            return $e;
        }
    }

    /**
     * 客戶付費
     *
     * @deprecated
     */
    public function pay()
    {
        $this->getConnection()->beginTransaction();
        try {
            $event = new AuctionPayEvent();
            $event->setAuction($this->getAuction())->setOptions($options);

            $this->getDispatcher()->dispatch(AuctionEvents::PAY, $event);
            $this->getConnection()->commit();

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            return $e;
        }
    }

    /**
     * 毛利分配
     *
     * @deprecated
     */
    public function assign()
    {
        $this->getConnection()->beginTransaction();
        try {
            $event = new AuctionAssignEvent();
            $event->setAuction($this->getAuction())->setOptions($options);

            $this->getDispatcher()->dispatch(AuctionEvents::ASSIGN, $event);
            $this->getConnection()->commit();

            return $event->getAuction();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();

            return $e;
        }
    }

    public function setAuction(Auction $auction)
    {
        $this->auction = $auction;

        return $this;
    }

    public function getAuction()
    {
        return $this->auction;
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

    /**
     * Setting auction profit assign percentage
     *
     * @param array $options
     */
    protected function setOptionPercentage(array &$options)
    {
        if (!array_key_exists('product', $options)) {
            throw new \Exception('Options doesnt include product');
        }

        $percentages = Auction::calculatePercentage($options['product']);
        
        $options['customPercentage'] = $percentages[0];
        $options['storePercentage'] = $percentages[1];
        $options['bsoPercentage'] = $percentages[2];

        return $this;
    }
}
