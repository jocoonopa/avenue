<?php

namespace Woojin\BaseBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Woojin\StoreBundle\Entity\Store;
use Woojin\UserBundle\Entity\UserHabit;
use Woojin\UserBundle\Entity\UsersLog;
use Woojin\Utility\Avenue\Avenue;

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
		$rActivity = $this->getDoctrine()->getRepository('WoojinStoreBundle:Activity')->findVisible();

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
		$_token = $this->get('security.csrf.token_manager')->getToken('unknown');
		
		return array('_token' => $_token);
	}

	/**
	 * @Route("/wholesale_index", name="wholesale_index", options={"expose"=true})
	 * @Template("WoojinBaseBundle:Wholesale:index.html.twig")
	 */
	public function wholesalerIndex(Request $request)
	{
		$perPage = 50;
		$em =  $this->getDoctrine()->getManager();

		$qb = $em->createQueryBuilder();
		$qb
			->select(array('g', 'i'))
			->from('WoojinGoodsBundle:GoodsPassport', 'g')
			->leftJoin('g.img', 'i')
			->where(
				$qb->expr()->eq('g.isAllowWholesale', true),
				$qb->expr()->notIn('g.status', [
					Avenue::GS_SOLDOUT,
					Avenue::GS_OFFSALE,
					Avenue::GS_BSO_SOLD,
				])
			)
		;

		if (!empty($request->query->get('brand_ids', []))) {
			$qb->andWhere(
				$qb->expr()->in('g.brand', $request->query->get('brand_ids'))
			);
		}

		if (!empty($request->query->get('pattern_ids', []))) {
			$qb->andWhere(
				$qb->expr()->in('g.pattern', $request->query->get('pattern_ids'))
			);
		}

		$qb
			->setFirstResult(($request->query->get('page', 1) - 1) * $perPage)
            ->setMaxResults($perPage)
        ;

		$products = new Paginator($qb, true);

		$qb = $em->createQueryBuilder();
		$brands = $qb->select(array('b'))
            ->from('WoojinGoodsBundle:Brand', 'b')
            ->orderBy('b.name')
            ->getQuery()
            ->getResult()
        ;

        $qb = $em->createQueryBuilder();
		$patterns = $qb->select(array('p'))
            ->from('WoojinGoodsBundle:Pattern', 'p')
            ->orderby('p.name')
            ->getQuery()
            ->getResult()
       ;

	    return [
	    	'products' => $products,
	    	'count' => count($products),
	    	'page' => $request->query->get('page', 1),
	    	'lastpage' => ceil(count($products)/$perPage),
	    	'per_page' => $perPage,
	    	'brands' => $brands,
	    	'patterns' => $patterns,
	    	'brand_ids' => $request->query->get('brand_ids', []),
	    	'pattern_ids' => $request->query->get('pattern_ids', []),
	    ];
	}

	/**
	 * @Route("/record_users_log", name="base_record_users_log")
	 * @Template()
	 */
	public function recordUsersLogAction(Request $request)
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();

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

		if ($user->getIsPartner()) {
			return $this->redirect($this->generateUrl('wholesale_index'), 301);
		}
		
		return $this->redirect($this->generateUrl('order'), 301);
	}
}