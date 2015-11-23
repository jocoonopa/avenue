<?php

namespace Woojin\Service\Sculper\Prototype;

use Woojin\OrderBundle\Entity\Ope;
use Woojin\Service\Sculper\Prototype\Type;

class PatchPrototype implements IPrototype
{
    protected $content;

    public function __construct()
    {
        $this->content = array(
            'type' => TYPE::PATCH,
            'product' => array(
                'sn' => '',
                'brand' => '',
                'name' => ''
            ),
            'order' => array(
                'required' => '',
                'paid' => '',
                'status' => ''
            ),
            'ope' => array(
                'pay_type' => '',
                'amount' => ''
            )
        );

        return $this;
    }

    public function setBeforeChanged($ope)
    {
        return $this;
    }

    public function setAfterChanged($ope)
    {
        $order = $ope->getOrder();

        $product = $order->getProduct();

        $this->content['product']['sn'] = $product->getSn();
        $this->content['product']['brand'] = ($brand = $product->getBrand()) ? $brand->getName() : null;
        $this->content['product']['name'] = $product->getName();
        $this->content['order']['required'] = $order->getRequired();
        $this->content['order']['paid'] = $order->getPaid();
        $this->content['order']['status'] = $order->getStatus()->getName();
        $this->content['ope']['pay_type'] = ($paytype = $ope->getPayType()) ? $paytype->getName() : null;
        $this->content['ope']['amount'] = $ope->getAmount();
    }

    public function getContent()
    {
        return $this->content;
    }
}