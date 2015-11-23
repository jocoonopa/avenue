<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CommonController extends Controller
{
    /**
     * @Route("/common/header", name="front_common_header")
     * @Template()
     */
    public function headerAction()
    {
        $em = $this->getDoctrine()->getManager();

        return array(
            'categorys' => $em->getRepository('WoojinGoodsBundle:Category')->findAll(),
            'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findValid(),
            'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findValid(),
            'promotions' => $em->getRepository('WoojinGoodsBundle:Promotion')->findValid()
        );
    }

    /**
     * @Route("/common/footer", name="front_common_footer")
     * @Template()
     */
    public function footerAction()
    {
        $em = $this->getDoctrine()->getManager();

        return array(
            'stores' => $em->getRepository('WoojinStoreBundle:Store')->findBy(array('isShow' => true))
        );
    }
}
