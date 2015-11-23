<?php

namespace Woojin\OrderBundle\Controller;

use Woojin\Utility\Avenue\Avenue;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
			'_token' => $this->get('form.csrf_provider')->generateCsrfToken('unknown')
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
		return array('_token' => $this->get('form.csrf_provider')->generateCsrfToken('unknown'));
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

		$user = $this->get('security.context')->getToken()->getUser();

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

		return array(
			'homeGoodses' => $homeGoodses,
			'fromOwnGoodses' => $fromOwnGoodses
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
}