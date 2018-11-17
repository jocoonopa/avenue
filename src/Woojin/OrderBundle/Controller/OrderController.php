<?php

namespace Woojin\OrderBundle\Controller;

use Woojin\Utility\Avenue\Avenue;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Handler\ResponseHandler;

/**
 * @Route("/order")
 */
class OrderController extends Controller
{
	/**
	 * @Route("/", name="order")
	 * @Template("WoojinOrderBundle:Orders:index.html.twig")
	 */
	public function indexAction()
	{
        if ($user->getIsPartner()) {
            return $this->redirect($this->generateUrl('wholesale_index'), 301);
        }
        
		$em = $this->getDoctrine()->getManager();

		return array(
			'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findBy(array(), array('name' => 'ASC')),
			'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findBy(array(), array('name' => 'ASC')),
			'colors' => $em->getRepository('WoojinGoodsBundle:Color')->findAll(),
			'mts' => $em->getRepository('WoojinGoodsBundle:GoodsMT')->findAll(),
			'sources' => $em->getRepository('WoojinGoodsBundle:GoodsSource')->findAll(),
			'levels' =>  $em->getRepository('WoojinGoodsBundle:GoodsLevel')->findBy(array(), array('id' => 'DESC')),
			'categorys' => $em->getRepository('WoojinGoodsBundle:Category')->findAll(),
			'seoSlogans' => $em->getRepository('WoojinGoodsBundle:SeoSlogan')->findAll(),
			'paytypes' => $em->getRepository('WoojinOrderBundle:PayType')->findAll(),
			'_token' => $this->get('security.csrf.token_manager')->getToken('unknown')
		);
	}

	/**
	 * @Route("/multisale_protected", name="order_multisale")
	 * @Template("WoojinOrderBundle:Orders:multisale.html.twig")
	 */
	public function orderMultiSaleAction ()
	{
		ini_set('memory_limit', '512M');

		$em = $this->getDoctrine()->getManager();

		return array(
			'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findAll(),
			'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findAll(),
			'colors' => $em->getRepository('WoojinGoodsBundle:Color')->findAll()
		);
	}

	/**
	 * @Route("/special/sell", name="order_special_sale")
	 * @Template("WoojinOrderBundle:Orders:specialSell.html.twig")
	 */
	public function orderSpecialSellAction ()
	{
		return array('_token' => $this->get('security.csrf.token_manager')->getToken('unknown'));
	}

	/**
	 * @Route("/consign_done/list", name="order_consign_done_list", options={"expose"=true})
	 * @Template("WoojinOrderBundle:Orders/Consign:doneList.html.twig")
	 * @Method("GET")
	 */
	public function orderAjaxInformConsignDoneAction()
	{
		$em = $this->getDoctrine()->getManager();

		$qb = $em->createQueryBuilder();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		/**
		 * 目前店內寄賣已經售出，但尚未給寄賣客戶回扣的商品
		 *
		 * @var array(\Woojin\GoodsBundle\Entity\GoodsPassport)
		 */
		$homeGoodses = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->getConsignCompleteAtHome($user);

		/**
		 * 他店來自我們店的寄賣商品
		 *
		 * @var array(\Woojin\GoodsBundle\Entity\GoodsPassport)
		 */
		$fromOwnGoodses = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->getConsignFromOurStore($user);

		/**
		 * 付清但尚未分派毛利的Auction
		 *
		 * @var array(\Woojin\StoreBundle\Entity\Auction)
		 */
		$auctions = $em->getRepository('WoojinStoreBundle:Auction')->fetchPaidCompleted($user);

		return array(
			'homeGoodses' => $homeGoodses,
			'fromOwnGoodses' => $fromOwnGoodses,
			'auctions' => $auctions
		);
	}

	/**
	 * @Route("/ajax/orders/one/info", name="orders_ajax_one_info", options={"expose"=true})
	 * @Template("WoojinOrderBundle:Orders:orders.ajax.edit.res.html.twig")
	 * @Method("POST")
	 */
	public function ordersAjaxOneInfoAction(Request $request)
	{
		foreach ($request->request->keys() as $key) {
			$$key = $request->request->get($key);
		}

		return array('oOrders' => $em->find('WoojinOrderBundle:Orders', $nOrdersId));
	}

	/**
     * @Route("/custom_orders_jsonfetch", name="admin_customs_orders_jsonfetch", options={"expose"=true})
     */
    public function ordersJsonfetchAction(Request $request)
    {
    	$mobil = $request->query->get('mobil', '123456789');

    	$em = $this->getDoctrine()->getManager();

        /**
         * 一般訂單
         * @var [type]
         */
        $qb = $em->createQueryBuilder();
        $qb
            ->select(['o', 'c', 'ope', 'u', 'g', 'k', 's'])
            ->from('WoojinOrderBundle:Orders', 'o')
            ->leftJoin('o.custom', 'c')
            ->leftJoin('o.kind', 'k')
             ->leftJoin('o.status', 's')
            ->leftJoin('o.opes', 'ope')
            ->leftJoin('ope.user', 'u')
            ->leftJoin('o.goods_passport', 'g')
        ;

        if (empty($mobil)) {
            $qb->where(
                $qb->expr()->eq('c.name', $qb->expr()->literal($request->query->get('name'))),
                $qb->expr()->eq('c.mobil', $qb->expr()->literal('')),
                $qb->expr()->gt('g.id', 0)
            );
        } else {
            $qb->where(
                $qb->expr()->eq('c.mobil', $qb->expr()->literal($mobil)),
                $qb->expr()->gt('g.id', 0)
            );
        }

        $qb
            ->orderBy('o.id', 'desc')
            ->orderBy('ope.id', 'desc')
            ->groupBy('ope.id')
        ;

        $orders = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        /**
         * BSO競拍
         */
        $qb = $em->createQueryBuilder();
        $qb
            ->select(['au', 'p', 'b', 's'])
            ->from('WoojinStoreBundle:Auction', 'au')
            ->leftJoin('au.product', 'p')
            ->leftJoin('au.buyer', 'b')
            ->leftJoin('au.bsser', 's')
        ;

        if (empty($mobil)) {
            $qb
                ->where(
                    $qb->expr()->eq('b.mobil', $qb->expr()->literal('')),
                    $qb->expr()->eq('b.name', $qb->expr()->literal($request->query->get('name')))
                )
                ->orderBy('au.id', 'desc')
            ;
        } else {
            $qb
                ->where(
                    $qb->expr()->eq('b.mobil', $qb->expr()->literal($mobil))
                )
                ->orderBy('au.id', 'desc')
            ;
        }

        $auctions = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $data = [
        	'orders' => $orders,
        	'auctions' => $auctions,
        ];

        $returnMsg = [
        	'message' => 'Fetched completedly',

        	'data' => $data,
        ];

        $data = json_encode($returnMsg);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $data, 'json');
    }
}