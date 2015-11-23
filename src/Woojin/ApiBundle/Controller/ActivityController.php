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

use Woojin\StoreBundle\Entity\Activity;
use Woojin\Utility\Handler\ResponseHandler;

class ActivityController extends Controller
{
    /**
     * @Route("/activity/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_activity_show", options={"expose"=true})
     * @ParamConverter("activity", class="WoojinStoreBundle:Activity")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得活動資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity Activity's id"
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
    public function showAction(Request $request, $activity, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonActivity = $serializer->serialize($activity, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonActivity, $_format);
    }

    /**
     * @Route("/activity/{_format}", name="api_activity_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有活動的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $activitys = $em->getRepository('WoojinStoreBundle:Activity')->findAll();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonActivitys = $serializer->serialize($activitys, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonActivitys, $_format);
    }
}
