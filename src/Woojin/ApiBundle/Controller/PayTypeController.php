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

class PayTypeController extends Controller
{
    use HelperTrait;

    /**
     * @Route("/paytype/{_format}", name="api_paytype_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  description="取得所有訂單狀態的資料"
     * )
     */
    public function listAction($_format)
    {
        $em = $this->getDoctrine()->getManager();

        $paytypes = $em->getRepository('WoojinOrderBundle:PayType')->findAll();

        return $this->_getResponse($paytypes, $_format);
    }
}
