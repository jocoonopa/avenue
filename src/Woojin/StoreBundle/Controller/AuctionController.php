<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Woojin\StoreBundle\Entity\Auction;

/**
 * Auction controller.
 *
 * @Route("/auction")
 */
class AuctionController extends Controller
{
    /**
     * Update Auction sold_at column
     *
     * @Route("/sold_at/{id}", name="auction_update_soldat", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("PUT")
     */
    public function updateSoldAtAction(Auction $auction, Request $request)
    {
        if (Auction::STATUS_SOLD !== $auction->getStatus()) {
            $response = new Response('This auction is not allowed to edit soldAt');

            return $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * 目前登入的使用者實體
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * Session
         *
         * @var \Symfony\Component\HttpFoundation\Session\Session
         */
        $session = $this->get('session');

        try {
            $auction->updateSoldAt(new \DateTime($request->request->get('sold_at')), $user);

            $em->persist($auction);
            $em->flush();

            $session->getFlashBag()->add('success', '競拍售出時間更新完成!');
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $auction->getProduct()->getId())));
    }

    /**
     * Export profit excel report
     *
     * @Route("/export_profit", name="auction_export_profit", options={"expose"=true})
     * @Method("POST")
     */
    public function exportProfitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $auctions = $em->getRepository('WoojinStoreBundle:Auction')->findByCriteria($this->convertCriteria($request->request->all()));

        return $this->get('exporter.bsoprofit')->create($auctions)->getResponse();
    }

    protected function convertCriteria(array $criteria)
    {
        $storesStr = array_key_exists('stores_str', $criteria) && !empty($criteria['stores_str']) ? $criteria['stores_str'] : NULL;
        $acStr = array_key_exists('auction_statuses_str', $criteria) && !empty($criteria['auction_statuses_str']) ? $criteria['auction_statuses_str'] : NULL;

        $criteria['stores'] = NULL === $storesStr ? array() : explode(',', $storesStr);
        $criteria['auction_statuses'] = NULL === $acStr ? array() : explode(',', $acStr);

        return $criteria;
    }

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

    /**
     *  Profit view template
     *
     * @Route("/template/profit", name="auction_template_profit", options={"expose"=true})
     * @Template()
     * @Method("GET")
     */
    public function templateProfitAction()
    {
        return array();
    }
}
