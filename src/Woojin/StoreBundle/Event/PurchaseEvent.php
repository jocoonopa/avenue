<?php

namespace Woojin\StoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\GoodsBundle\Entity\Img;
use Woojin\GoodsBundle\Entity\Desimg;

/**
 * 1. 置入Product Factory材料
 * 2. 產生Product
 * 3. 產生Order
 */
class PurchaseEvent extends Event
{
    protected $products;

    protected $orders;

    protected $opes;

    protected $img;

    protected $desimg;

    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function setImg(Img $img)
    {
        $this->img = $img;

        return $this;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function setDesimg(Desimg $desimg)
    {
        $this->desimg = $desimg;

        return $this;
    }

    public function getDesimg()
    {
        return $this->desimg;
    }

    public function setOrders(array $orders)
    {
        $this->orders = $orders;

        return $this;
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function setProducts(array $products)
    {
        $this->products = $products;

        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setOpes(array $opes)
    {
        $this->opes = $opes;

        return $this;
    }

    public function getOpes()
    {
        return $this->opes;
    }

    public function setOption($key, $val)
    {
        $this->options[$key] = $val;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}