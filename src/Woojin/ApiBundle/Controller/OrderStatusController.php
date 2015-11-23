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

use Woojin\OrderBundle\Entity\OrderStatus;
use Woojin\Utility\Handler\ResponseHandler;

class OrderStatusController extends Controller
{
    /**
     * @Route("/order_status/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_orderStatus_show", options={"expose"=true})
     * @ParamConverter("orderStatus", class="WoojinOrderBundle:OrdersStatus")
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
     *          "description"="Entity OrderStatus's id"
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
    public function showAction(Request $request, $orderStatus, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonOrderStatus = $serializer->serialize($orderStatus, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonOrderStatus, $_format);
    }

    /**
     * @Route("/order_status/{_format}", name="api_orderStatus_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有訂單狀態的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $orderStatuss = $em->getRepository('WoojinOrderBundle:OrdersStatus')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonOrderStatuss = $serializer->serialize($orderStatuss, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonOrderStatuss, $_format);
    }
}
