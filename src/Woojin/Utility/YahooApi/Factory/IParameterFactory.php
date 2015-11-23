<?php

namespace Woojin\Utility\YahooApi\Factory;

use Woojin\GoodsBundle\Entity\GoodsPassport;

interface IParameterFactory
{
    /**
     * 取得 Parameters
     * 
     * @return array $parameters
     */
    public function get();

    /**
     * 建立 Parameters
     * 
     * @param  $val
     * @return $this                
     */
    public function create($val);

    /**
     * 設置 parameters 屬性
     * 
     * @param string $key
     * @param string $val
     * @return $this  
     */
    public function set($key, $val);

    /**
     * 清空  Parameters
     * 
     * @return $this
     */
    public function clear();
}