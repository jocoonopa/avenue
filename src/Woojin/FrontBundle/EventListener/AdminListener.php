<?php

namespace Woojin\FrontBundle\EventListener;

use Woojin\FrontBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\SecurityContext;

class AdminListener
{
    private $context;

    public function __construct(SecurityContext $context)
    {
        $this->context = $context;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AuthenticatedController) {
            $controller[0]->isValid();
        } 
    }
}