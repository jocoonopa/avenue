<?php

namespace Woojin\Service\Sculper\Prototype;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\Service\Sculper\Prototype\Type;

class PurchaseInPrototype implements IPrototype
{
    protected $content;

    public function __construct()
    {
        $this->content = array(
            'type' => TYPE::PURCHASE_IN,
            'product' => array(
                'sn' => '',
                'brand' => '',
                'name' => '',
                'custom' => ''
            ),
            'order' => array(
                'required' => '',
                'paid' => '',
                'status' => ''
            )
        );

        return $this;
    }

    public function setBeforeChanged($order)
    {
        return $this;
    }

    public function setAfterChanged($order)
    {
        $product = $order->getProduct();

        $this->content['product']['sn'] = $product->getSn();
        $this->content['product']['brand'] = ($brand = $product->getBrand()) ? $brand->getName() : null;
        $this->content['product']['name'] = $product->getName();

        if ($custom = $product->getCustom()) {
            $this->content['product']['custom'] = $custom->getName() . $custom->getSex();
        }

        $this->content['order']['required'] = $order->getRequired();
        $this->content['order']['paid'] = $order->getPaid();
        $this->content['order']['status'] = $order->getStatus()->getName();
    }

    public function getContent()
    {
        return $this->content;
    }
}