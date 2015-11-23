<?php

namespace Woojin\Utility\YahooApi\Factory;

class OnlineParameterFactory implements IParameterFactory
{
    protected $parameters;

    public function get()
    {
        return $this->parameters;
    }

    public function create($product)
    {
        $this->parameters = array('ProductId' => $product->getYahooId());

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