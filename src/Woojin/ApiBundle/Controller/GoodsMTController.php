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

use Woojin\GoodsBundle\Entity\GoodsMT;
use Woojin\Utility\Handler\ResponseHandler;

class GoodsMTController extends Controller
{
    /**
     * @Route("/mt/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_mt_show", options={"expose"=true})
     * @ParamConverter("mt", class="WoojinGoodsBundle:GoodsMT")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得材質資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity GoodsMT's id"
     *      },
     *      {
     *          "name"="_format", 
     *          "dataType"="string", 
     *          "required"=false, 
     *          "description"="回傳的格式，支援 json, xml, html"
     *      }
     *  }
     * )
     */
    public function showAction(Request $request, $mt, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonGoodsMT = $serializer->serialize($mt, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonGoodsMT, $_format);
    }

    /**
     * @Route("/mt/{_format}", name="api_mt_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有材質的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $mts = $em->getRepository('WoojinGoodsBundle:GoodsMT')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonGoodsMTs = $serializer->serialize($mts, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonGoodsMTs, $_format);
    }
}
