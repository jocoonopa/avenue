<?php

namespace Woojin\BaseBundle\Controller;

use Woojin\StoreBundle\Entity\Store;
use Woojin\UserBundle\Entity\UsersLog;
use Woojin\UserBundle\Entity\UserHabit;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class BaseController extends Controller
{
	/**
	 * @Route("/header", name="base_header")
	 * @Template("WoojinBaseBundle:Base:header.html.twig")
	 */
	public function headerAction()
	{
		 return array();
	}

	/**
	* @Route("/footer", name="base_footer")
	* @Template("WoojinBaseBundle:Base:footer.html.twig")
	*/
	public function footerAction()
	{
		$stores = $this->getDoctrine()->getRepository('WoojinStoreBundle:Store')->findAll();

		return array('stores' => $stores);
	}

	/**
	 * @Route("/get_store_select", name="base_get_store_select", options={"expose=true"})
	 * @Template("WoojinBaseBundle:Ajax:store.select.html.twig")
	 */
	public function getStoreSelectAction()
	{
		/**
		 * 商店的collections
		 * 
		 * @var object
		 */
		$stores = $this->getDoctrine()->getRepository('WoojinStoreBundle:Store')->findAll();

		return array('stores' => $stores);
	}

	/**
	 * @Route("/get_goods_level_select", name="base_get_goods_level_select" , options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:goods.level.select.html.twig")
	 */
	public function getGoodsLevelSelectAction()
	{
		$goods_level = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsLevel')->findBy(array(), array('id' => 'desc'));

		return array('goodsLevel' => $goods_level);
	}

	/**
	 * @Route("/get_goods_status_select", name="base_get_goods_status_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:goods.status.select.html.twig")
	 */
	public function getGoodsStatusSelectAction()
	{
		$goods_status = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsStatus')->findAll();

		return array('goodsStatus' => $goods_status);
	}

	/**
	 * @Route("/get/goods_source/select", name="base_get_goodsSource_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:goods.source.select.html.twig")
	 */
	public function getGoodsSourceSelectAction()
	{
		$rGoodsSource = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsSource')->findAll();

		return array('rGoodsSource' =>$rGoodsSource);
	}

	/**
	 * @Route("/get/goods_mt/select", name="base_get_goodsMT_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:goods.mt.select.html.twig")
	 */
	public function getGoodsMTSelectAction()
	{
		$rGoodsMT = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsMT')->findAll();

		return array('rGoodsMT' => $rGoodsMT);
	}

	/**
	 * @Route("/get/activity/select", name="base_get_activity_select", options={"expose"=true})
	 * @Template()
	 */
	public function getActivitySelectAction()
	{
		$rActivity = $this->getDoctrine()->getRepository('WoojinStoreBundle:Activity')->findAll();

		return array('activitys' => $rActivity);
	}

	/**
	 * @Route("/get/goods_source/arr/select", name="base_get_goodsSource_arr_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:goods.source.arr.select.html.twig")
	 */
	public function getGoodsSourceArrSelectAction()
	{
		$rGoodsSource = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsSource')->findAll();

		return array('rGoodsSource' => $rGoodsSource);
	}

	/**
	 * @Route("/color/list/select", name="base_get_color_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:simple.entity.html.twig")
	 */
	public function getColorSelectAction()
	{
		return array(
			'collection' => $this->getDoctrine()->getRepository('WoojinGoodsBundle:Color')->findAll(),
			'name' => 'color[]'
		);
	}

	/**
	 * @Route("/brand/list/select", name="base_get_onlybrand_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:simple.entity.html.twig")
	 */
	public function getOnlyBrandSelectAction()
	{
		return array(
			'collection' => $this->getDoctrine()->getRepository('WoojinGoodsBundle:Brand')->findAll(),
			'name' => 'brand[]'
		);
	}

	/**
	 * @Route("/pattern/list/select", name="base_get_pattern_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:simple.entity.html.twig")
	 */
	public function getPatternSelectAction()
	{
		return array(
			'collection' => $this->getDoctrine()->getRepository('WoojinGoodsBundle:Pattern')->findAll(),
			'name' => 'pattern[]'
		);
	}

	/**
	 * @Route("/get/orders_kind/select", name="base_get_ordersKind_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:orders.kind.select.html.twig")
	 */
	public function getOrdersKindSelectAction()
	{
		$rOrdersKind = $this->getDoctrine()->getRepository('WoojinOrderBundle:OrdersKind')->findAll();

		return array('rOrdersKind' =>$rOrdersKind);
	}

	/**
	 * @Route("/get/orders_status/select", name="base_get_ordersStatus_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:orders.status.select.html.twig")
	 */
	public function getOrdersStatusSelectAction()
	{
		$rOrdersStatus = $this->getDoctrine()->getRepository('WoojinOrderBundle:OrdersStatus')->findAll();

		return array('rOrdersStatus' => $rOrdersStatus);
	}

	/**
	 * @Route("/get_only_brand_select_ajax", name="base_get_only_brand_select_ajax", options={"expose"=true})
	 * @method("GET")
	 */
	public function getOnlyBrandSelectAjaxAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$qb = $em->createQueryBuilder();
		$rBrand = $qb->select('b')->from('WoojinGoodsBundle:Brand', 'b')->getQuery()->getArrayResult();

		return new Response(json_encode($rBrand));
	}

	/**
	 * @Route("/get_payType_select", name="base_get_payType_select", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Ajax:payType.select.html.twig")
	 */
	public function getPayTypeSelectAction()
	{
		$rPayType = $this->getDoctrine()->getRepository('WoojinOrderBundle:PayType')->findAll();

		return array('rPayType' => $rPayType);
	}

	/**
	 * @Route("/custom_form", name="base_custom_form")
	 * @Template("WoojinBaseBundle:Block:custom.form.html.twig")
	 */
	public function customFormAction()
	{
		$_token = $this->get('form.csrf_provider')->generateCsrfToken('unknown');
		
		return array('_token' => $_token);
	}

	/**
	 * @Route("/record_users_log", name="base_record_users_log")
	 * @Template()
	 */
	public function recordUsersLogAction(Request $request)
	{
		$user = $this->get('security.context')->getToken()->getUser();

		$ip = $request->server->get('REMOTE_ADDR');

		$baseMethod = $this->get('base_method');
		if ($baseMethod->isMobile()) {
			return array();
		}

		$usersLog = new UsersLog();
		$usersLog
			->setUser($user)
			->setCreatetime(new \Datetime())
			->setIp($ip)
			->setError('')
		;

		$em = $this->getDoctrine()->getManager();
		$em->persist($usersLog);
		$em->flush();
		
		return $this->redirect($this->generateUrl('order'), 301);
	}
}