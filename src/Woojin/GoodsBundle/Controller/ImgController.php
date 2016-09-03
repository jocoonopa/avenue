<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\Utility\Avenue\Avenue;

/**
 * Behalf controller.
 *
 * @Route("/img")
 */
class ImgController extends Controller
{
    const NUM_PERPAGE = 50;

    /**
     * @Route("/test", name="admin_img_test")
     * @Method("GET")
     */
    public function testAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $img = $em->find('WoojinGoodsBundle:Img', 10364);

        $imgFactory = $this->get('factory.img');
        $imgFactory->createRemoveBorder($img);

        return new Response($img->getPathNoBorder());
    }
}



