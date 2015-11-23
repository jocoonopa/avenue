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

use Woojin\GoodsBundle\Entity\GoodsStatus;
use Woojin\Utility\Handler\ResponseHandler;

class GoodsStatusController extends Controller
{
    /**
     * @Route("/goods_status/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_goodsStatus_show", options={"expose"=true})
     * @ParamConverter("goodsStatus", class="WoojinGoodsBundle:GoodsStatus")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得商品狀態資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity GoodsStatus's id"
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
    public function showAction(Request $request, $goodsStatus, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonGoodsStatus = $serializer->serialize($goodsStatus, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonGoodsStatus, $_format);
    }

    /**
     * @Route("/goods_status/{_format}", name="api_goodsStatus_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有商品狀態的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $goodsStatuses = $em->getRepository('WoojinGoodsBundle:GoodsStatus')->findAll();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonGoodsStatuss = $serializer->serialize($goodsStatuses, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonGoodsStatuss, $_format);
    }
}
