<?php

namespace Woojin\Utility\YahooApi\Factory;

use Woojin\GoodsBundle\Entity\GoodsPassport;

class DeleteParameterFactory implements IParameterFactory
{
    protected $parameters;

    public function get()
    {
        return $this->parameters;
    }

    public function create($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $product) {
                if ($product instanceof GoodsPassport) {
                    $this->parameters['ProductId_!' . $key] = $product->getYahooId();
                }
            }
        } else {
            $product = $input;

            $this->parameters = array('ProductId' => $product->getYahooId());
        }
        
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