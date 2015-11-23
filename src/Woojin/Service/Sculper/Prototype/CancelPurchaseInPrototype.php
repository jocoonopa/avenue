<?php

namespace Woojin\Service\Sculper\Prototype;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\Service\Sculper\Prototype\Type;

class CancelPurchaseInPrototype implements IPrototype
{
    protected $content;

    public function __construct()
    {
        $this->content = array(
            'type' => TYPE::CANCEL_PURCHASE_IN,
            'product' => array(
                'id' => '',
                'sn' => '',
                'brand' => '',
                'name' => '',
                'cost' => '',
                'price' => ''
            )
        );

        return $this;
    }

    public function setBeforeChanged($product)
    {
        return $this;
    }

    public function setAfterChanged($product)
    {
        $this->content['product']['id'] = $product->getId();
        $this->content['product']['sn'] = $product->getSn();
        $this->content['product']['brand'] = ($brand = $product->getBrand()) ? $brand->getName() : null;
        $this->content['product']['name'] = $product->getName();
        $this->content['product']['cost'] = $product->getCost();
        $this->content['product']['price'] = $product->getPrice();
    }


    public function getContent()
    {
        return $this->content;
    }
}