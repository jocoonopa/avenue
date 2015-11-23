<?php

namespace Woojin\ApiBundle\Controller;

//Third Party
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\GoodsBundle\Entity\Behalf;
use Woojin\Utility\Handler\ResponseHandler;

class BehalfController extends Controller
{
    /**
     * @Route("/behalf/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_behalf_show", options={"expose"=true})
     * @ParamConverter("behalf", class="WoojinGoodsBundle:Behalf")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得代購資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity Behalf's id"
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
    public function showAction(Request $request, $behalf, $_format)
    {
        if ($behalf->getCustom()->getId() !== $this->get('session.custom')->current()->getId()) {
            throw new AccessDeniedHttpException('Not Your own behalf');
        }

        if ($want = $behalf->getWant()) {
            $want->setCost(0);
        }

        if ($got = $behalf->getGot()) {
            $got->setCost(0);
        }

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonBehalf = $serializer->serialize($behalf, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonBehalf, $_format);
    }
}
