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

use Woojin\GoodsBundle\Entity\Color;
use Woojin\Utility\Handler\ResponseHandler;

class ColorController extends Controller
{
    /**
     * @Route("/color/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_color_show", options={"expose"=true})
     * @ParamConverter("color", class="WoojinGoodsBundle:Color")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得顏色資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity Color's id"
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
    public function showAction(Request $request, $color, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonColor = $serializer->serialize($color, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonColor, $_format);
    }

    /**
     * @Route("/color/{_format}", name="api_color_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有顏色的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $colors = $em->getRepository('WoojinGoodsBundle:Color')->findAll();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonColors = $serializer->serialize($colors, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonColors, $_format);
    }
}
