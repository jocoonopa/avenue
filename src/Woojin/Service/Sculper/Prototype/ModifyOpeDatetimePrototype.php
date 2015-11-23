<?php

namespace Woojin\Service\Sculper\Prototype;

use Woojin\OrderBundle\Entity\Ope;
use Woojin\Service\Sculper\Prototype\Type;

class ModifyOpeDatetimePrototype implements IPrototype
{
    protected $content;

    public function __construct()
    {
        $this->content = array(
            'type' => TYPE::MODIFY_OPE_DATETIME,
            'product' => array(
                'sn' => '',
                'brand' => '',
                'name' => ''
            ),
            'ope' => array(
                'before_datetime' => '',
                'after_datetime' => '',
            )
        );
        
        return $this;
    }

    public function setAfterChanged($ope)
    {
        $order = $ope->getOrder();

        $product = $order->getProduct();

        $this->content['product']['sn'] = $product->getSn();
        $this->content['product']['brand'] = ($brand = $product->getBrand()) ? $brand->getName() : null;
        $this->content['product']['name'] = $product->getName();
        $this->content['ope']['after_datetime'] = $ope->getDatetime()->format('Y-m-d');

        return $this;
    }

    public function setBeforeChanged($ope)
    {
        $order = $ope->getOrder();

        $product = $order->getProduct();

        $this->content['ope']['before_datetime'] = $ope->getDatetime()->format('Y-m-d');

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }
}