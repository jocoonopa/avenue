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

use Woojin\OrderBundle\Entity\Invoice;
use Woojin\Utility\Handler\ResponseHandler;

class InvoiceController extends Controller
{
    /**
     * @Route("/invoice/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_invoice_show", options={"expose"=true})
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("GET")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="取得發票資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id", 
     *          "requirement"="\d+",
     *          "dataType"="integer", 
     *          "required"=true, 
     *          "description"="Entity Invoice's id"
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
    public function showAction(Request $request, $invoice, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $jsonInvoice = $serializer->serialize($invoice, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonInvoice, $_format);
    }

    /**
     * @Route("/invoice/{_format}", name="api_invoice_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  description="取得所有發票的資料"
     * )
     */
    public function listAction(Request $request,$_format)
    {
        $em = $this->getDoctrine()->getManager();

        $invoices = $em->getRepository('WoojinOrderBundle:Invoice')->findBy(array(), array('name' => 'ASC'));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonInvoices = $serializer->serialize($invoices, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getETag($request, $jsonInvoices, $_format);
    }
}
