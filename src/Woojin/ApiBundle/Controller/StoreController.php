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

use Woojin\StoreBundle\Entity\Store;
use Woojin\Utility\Handler\ResponseHandler;

class StoreController extends Controller
{
    /**
     * @Route("/store/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_store_show", options={"expose"=true})
     * @ParamConverter("store", class="WoojinStoreBundle:Store")
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="取得商店資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id",
     *          "requirement"="\d+",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="Entity Store's id"
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
    public function showAction(Request $request, $store, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonStore = $serializer->serialize($store, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $jsonStore, $_format);
    }

    /**
     * @Route("/store/{_format}", name="api_store_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有商店的資料"
     * )
     */
    public function listAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $stores = $em->getRepository('WoojinStoreBundle:Store')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonStores = $serializer->serialize($stores, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $jsonStores, $_format);
    }

    /**
     * @Route("/store_valid/{_format}", name="api_store_valid_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有valid商店的資料"
     * )
     */
    public function validListAction(Request $request, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $stores = $em->getRepository('WoojinStoreBundle:Store')->findBsoOptions();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonStores = $serializer->serialize($stores, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $jsonStores, $_format);
    }
}
