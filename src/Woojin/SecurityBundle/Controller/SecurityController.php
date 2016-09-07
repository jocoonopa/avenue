<?php

namespace Woojin\SecurityBundle\Controller;

use Woojin\UserBundle\Entity\User;
use Woojin\StoreBundle\Entity\Store;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $helper = $this->get('security.authentication_utils');

        return  array(
            'last_username' => $helper->getLastUsername(),
            'error'         => $helper->getLastAuthenticationError(),
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