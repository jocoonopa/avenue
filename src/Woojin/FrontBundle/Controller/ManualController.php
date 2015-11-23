<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Avenue\Avenue;

class ManualController extends Controller 
{
    /**
     * @Route("/manual/purchase", name="front_manual_purchase", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function purchaseAction()
    {
        return array();
    }

    /**
     * @Route("/manual/chargeback", name="front_manual_chargeback", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function chargebackAction()
    {
        return array();
    }

    /**
     * 隱私權政策頁面
     *
     * @Route("/privacy", name="front_manual_privacy")
     * @Method("GET")
     * @Template()
     */
    public function privacyAction()
    {
        return array();
    }

    /**
     * 服務條款政策頁面
     *
     * @Route("/terms", name="front_manual_terms")
     * @Method("GET")
     * @Template()
     */
    public function termsAction()
    {
        return array();
    }
}
