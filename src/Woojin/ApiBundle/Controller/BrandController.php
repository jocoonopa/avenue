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

use Woojin\GoodsBundle\Entity\Brand;
use Woojin\Utility\Handler\ResponseHandler;

class BrandController extends Controller
{
    /**
     * @Route("/brand/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_brand_show", options={"expose"=true})
     * @ParamConverter("brand", class="WoojinGoodsBundle:Brand")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得品牌資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity Brand's id"
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
    public function showAction(Request $request, $brand, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonBrand = $serializer->serialize($brand, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonBrand, $_format);
    }

    /**
     * @Route("/brand/{_format}", name="api_brand_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有品牌的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $brands = $em->getRepository('WoojinGoodsBundle:Brand')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonBrands = $serializer->serialize($brands, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonBrands, $_format);
    }
}
