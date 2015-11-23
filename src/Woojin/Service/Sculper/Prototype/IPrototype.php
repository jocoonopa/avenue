<?php

namespace Woojin\Service\Sculper\Prototype;

interface IPrototype
{
    /**
     * 注入尚未修改前的資料
     * 
     * @param  $entity 
     */
    public function setBeforeChanged($entity);

    /**
     * 注入修改後的資料
     * 
     * @param  $entity 
     */
    public function setAfterChanged($entity);
    
    public function getContent();
}