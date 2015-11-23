<?php
// src/AppBundle/EventListener/TokenListener.php
namespace Woojin\FrontBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Woojin\FrontBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Doctrine\ORM\EntityManager;

class TokenListener
{
    private $container;

    private $session;

    private $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;

        $this->session = $this->container->get('session');

        $this->em = $em;
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

        if ($controller[0] instanceof TokenAuthenticatedController) {    
            $this->session = $this->container->get('session');

            $custom = json_decode($this->session->get('custom'));
            
            if (!$custom) {
                return;
            }
            
            $custom = $this->em->find('WoojinOrderBundle:Custom', $custom->id);
            if ($custom->getCsrf() !== $this->session->get('avenue_token')) {
                $this->session->clear();

                $url = $this->container->get('router')->generate('front_custom_login', null, true);

                throw new AccessDeniedHttpException('憑證失效，請前往' . $url . '重新登入會員');
            }        

            // mark the request as having passed token authentication
            $event->getRequest()->attributes->set('auth_token', true);
        } else {
            $event->getRequest()->attributes->set('auth_token', null);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$token = $event->getRequest()->attributes->get('auth_token')) {
            return;
        }

        $this->session = $this->container->get('session');

        $custom = json_decode($this->session->get('custom'));
        
        if (!$custom) {
            return;
        }

        $custom = $this->em->find('WoojinOrderBundle:Custom', $custom->id);
        $custom
            ->setCsrf('avenue2003')
            ->setPreCsrf($this->session->get('avenue_token'))
        ;
        $this->em->persist($custom);
        $this->em->flush();

        $this->session->set('avenue_token', $custom->getCsrf());

        return;
    }
}