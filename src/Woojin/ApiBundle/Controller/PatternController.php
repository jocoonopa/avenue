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

use Woojin\GoodsBundle\Entity\Pattern;
use Woojin\Utility\Handler\ResponseHandler;

class PatternController extends Controller
{
    /**
     * @Route("/pattern/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_pattern_show", options={"expose"=true})
     * @ParamConverter("pattern", class="WoojinGoodsBundle:Pattern")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得款式資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity Pattern's id"
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
    public function showAction(Request $request, $pattern, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonPattern = $serializer->serialize($pattern, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonPattern, $_format);
    }

    /**
     * @Route("/pattern/{_format}", name="api_pattern_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有款式的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $patterns = $em->getRepository('WoojinGoodsBundle:Pattern')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonPatterns = $serializer->serialize($patterns, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonPatterns, $_format);
    }
}
