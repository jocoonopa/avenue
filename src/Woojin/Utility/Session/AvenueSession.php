<?php 

namespace Woojin\Utility\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

class AvenueSession
{
    protected $container;

    protected $context;

    public function __construct(ContainerInterface $container, SecurityContext $context)
    {
        $this->context = $context;

        $this->container = $container;

        $this->session = $this->init();
    }

    protected function init()
    {
        return (!$this->container->get('session')->isStarted()) 
            ? new Session() 
            : $this->container->get('session');
    }

    public function get()
    {
        return $this->session;
    }
}