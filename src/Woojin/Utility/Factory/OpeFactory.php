<?php

namespace Woojin\Utility\Factory;

use Woojin\OrderBundle\Entity\Ope;
use Woojin\Utility\Avenue\Avenue;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeFactory
{
    protected $storage;

    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        
        $this->configureOptions($this->resolver);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setOptional(
            array(
            'amount',
            'name',
            'cost',
            'price',
            'webPrice',
            'seoWord',
            'orgSn',
            'memo',
            'custom',
            'customSn',
            'seoSlogan',
            'seoSlogan2',
            'brand',
            'level',
            'color',
            'colorSn',
            'model',
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

        $resolver->setRequired(array('orders', 'user', 'paytype'));

        return $this;
    } 

    public function create(array $options = array())
    {
        $act;

        foreach ($options['orders'] as $order) {
            switch($order->getKind()->getId())
            {
                case Avenue::OK_IN:
                    $act = '成立進貨訂單';
                    break;

                case Avenue::OK_CONSIGN_IN:
                    $act = '成立寄賣訂單';
                    break;

                case Avenue::OK_FEEDBACK:
                    $act = '成立寄賣回扣訂單';
                    break;

                default:
                    break;
            }

            $ope = new Ope();
            $ope
                ->setUser($options['user'])
                ->setOrder($order)
                ->setAct($act)
                ->setDatetime(new \DateTime())
                ->setAmount($options['cost'])
                ->setPayType($options['paytype'])
            ;

            $this->addStorage($ope);
        }

        return $this;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function addStorage(Ope $ope)
    {
        $this->storage[] = $ope;

        return $this;
    } 
}