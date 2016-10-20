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
use Woojin\StoreBundle\Entity\AuctionShipping;
use Woojin\UserBundle\Entity\User;
use Woojin\ApiBundle\Controller\HelperTrait;

/**
 * Auction controller.
 *
 * @Route("/auction")
 */
class AuctionController extends Controller
{
    use HelperTrait;

    /**
     * Bind(Update) auction's shipping
     *
     * @Route("/{id}/shipping", name="auction_update_shipping", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("PUT")
     */
    public function updateShipping(Auction $auction, Request $request)
    {
        /**
         * 目前登入的使用者實體
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$auction->isAllowedEditPayment($user)) {
            throw $this->createAccessDeniedException('You cannot access this page!');
        }

        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();
        
        try {
            $shipping = $auction->getShipping();

            if (is_null($shipping) || !($shipping instanceof AuctionShipping)) {
                $shipping = new AuctionShipping();
                $auction->setShipping($shipping);
            }

            $shippingOption = $em->getRepository('WoojinStoreBundle:ShippingOption')->find($request->request->get('shipping'));
            if (is_null($shippingOption)) {
                throw $this->createNotFoundException('No shippingOptions has found!');
            }

            $shipping
                ->setOption($shippingOption)
                ->addMemo($user, $shippingOption)
            ;
            $shipping->setAuction($auction);

            $em->persist($shipping);
            $em->persist($auction);
            $em->flush();
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->_getResponse($shipping, 'json');
    }

    /**
     * Update Auction sold_at column
     *
     * @Route("/sold_at/{id}", name="auction_update_soldat", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("PUT")
     */
    public function updateSoldAtAction(Auction $auction, Request $request)
    {
        /**
         * 目前登入的使用者實體
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$auction->isAllowedEditSoldAt($user)) {
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
     * Update Auction sold_at column
     *
     * @Route("/profit/assign/{id}", name="auction_profit_assign", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("PUT")
     */
    public function updateProfitAssignAction(Auction $auction)
    {
        /**
         * 目前登入的使用者實體
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$auction->isAllowedAssignProfit($user)) {
            throw $this->createAccessDeniedException("You cannot assign profit of auction, might be you are not belong the auction's createStore?");
        }

        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * Session
         *
         * @var \Symfony\Component\HttpFoundation\Session\Session
         */
        $session = $this->get('session');

        try {
            $auction->assignProfit($user);

            $em->persist($auction);
            $em->flush();

            $msg = "{$auction->getProduct()->getSn()}由{$user->getUsername()}於{$auction->getAssignCompleteAt()->format('Y-m-d H:i:s')}完成分配競拍毛利!";

            $session->getFlashBag()->add('success', $msg);
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('order_consign_done_list'));
    }

    /**
     * Export profit excel report
     *
     * @Route("/export_profit", name="auction_export_profit", options={"expose"=true})
     * @Method("POST")
     */
    public function exportProfitAction(Request $request)
    {
        /**
         * 目前登入的使用者實體
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user->getRole()->hasAuth('BSO_VIEW_BELONG_PROFIT')) {
            return $this->createAccessDeniedException('You cannot access this page!');
        }
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * Fetch auctions we would return into response
         */
        $auctions = array_map(
            '\Woojin\StoreBundle\Entity\Auction::initVirtualProperty', 
            $em->getRepository('WoojinStoreBundle:Auction')->findByCriteria($this->convertCriteria($request->request->all(), $user))
        );

        return $this->get('exporter.bsoprofit')->create($auctions)->getResponse();
    }

    protected function convertCriteria(array $criteria, User $user)
    {
        $storesStr = array_key_exists('stores_str', $criteria) && !empty($criteria['stores_str']) ? $criteria['stores_str'] : NULL;
        $acStr = array_key_exists('auction_statuses_str', $criteria) && !empty($criteria['auction_statuses_str']) ? $criteria['auction_statuses_str'] : NULL;
        $apcStr = array_key_exists('auction_profit_statuses_str', $criteria) && !empty($criteria['auction_profit_statuses_str']) ? $criteria['auction_profit_statuses_str'] : NULL;

        $criteria['stores'] = is_null($storesStr) ? array() : explode(',', $storesStr);
        $criteria['stores'] = $user->getRole()->hasAuth('BSO_VIEW_ALL_PROFIT') ? $criteria['stores'] : $user->getStore()->getId();
        $criteria['auction_statuses'] = is_null($acStr) ? array() : explode(',', $acStr);
        $criteria['auction_profit_statuses'] = is_null($apcStr) ? array() : explode(',', $apcStr);

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
