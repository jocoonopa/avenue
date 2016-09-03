<?php

namespace Woojin\BaseBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;  

class BaseExtension extends \Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'isMobile' => new \Twig_Function_Method($this, 'isMobile'),           
        );
    }

    public function getName()
    {
        return 'base_extension';
    }

    public function isMobile()
    {
        return $this->container->get('base_method')->isMobile();
    }
}