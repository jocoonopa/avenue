<?php

namespace Woojin\Utility\YahooApi\Factory;

class MallCategoryGetParameterFactory implements IParameterFactory
{
    protected $parameters;

    public function get()
    {
        return $this->parameters;
    }

    public function create($r)
    {
        $this->parameters = $r;

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