<?php

namespace Woojin\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class AuctionPaymentController extends Controller
{
    use AuctionTrait;

    /**
     * @Route("/auction_payment/{_format}", defaults={"_format"="json"}, name="api_auction_payment_create", options={"expose"=true})
     * @Method("POST")
     */
    public function create()
    {
    }

    /**
     * @Route("/auction_payment/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_auction_payment_update", options={"expose"=true})
     * @ParamConverter("auctionPayment", class="WoojinStoreBundle:AuctionPayment")
     * @Method("PUT")
     */
    public function update()
    {
    }

    /**
     * @Route("/auction_payment/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_auction_payment_delete", options={"expose"=true})
     * @ParamConverter("auctionPayment", class="WoojinStoreBundle:AuctionPayment")
     * @Method("DELETE")
     */
    public function delete()
    {
    }

    /**
     * @Route("/auction_payment/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_auction_payment_show", options={"expose"=true})
     * @ParamConverter("auctionPayment", class="WoojinStoreBundle:AuctionPayment")
     * @Method("GET")
     */
    public function show($payment, $_format)
    {
    }
}
