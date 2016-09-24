<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Auction controller.
 *
 * @Route("/auction")
 */
class AuctionController extends Controller
{
    /**
     * SPA template
     *
     * @Route("/", name="auction")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
    * List template
    *
    * @Route("/template/list", name="auction_template_list", options={"expose"=true})
    * @Method("GET")
    * @Template()
    */
    public function templateListAction()
    {
        return array();
    }

    /**
     * Sold template
     *
     * @Route("/template/sold", name="auction_template_sold", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function templateSoldAction()
    {
        return array();
    }
}
