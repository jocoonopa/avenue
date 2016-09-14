<?php

namespace Woojin\Utility\Playground;

class Calculator
{
    public function methodToTestOverride()
    {
        $myClass = new MyClass();
        $result = $myClass->someMethod();
        
        return $result;
    }

    public function methodToTestAlias()
    {
        return MyClass::someStaticMethod();
    }

    public function plus(&$a)
    {
        $a ++;

        return $a;
    }

    public function a($a)
    {
        return $this;
    }

    public function b($b)
    {
        return $this;
    }

    public function c()
    {
        return $this;
    }

    public function getClassName()
    {
        return __CLASS__;
    }
}