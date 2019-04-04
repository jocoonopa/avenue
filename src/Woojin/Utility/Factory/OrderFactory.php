<?php

namespace Woojin\Utility\Factory;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\Utility\Avenue\Avenue;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFactory
{
    protected $storage;

    protected $resolver;

    public function __construc()
    {
        $this->storage = new ArrayCollection();

        $this->resolver = new OptionsResolver();

        $this->configureOptions($this->resolver);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'amount',
            'name',
            'cost',
            'price',
            'webPrice',
            'seoWord',
            'orgSn',
            'memo',
            'seoSlogan',
            'seoSlogan2',
            'brand',
            'level',
            'color',
            'colorSn',
            'model',
            'customSn',
            'mt',
            'pattern',
            'source',
            'status',
            'isAllowWeb',
            'isAllowCreditCard',
            'isBehalf',
            'description',
            'brief',
            'categorys',
            'orderStatusHandling',
            'orderStatusComplete',
            'orderKindIn',
            'orderKindConsign',
            'orderKindFeedback'
        ));
        $resolver->setRequired(array('products' , 'paytype', 'custom', 'user'));

        return $this;
    }

    public function create(array $options = array())
    {
        foreach ($options['products'] as $product) {
            if ($options['custom']) {
                $this->onConsign($options, $product);
            } else {
                $this->onPurchase($options, $product);
            }
        }

        return $this;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function addStorage(Orders $order)
    {
        $this->storage[] = $order;

        return $this;
    }

    protected function onPurchase(array $options, GoodsPassport $product)
    {
        $order = new Orders;
        $order
            ->setCustom($options['custom'])
            ->setGoodsPassport($product)
            ->setPayType($options['paytype'])
            ->setKind($options['orderKindIn'])
            ->setStatus($options['orderStatusComplete'])
            ->setRequired($product->getCost())
            ->setPaid($product->getCost())
        ;

        $this->addStorage($order);

        return $this;
    }

    protected function onConsign(array $options, GoodsPassport $product)
    {
        $order = new Orders;
        $order
            ->setCustom($options['custom'])
            ->setGoodsPassport($product)
            ->setPayType($options['paytype'])
            ->setKind($options['orderKindConsign'])
            ->setStatus($options['orderStatusComplete'])
            ->setRequired($product->getCost())
            ->setPaid($product->getCost())
        ;

        $orderFeedBack = new Orders;
        $orderFeedBack
            ->setKind($options['orderKindFeedback'])
            ->setStatus($options['orderStatusHandling'])
            ->setCustom($order->getCustom())
            ->setGoodsPassport($order->getProduct())
            ->setPayType($order->getPayType())
            ->setRequired($order->getProduct()->getCost())
            ->setPaid(0)
            ->setParent($order)
        ;

        $this
            ->addStorage($order)
            ->addStorage($orderFeedBack)
        ;

        return $this;
    }
}