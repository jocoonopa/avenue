<?php

namespace Woojin\Utility\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityManager;

use Woojin\UserBundle\Entity\User;
use Woojin\GoodsBundle\Entity\Behalf as Entity;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\OrderBundle\Entity\Custom;
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\OrderBundle\Entity\Orders;

use Woojin\Utility\Authority\AuthorityJudger;
use Woojin\Utility\Avenue\Notifier;
use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\Factory\Product as ProductFacory;
use Woojin\Utility\Facade\Product;
use Woojin\Utility\Facade\BehalfStatus;

use Woojin\Service\Business\Stock;

class Behalf
{
    protected $em;

    protected $container;

    protected $statusFacade;

    protected $behalf;
    
    protected $notifier;

    protected $resolver;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;

        $this->statusFacade = $this->container->get('facade.behalf.status');
        $this->notifier = $this->container->get('avenue.notifier');

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $behalfResolver = new OptionsResolver();
        $this->behalfConfigureOptions($behalfResolver);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'behalf' => null,
            'deliverySn' => null,
            'bankAccount' => null,
            'bankCode' => null,
            'phone' => null,
            'required' => 0,
            'memo' => null,
            'status' => null,
            'cost' => null
        ));

        $resolver->setRequired(array('behalf'));
        
        $this->resolver = $resolver;

        return $this;
    }

    protected function behalfConfigureOptions(OptionsResolver $resolver)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'custom' => null,
            'want' => null,
            'phone' => null,
            'status' => $this->statusFacade->findNotConfirm()
        ));

        $resolver->setRequired(array('custom', 'want', 'phone'));
        
        $this->behalfResolver = $resolver;

        return $this;
    }

    /**
     * 建立代購訂單 (訂單狀態: 未確認)
     *
     * 1. 新增代購實體
     * 2. 送信通知該商品店長
     *
     * @param  array  $options 
     * @return this
     */
    public function create(array $options = array())
    {
        $options = $this->behalfResolver->resolve($options);

        $want = $options['want'];
        $custom = $options['custom'];
        $phone = $options['phone'];
        $status = $options['status'];

        if (!$want->getIsBehalf()) {
            throw new \Exception('該商品不可代購');
        }

        $this->behalf = new Entity($custom, $want, $status, $phone);
        $this->em->persist($this->behalf);
        $this->em->flush();

        $this->notifier->behalfForCustom($this->behalf);
        $this->notifier->behalfForAvenue($this->behalf);

        return $this->behalf;
    }

    /**
     * 確認代購訂單 (訂單狀態: 已確認)
     *
     * 1. 修改代購實體狀態
     * 2. 綁定店長和代購實體
     *
     * @param  array  $options 
     * @return this
     */
    public function firstConfirm(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        $this->behalf = $options['behalf'];
        $this->behalf
            ->setUser($this->container->get('authority.judger')->getUser())
            ->setStatus($this->statusFacade->findFirstConfirm())
            ->setRequired($this->behalf->getWant()->getPromotionPrice(true))
            ->setConfirmFirstAt(new \DateTime())
        ;

        $this->em->persist($this->behalf);
        $this->em->flush();

        $this->notifier->behalfForCustom($this->behalf);

        return $this;
    }

    /**
     * 代購訂單支付訂金 (訂單狀態: 已付訂金)
     *
     * 1. 修改代購實體狀態
     * 2. 更新代購已付金額
     * 3. 通知客戶已收到訂金
     *
     * @param  array  $options 
     * @return this
     */
    public function paid(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        $this->behalf = $options['behalf'];
        $this->behalf
            ->setBankAccount($options['bankAccount'])
            ->setBankCode($options['bankCode'])
            ->setStatus($this->statusFacade->findPaid())
            ->setPayAt(new \DateTime())
            ->setMemo($options['memo'])
        ;

        $this->em->persist($this->behalf);
        $this->em->flush();

        $this->notifier
            ->behalfForCustom($this->behalf)
            ->behalfForAvenue($this->behalf)
        ;

        return $this;
    }

    /**
     * 代購商品入庫完成 (訂單狀態: 已入庫)
     *
     * 1. 修改代購實體狀態
     * 2. 進貨訂單成立
     * 3. 通知客戶支付尾款(支付完後請mail 或是 來電告知)
     *
     * @param  array  $options 
     * @return this
     */
    public function productIn(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        $this->behalf = $options['behalf'];

        $want = $this->behalf->getWant();

        $this->em->getConnection()->beginTransaction();

        try {
            $productFactory = $this->container->get('factory.product');
            $got = $productFactory->_clone($want)->getProduct();
            $got
                ->setIsBehalf(false) // 否則會一直出現一樣的代購商品在官網
                ->setCost($options['cost'])
                ->setSn($got->genSn($this->behalf->getUser()))
                ->setGotBehalf($this->behalf)
            ;
            $this->em->persist($got);

            $stock = $this->container->get('business.stock');
            $stock->init($got);

            $this->behalf
                ->setGot($got)
                ->setStatus($this->statusFacade->findPurIn())
                ->setInAt(new \DateTime())
            ;
            $this->em->persist($this->behalf);

            $this->container->get('logger.ope')->recordOpe(
                $stock->getOrder(), 
                '成立進貨訂單',
                $this->container->get('authority.judger')->getUser()
            );

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();

            throw $e;
        }

        return $this;
    }

    /**
     * 代購尾款支付完成 (訂單狀態: 已付尾款)
     *
     * 1. 修改代購實體狀態
     * 2. 通知客戶已收到尾款
     * 3. 銷貨訂單自動成立
     *
     * @param  array  $options 
     * @return this
     */
    public function secondConfirm(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        $this->behalf = $options['behalf'];
        $this->behalf
            ->setStatus($this->statusFacade->findSecondConfirm())
            ->setConfirmSecondAt(new \DateTime())
        ;
        
        $this->notifier
            ->behalfForAvenue($this->behalf)
            ->behalfForCustom($this->behalf)
        ;

        return $this;
    }

    /**
     * 代購商品出貨 (訂單狀態: 已出貨)
     *
     * 1. 修改代購實體狀態
     * 2. 通知客戶已出貨
     *
     * @param  array  $options 
     * @return this
     */
    public function productOut(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        $this->behalf = $options['behalf'];
        $this->behalf
            ->setStatus($this->statusFacade->findPurOut())
            ->setSendAt(new \DateTime())
            ->setDeliverySn($options['deliverySn'])
        ;
        
        $this->em->getConnection()->beginTransaction();

        try {
            $custom = $this->behalf->getCustom();

            $got = $this->behalf->getGot();

            $required = $this->behalf->getWant()->getPromotionPrice(true);
            
            $invoice = new Invoice;
            $invoice
                ->setCustom($custom)
                ->setStore($this->behalf->getUser()->getStore())
                ->setHasPrint(0)
                ->setSn(uniqid())
            ;
            $this->em->persist($invoice);

            $order = new Orders();
            $order
                ->setGoodsPassport($got)
                ->setCustom($custom)
                ->setPayType($this->em->find('WoojinOrderBundle:PayType', Avenue::PT_ATM))
                ->setKind($this->em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_OUT))
                ->setStatus($this->em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE))
                ->setRequired($required)
                ->setOrgRequired($required)
                ->setOrgPaid($required)
                ->setPaid($required)
                ->setInvoice($invoice)
            ;
            $this->em->persist($order);

            $got->setStatus($this->em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_SOLDOUT));
            $this->em->persist($got);
            
            $this->em->flush();

            $this->container->get('logger.ope')->recordOpe(
                $order, 
                '成立銷貨訂單(代購)',
                $this->container->get('authority.judger')->getUser()
            );

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();

            throw $e;
        }

        $this->notifier->behalfForCustom($this->behalf);

        return $this;
    }

    /**
     * 取消代購( create 之後 confirm 之前才可執行, 訂單狀態: 已取消)
     *
     * 1. 修改代購實體狀態
     *
     * @param  array  $options 
     * @return this
     */
    public function cancel(array $options = array())
    {
        $this->behalf = $options['behalf'];
        $this->behalf->setStatus($this->statusFacade->findCancel());

        $this->em->persist($this->behalf);
        $this->em->flush();

        $this->notifier
            ->behalfForAvenue($this->behalf)
            ->behalfForCustom($this->behalf)
        ;

        return $this;
    }

    /**
     * 取消代購( create 之後 confirm 之前才可執行, 訂單狀態: 香榭取消)
     *
     * 1. 修改代購實體狀態為"香榭取消"
     *
     * @param  array  $options 
     * @return this
     */
    public function avenueCancel(array $options = array())
    {
        $this->behalf = $options['behalf'];
        $this->behalf
            ->setStatus($this->statusFacade->findAvenueCancel())
            ->setAcancelAt(new \DateTime())
        ;

        $this->em->persist($this->behalf);
        $this->em->flush();

        $this->notifier->behalfForCustom($this->behalf);

        return $this;
    }

    /**
     * 退款(狀態改為: 已退款)
     *
     * 1. 修改代購實體狀態為"已退款"
     *
     * @param  array  $options 
     * @return this
     */
    public function chargeBack(array $options = array())
    {
        $this->behalf = $options['behalf'];
        $this->behalf
            ->setStatus($this->statusFacade->findChargeBack())
            ->setChargeBackAt(new \DateTime())
        ;

        $this->em->persist($this->behalf);
        $this->em->flush();

        $this->notifier->behalfForCustom($this->behalf);

        return $this;
    }

    /**
     * 根據目前代購狀態自動前進至下一狀態
     * 
     * @param  array  $options 
     * @return $this
     */
    public function nextStep(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        $this->behalf = $options['behalf'];

        switch ($this->behalf->getStatus()->getId())
        {
            case Avenue::BS_NOT_CONFIRM:
                $this->firstConfirm($options);
                break;

            case Avenue::BS_FIRST_CONFIRM:
                $this->paid($options);
                break;

            case Avenue::BS_PAID:
                $this->secondConfirm($options);
                break;

            case Avenue::BS_SECOND_CONFIRM:
                $this->productIn($options);
                break;

            case Avenue::BS_PURIN:
                $this->productOut($options);
                break;

            case Avenue::BS_AVENUE_CANCEL:
                $this->chargeBack($options);
                break;

            case Avenue::BS_PUROUT:
            case Avenue::BS_CANCEL:
            default:
                break;
        }
    }

    /**
     * Get Behalf
     * 
     * @return \Woojin\GoodsBundle\Entity\Behalf
     */
    public function getBehalf()
    {
        return $this->behalf;
    }
}