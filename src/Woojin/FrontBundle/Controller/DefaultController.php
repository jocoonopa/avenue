<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Avenue\Avenue;
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\GoodsBundle\Entity\GoodsPassport;

class DefaultController extends Controller
{
    /**
     * @Route("", name="front_index", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $mobileDetector = $this->get('resolver.device');
        $mobileDetector->setForce($request);

        if ($mobileDetector->isM()) {
            return $this->redirect($this->generateUrl('mobile_front_index'));
        }

        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->getRandOnSale(20);

        $tls = $em->getRepository('WoojinGoodsBundle:ProductTl')->findNotExpired();

        $promotions = $em->getRepository('WoojinGoodsBundle:Promotion')->findValid();

        return array(
            'goodses' => $products,
            'tls' => $tls,
            // 原本的限時搶購 timeliness.html.twig
            'latestPromotion' => ((isset($promotions[0])) ? $promotions[0] : null),
            'promotions' => $promotions
        );
    }

    /**
     * @Route("/product/{id}", requirements={"id" = "\d+"}, name="front_product_show", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
     * @Template()
     */
    public function productAction(Request $request, GoodsPassport $goods)
    {
        // if (!$goods->getIsAllowWeb() || !in_array($goods->getStatus()->getId(), array(Avenue::GS_ONSALE, Avenue::GS_SOLDOUT, Avenue::GS_ACTIVITY, Avenue::GS_BEHALF) )) {
        //     throw $this->createNotFoundException('The product does not exist');
        // }

        if (!$goods->getIsAllowWeb()) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $mobileDetector = $this->get('resolver.device');
        $mobileDetector->setForce($request);

        if ($mobileDetector->isM()) {
            return $this->redirect($this->generateUrl('mobile_front_product', array('id' => $goods->getId())));
        }

        $historys = array();
        $relatives = array();

        $em = $this->getDoctrine()->getManager();

        $relatives = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->getRandOnSale(10);

        $historyIds = json_decode($request->cookies->get('avenueHistory', '[]'), true);
        $historyIds = array_unique($historyIds);
        $historyIds = array_values($historyIds);

        if (!empty($historyIds)) {
            $qb = $em->createQueryBuilder();
            $historys = $qb
                ->select('g')
                ->from('WoojinGoodsBundle:GoodsPassport', 'g')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->in('g.id', $historyIds),
                        $qb->expr()->eq('g.isAllowWeb', true)
                    )
                )
                ->groupBy('g.id')
                ->getQuery()
                ->getResult()
            ;

            $returns = array();

            for ($i = 0; $i < count($historys); $i ++) {
                $returns[$i] = null;
            }

            foreach ($historys as $key => $history) {
                $pos = array_search($history->getId(), $historyIds);

                if ($pos !== false) {
                    $returns[$pos] = $history;
                }
            }

            $historys = $returns;
        }

        return array(
            'goods' => $goods,
            'relatives' => $relatives,
            'historys' => $historys
        );
    }

    /**
     * @Route("/history", name="front_timeline")
     * @Method("GET")
     * @Template()
     */
    public function timelineAction()
    {
        return array();
    }

    /**
     * @Route("/store", name="front_storeIntro")
     * @Method("GET")
     * @Template()
     */
    public function storeIntroAction()
    {
        $em = $this->getDoctrine()->getManager();

        return array('stores' => $em->getRepository('WoojinStoreBundle:Store')->findIsShow());
    }

    /**
     * @Route("/testNotifier/ship/{id}", name="front_testNotifier_ship")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("GET")
     * @Template(":Email/Custom:ship.html.twig")
     */
    public function testNotifierAction(Invoice $invoice)
    {
        // $notifier = $this->get('avenue.notifier');
        // $notifier->ship($invoice);

        // return new Response('123');
    }

    /**
     * @Route("/testFbSDK", name="front_testFbSDK")
     * @Method("GET")
     */
    public function testFbSDKAction()
    {
        $fbParam = $this->container->getParameter('fb');

        $fb = new Facebook\Facebook([
            'app_id' => $fbParam['app_id'],
            'app_secret' => $fbParam['app_secret'],
            'default_graph_version' => $fbParam['app_version'],
        ]);

        $helper = $fb->getJavaScriptHelper();
        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (isset($accessToken)) {
          // Logged in
        }
        return new Response('ok');
    }

    /**
     * @Route("/countdown", name="front_countdown")
     * @Method("GET")
     * @Template()
     */
    public function countdownAction()
    {
        return array();
    }
}
