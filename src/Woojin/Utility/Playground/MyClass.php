<?php

namespace Woojin\Utility\Playground;

class MyClass
{
    public function someMethod()
    {   
        return __METHOD__;
    }

    public static function someStaticMethod()
    {
        return __METHOD__;
    }
}