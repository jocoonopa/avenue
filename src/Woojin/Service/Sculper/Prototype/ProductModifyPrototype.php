<?php

namespace Woojin\Service\Sculper\Prototype;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\Service\Sculper\Prototype\Type;

class ProductModifyPrototype implements IPrototype
{
    protected $content;

    public function __construct()
    {
        $this->content = array(
            'type' => TYPE::PRODUCT_MODIFY,
            'product' => array(
                'id' => '',
                'sn' => '',
                'brand' => '',
                'name' => '',
                'before_cost' => '',
                'before_price' => '',
                'after_cost' => '',
                'after_price' => ''
            )
        );

        return $this;
    }

    public function setAfterChanged($product)
    {
        $this->content['product']['id'] = $product->getId();
        $this->content['product']['sn'] = $product->getSn();
        $this->content['product']['brand'] = ($brand = $product->getBrand()) ? $brand->getName() : null;
        $this->content['product']['name'] = $product->getName();
        $this->content['product']['after_cost'] = $product->getCost();
        $this->content['product']['after_price'] = $product->getPrice();
    }

    public function setBeforeChanged($product)
    {
        $this->content['product']['before_cost'] = $product->getCost();
        $this->content['product']['before_price'] = $product->getPrice();
    }

    public function getContent()
    {
        return $this->content;
    }
}