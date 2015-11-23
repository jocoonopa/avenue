<?php

namespace Woojin\SecurityBundle\Controller;

use Woojin\UserBundle\Entity\User;
use Woojin\StoreBundle\Entity\Store;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class SecurityController extends Controller
{
    /**
    * @Route("/login", name="login")
    * @Template("WoojinSecurityBundle:Security:login.html.twig")
    */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        //$test = new User();
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return  array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
        );
    }

    /**
    * @Route("/login_check", name="login_check")
    */
    public function loginCheckAction()
    {

    }

    /**
    * @Route("/logout", name="logout")
    */
    public function logoutAction()
    {

    }
}