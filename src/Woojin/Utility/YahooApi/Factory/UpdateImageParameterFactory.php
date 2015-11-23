<?php

namespace Woojin\Utility\YahooApi\Factory;

class UpdateImageParameterFactory implements IParameterFactory
{
    protected $parameters;

    public function get()
    {
        return $this->parameters;
    }

    public function create($product)
    {
        $this->parameters = array(
            'ProductId'     => $product->getYahooId(),
            'ImageName'     => $product->getImg()->getYahooName(), // 必須要增加一個商品Yahoo_id 屬性
            'ImageFile1'    => '@' . $_SERVER['HTTP_HOST'] . $product->getImg()->getPath(), // $_SERVER於此為必要之餓
            'MainImage'     => 'ImageFile1'
        );

        return $this;
    }

    public function set($key, $val)
    {
        $this->parameters[$key] = $val;

        return $this;
    }

    public function clear()
    {
        $this->parameters = array();

        return $this;
    }
}