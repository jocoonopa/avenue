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
            new \Twig_SimpleFunction('isMobile', array($this, 'isMobile')) 
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