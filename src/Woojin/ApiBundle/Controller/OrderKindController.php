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

use Woojin\OrderBundle\Entity\OrderKind;
use Woojin\Utility\Handler\ResponseHandler;

class OrderKindController extends Controller
{
    /**
     * @Route("/order_kind/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_orderKind_show", options={"expose"=true})
     * @ParamConverter("orderKind", class="WoojinOrderBundle:OrdersKind")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得訂單狀態資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity OrderKind's id"
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
    public function showAction(Request $request, $orderKind, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonOrderKind = $serializer->serialize($orderKind, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonOrderKind, $_format);
    }

    /**
     * @Route("/order_kind/{_format}", name="api_orderKind_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有訂單狀態的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $orderKinds = $em->getRepository('WoojinOrderBundle:OrdersKind')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonOrderKinds = $serializer->serialize($orderKinds, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonOrderKinds, $_format);
    }
}
