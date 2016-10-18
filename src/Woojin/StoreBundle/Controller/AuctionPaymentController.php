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
use Woojin\StoreBundle\Entity\AuctionPayment;
use Woojin\UserBundle\Entity\User;

/**
 * Auction controller.
 *
 * @Route("/auction_payment")
 */
class AuctionPaymentController extends Controller
{
    /**
     * @Route("/auction/{id}", name="auction_payment_create", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("POST")
     */
    public function createAction(Auction $auction, Request $request)
    {
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
         * @var \Woojin\OrderBundle\Entity\PayType
         */
        $payType = $em->getRepository('WoojinOrderBundle:PayType')->find($request->request->get('pay_type'));
        /**
         * Amount
         *
         * @var integer
         */
        $amount = (int) $request->request->get('amount');
        /**
         * @var \DateTime
         */
        $paidAt = new \DateTime($request->request->get('paid_at'));
        /**
         * Session
         *
         * @var \Symfony\Component\HttpFoundation\Session\Session
         */
        $session = $this->get('session');
        /**
         * @var \Woojin\Service\Store\AuctionPaymentService
         */
        $service = $this->get('auction.payment.service');

        try {
            $payment = $service->create(array(
                'auction' => $auction,
                'payType' => $payType,
                'amount' => $amount,
                'creater' => $user,
                'paidAt' => $paidAt
            ));

            $em->persist($payment);
            $em->flush();

            $session->getFlashBag()->add('success', '競拍付款新增成功!');
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $auction->getProduct()->getId())) . '/#_soldop');
    }

    /**
     * @Route("/{id}", name="auction_payment_drop", options={"expose"=true})
     * @ParamConverter("payment", class="WoojinStoreBundle:AuctionPayment")
     * @Method("DELETE")
     */
    public function dropAction(AuctionPayment $payment)
    {
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
        /**
         * @var \Woojin\Service\Store\AuctionPaymentService
         */
        $service = $this->get('auction.payment.service');
        try {
            $payment = $service->drop($payment, array(
                'canceller' => $user
            ));

            $em->persist($payment);
            $em->flush();

            $session->getFlashBag()->add('success', '競拍付款取消完成!');
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $payment->getAuction()->getProduct()->getId())) . '/#_soldop');
    }

    /**
     * @Route("/{id}", name="auction_payment_update", options={"expose"=true})
     * @ParamConverter("payment", class="WoojinStoreBundle:AuctionPayment")
     * @Method("PUT")
     */
    public function updateAction(AuctionPayment $payment, Request $request)
    {
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
        /**
         * @var \Woojin\Service\Store\AuctionPaymentService
         */
        $service = $this->get('auction.payment.service');
        /**
         * @var \DateTime
         */
        $paidAt = new \DateTime($request->request->get('paid_at'));

        try {
            $payment = $service->update($payment, array(
                'updater' => $user,
                'paidAt' => $paidAt
            ));

            $em->persist($payment);
            $em->flush();

            $session->getFlashBag()->add('success', '競拍付款時間更新完成!');
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $payment->getAuction()->getProduct()->getId())) . '/#_soldop');
    }
}
