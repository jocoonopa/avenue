<?php

namespace Woojin\ApiBundle\Controller;

//Third Party
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Handler\ResponseHandler;

class UserController extends Controller
{
    use HelperTrait;

    /**
     * @Route("/user/rolelist/{_format}",  defaults={"_format"="json"}, name="api_user_rolelist", options={"expose"=true})
     * @Method("GET")
     */
    public function roleListAction($_format)
    {
        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->_getResponse($user->getRoleH(), $_format);
    }
}
