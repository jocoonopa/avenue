<?php

namespace Woojin\GoodsBundle\Controller;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Entity\GoodsSource;
use Woojin\GoodsBundle\Entity\GoodsLevel;
use Woojin\GoodsBundle\Entity\GoodsMT;
use Woojin\GoodsBundle\Entity\Color;
use Woojin\GoodsBundle\Entity\Pattern;
use Woojin\GoodsBundle\Entity\GoodsStatus;
use Woojin\GoodsBundle\Entity\Img;
use Woojin\GoodsBundle\Entity\Desimg;
use Woojin\GoodsBundle\Entity\Description;
use Woojin\GoodsBundle\Entity\Brief;
use Woojin\GoodsBundle\Entity\Brand;
use Woojin\GoodsBundle\Entity\BrandType;
use Woojin\GoodsBundle\Entity\BrandSn;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Ope;
use Woojin\StoreBundle\Entity\Store;
use Woojin\StoreBundle\Entity\Activity;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\UserBundle\Entity\UsersHabit;
use Woojin\UserBundle\Entity\AvenueClue;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Woojin\Utility\Avenue\Avenue;

class GoodsController extends Controller
{
    const SN_RATE       = 100;
    const LV_DEFAULT    = 22;
    const BATCH_LIMIT   = 150;

    protected $oBrandSn;
    protected $oGoodsLevel;
    protected $oGoodsStatus;
    protected $oOrdersKind;
    protected $oOrdersStatus;
    protected $oPayType;
    protected $oUser;
    protected $em;

    /**
     * @Route("/", name="goods")
     * @Template("WoojinGoodsBundle:Goods:goods.html.twig")
     */
    public function indexAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        if ($user->getIsPartner()) {
            return $this->redirect($this->generateUrl('wholesale_index'), 301);
        }
        
        return array();
    }

    /**
     * @Route("/search_old", name="goods_search")
     * @Template("WoojinGoodsBundle:Goods:goods.search.html.twig")
     */
    public function goodsSearchAction(Request $request)
    {
        $oUser = $this->get('security.token_storage')->getToken()->getUser();

        $nUserId = $oUser->getId();

        $_token = $this->get('security.csrf.token_manager')->getToken('unknown');

        $sUsersHabitValue = '["1","2"]';

        $oUsersHabitName = 'goods_search';

        $rCondition = array('user' => $nUserId,'name' => $oUsersHabitName);
        $dc = $this->getDoctrine();
        $em = $dc->getManager();

        $oUsersHabit = $dc
                ->getRepository('WoojinUserBundle:UsersHabit')
                ->findOneBy($rCondition)
            ;

        if (!$oUsersHabit) {
            $oUsersHabit = new UsersHabit();
            $oUsersHabit
                ->setName($oUsersHabitName)
                ->setValue($sUsersHabitValue)
                ->setUser($oUser)
            ;

            $em->persist($oUsersHabit);
            $em->flush();
        }

        // 改成進入頁面透過ajax 請求( 當然, 有用到該選項才進行請求 )
        //$rBrand = $this->getDoctrine()->getRepository('WoojinGoodsBundle:Brand')->findAll();
        $rStore = $this->getDoctrine()->getRepository('WoojinStoreBundle:Store')->findAll();

        return array(
            'users_habit' => $oUsersHabit,
            'rStore' => $rStore,
            '_token' => $_token
        );
    }

    /**
     * @Route("/new_search", name="goods_new_search")
     * @Template("WoojinGoodsBundle:New:search.html.twig")
     */
    public function goodsNewSearchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        return array();
    }

    /**
     * @Route("/move/activity", name="goods_move_activity")
     * @Template("WoojinGoodsBundle:Goods/Move:activity.html.twig")
     */
    public function goodsMoveActivity()
    {
        return array();
    }

    /**
     * @Route("/search/ajax", name="goods_search_ajax", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:new.goods.search.ajax.html.twig")
     */
    public function goodsSearchAjaxAction(Request $request)
    {
        $userStore = $this->get('security.token_storage')->getToken()->getUser()->getStore();

        $em = $this->getDoctrine()->getManager();

        $stores = $this->getDoctrine()->getRepository('WoojinStoreBundle:Store')->findAll();
        $storeSns = array();
        foreach ($stores as $store) {
            $storeSns[] = $store->getSn();
        }

        $qb = $em->createQueryBuilder()
                ->select(array('gd', 'i'))
                ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
                ->leftJoin('gd.activity', 'ac')
                ->leftJoin('gd.img', 'i')
                ->leftJoin('gd.brand', 'b')
                ->leftJoin('gd.status', 'gs')
                ->leftJoin('gd.level', 'gl')
                ->leftJoin('gd.orders', 'od')
                ->leftJoin('od.opes', 'op')
                ->leftJoin('op.user', 'u')
                ->leftJoin('u.store','st')
                ->leftJoin('gd.custom', 'gcm')
                ->where('gd.id > 0')
            ;

        if ($request->request->count() == 1) {
            $qb->andWhere($qb->expr()->eq('gd.id', 0));
        }

        foreach ($request->request->all() as $key => $eachCon) {
            switch ($key) {
                case 'goods_passport_id':

                    $qb->andWhere($qb->expr()->in('gd.id', $eachCon));

                    break;

                case 'goods_search_store':
                    $rTempStoreSn = array();

                    foreach ($eachCon as $eachStoreId) {
                        array_push($rTempStoreSn, $storeSns[($eachStoreId > 7 ? $eachStoreId - 1: $eachStoreId) - 1]);
                    }

                    $qb->andWhere($qb->expr()->in('SUBSTRING(gd.sn, 1, 1)', $rTempStoreSn));

                    break;

                case 'goods_search_goods_name':
                    $dql = '';

                    foreach ($eachCon as $key_ => $cost_min) {
                        $rArr = $request->request->get($key);
                        $goods_name = $rArr[$key_];
                        $dql .= '(gd.name like \'%'.$goods_name.'%\') OR ';
                        $dql .= '(gd.org_sn = \''.$goods_name.'\') OR ';
                    }

                    $dql = substr($dql, 0, -3);

                    $qb->andWhere($dql);

                    break;

                case 'goods_search_goods_sn':

                    $qb->andWhere($qb->expr()->in('gd.sn', $eachCon));

                    break;

                case 'goods_search_cost_min':
                    $dql = '';

                    foreach ($eachCon as $key_ => $cost_min) {
                        $rArr = $request->request->get('goods_search_cost_max');
                        $cost_max = $rArr[$key_];
                        $dql .= '(gd.cost <='.$cost_max.' AND gd.cost >='.$cost_min.') OR ';
                    }

                    $dql = substr($dql, 0, -3);

                    $qb->andWhere($dql);

                    break;

                case 'goods_search_price_min':
                    $dql = '';

                    foreach ($eachCon as $key_ => $price_min) {
                        $rArr = $request->request->get('goods_search_price_max');
                        $price_max = $rArr[$key_];
                        $dql .= '(gd.price <='.$price_max.' AND gd.price >='.$price_min.') OR ';
                    }

                    $dql = substr($dql, 0, -3);

                    $qb->andWhere($dql);

                    break;

                case 'goods_search_create_time_start':
                    $dql = '';

                    foreach ($eachCon as $key_ => $time_start) {
                        $rArr = $request->request->get('goods_search_create_time_end');
                        $time_end = date($rArr[$key_]);
                        $time_end = date('Y-m-d', strtotime($time_end.' +1 day'));
                        $dql .= '(op.datetime <=\''.$time_end.'\' AND op.datetime >=\''.$time_start.'\') OR ';
                    }

                    $dql = substr($dql, 0, -3);

                    $qb->andWhere($dql);

                    break;

                case 'base_ajax_brand':

                    $qb->andWhere($qb->expr()->in('gd.brand', $eachCon));

                    break;

                case 'goods_search_has_picture':

                    if ($eachCon[0] == 1) {
                        $qb->andWhere($qb->expr()->isNull('gd.img'));
                    } else {
                        $qb->andWhere($qb->expr()->isNotNull('gd.img'));
                    }

                    break;

                case 'goods_search_has_memo':

                    if ($eachCon[0] == 1) {
                        $qb->andWhere($qb->expr()->neq('gd.memo', ''));
                    } else {
                        $qb->andWhere($qb->expr()->eq('gd.memo', ''));
                    }

                    break;

                case 'goods_search_goods_level':

                    $qb->andWhere($qb->expr()->in('gd.level', $eachCon));

                    break;

                case 'goods_search_goods_status':
                    if (in_array(Avenue::GS_BEHALF, $eachCon)) {
                        $qb->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->in('gd.status', $eachCon),
                                $qb->expr()->andX(
                                    $qb->expr()->eq('gd.status', Avenue::GS_SOLDOUT),
                                    $qb->expr()->eq('gd.isBehalf', true)
                                )
                            )
                        );
                    } else {
                        $qb->andWhere($qb->expr()->in('gd.status', $eachCon));
                    }

                    break;

                case 'nOrdersKindId':

                    $qb->andWhere($qb->expr()->in('od.kind', $eachCon));

                    break;

                case 'nOrdersStatusId':

                    $qb->andWhere($qb->expr()->in('od.status', $eachCon));

                    break;

                case 'nGoodsMTId':

                    $qb->andWhere($qb->expr()->in('gd.mt', $eachCon));

                    break;

                case 'nGoodsSourceId':

                    $qb->andWhere($qb->expr()->in('gd.source', $eachCon));

                    break;

                case 'nActivityId':

                    $qb->andWhere($qb->expr()->in('gd.activity', $eachCon) );

                    break;

                case 'color':
                    $qb->andWhere($qb->expr()->in('gd.color', $eachCon));
                    break;

                case 'pattern':
                    $qb->andWhere($qb->expr()->in('gd.pattern', $eachCon));
                    break;

                case 'model':
                    $qb->andWhere($qb->expr()->in('gd.model', $eachCon));
                    break;

                case 'colorSn':
                    $qb->andWhere($qb->expr()->in('gd.colorSn', $eachCon));
                    break;

                case 'customSn':
                    $qb->andWhere($qb->expr()->in('gd.customSn', $eachCon));
                    break;

                case 'brand':
                    $qb->andWhere($qb->expr()->in('gd.brand', $eachCon));
                    break;

                case 'isAllowWeb':
                    if ($eachCon == 1) {
                        $qb->andWhere($qb->expr()->eq('gd.isAllowWeb', true));
                    } else if ($eachCon == 0) {
                        $qb->andWhere($qb->expr()->eq('gd.isAllowWeb', false));
                    } else {

                    }

                    break;

                case 'isAllowAuction':
                    if ($eachCon == 1) {
                        $qb->andWhere($qb->expr()->eq('gd.isAllowAuction', true));
                    } else if ($eachCon == 0) {
                        $qb->andWhere($qb->expr()->eq('gd.isAllowAuction', false));
                    } else {

                    }

                    break;

                case 'phone':
                    $qb->andWhere($qb->expr()->in('gcm.mobil', $eachCon));

                    break;

                case 'yahooId':
                    if ($eachCon == 1) {
                        $qb->andWhere($qb->expr()->isNotNull('gd.yahooId'));
                    } else if ($eachCon == 0) {
                        $qb->andWhere($qb->expr()->isNull('gd.yahooId'));
                    }
                    break;

                case 'isAllowWholesale':
                    if ($eachCon == 1) {
                        $qb->andWhere($qb->expr()->eq('gd.isAllowWholesale', true));
                    } else if ($eachCon == 0) {
                        $qb->andWhere($qb->expr()->eq('gd.isAllowWholesale', false));
                    }
                    break;
                default:

                    break;
            }
        }

        $qb->groupBy('gd.id');

        // 死在這段吧....
        //$qbCount = $qb;
        //$nCount = count( $qbCount->getQuery()->getResult() );
        $orderCondition = ($request->request->get('orderCondition') == '') ?
            'gd.id': $request->request->get('orderCondition', 'gd.id');

        $orderSort = ($request->request->get('orderSort') == '') ?
            'DESC': $request->request->get('orderSort', 'DESC');

        $paginator = new Paginator($qb, $fetchJoinCollection = true);

        $c = count($paginator);

        $qb
            ->orderBy($orderCondition, $orderSort)
            ->setMaxResults(40)
            ->setFirstResult(($request->request->get('page', 1) - 1) * 40)
        ;

        $rGoodsPassport = $qb->getQuery()->getResult();

        return array(
            'GoodsPassports' => $rGoodsPassport,
            'rGoodsSnHistory' => $this->historyIterator($rGoodsPassport),
            'nCount' => $c,
            'nNowPage' => $request->request->get('page', 1),
            'bGoods' => $request->request->get('bGoods', 0)
        );
    }

    /**
     * 專門為了多筆銷貨做的查詢功能
     *
     * @Route("/multisale", name="goods_search_multisale", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.multisale.html.twig")
     */
    public function goodsSearchMultiSaleAction (Request $request)
    {
        $store = $this->get('security.token_storage')->getToken()->getUser()->getStore();

        $em = $this->getDoctrine()->getManager();

        $rGd = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->getMultile($request, $store);

        return array('rGd' => $rGd);
    }

    /**
     * 專門為了多筆銷貨做的產編查詢功能
     *
     * @Route("/multisale/goodsSn", name="goods_search_multisale_goodsSn", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.multisale.html.twig")
     */
    public function goodsSearchMultiSaleGoodsSnAction (Request $request)
    {
        $store = $this->get('security.token_storage')->getToken()->getUser()->getStore();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq(
                        'gd.sn', $qb->expr()->literal($request->request->get('sGoodsSn', ''))
                    ),
                    $qb->expr()->eq('SUBSTRING(gd.sn, 1, 1)', $qb->expr()->literal($store->getSn())),
                    $qb->expr()->eq('gd.status', Avenue::GS_ONSALE)
                )
            )
            ->groupBy('gd.id')
            ->orderBy('gd.sn')
        ;

        $rGd = $qb->getQuery()->getResult();

        return array(
            'rGd' => $rGd,
            'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findAll(),
            'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findAll(),
            'colors' => $em->getRepository('WoojinGoodsBundle:Color')->findAll()
        );
    }

    /**
     * @Route("/batch/ajax/res", name="goods_batch_ajax_res", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.search.ajax.html.twig")
     * @Method("POST")
     */
    public function goodsBatchResAjaxAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $time = $request->request->get('time');

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
            ->join('gd.brand', 'b')
            ->leftJoin('gd.img', 'i')
            ->join('gd.status', 'gs')
            ->join('gd.level', 'gl')
            ->join('gd.orders', 'od')
            ->join('od.opes', 'op')
            ->join('op.user', 'u')
            ->join('u.store','st')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->between('op.datetime', ':date_from', ':date_to'),
                    $qb->expr()->eq('st.id', $user->getStore()->getId())
                )
            )
            ->setParameter('date_from', new \DateTime($time))
            ->setParameter('date_to', new \DateTime($time))
        ;

        $nNowPage = $request->request->get('page', 1);
        $qbCount = $qb;
        $nCount = count($qbCount->getQuery()->getResult());

        $qb->setMaxResults(10)->setFirstResult(($nNowPage-1)*10);

        $products = $qb->getQuery()->getResult();

        return array(
            'GoodsPassports' => $products,
            'rGoodsSnHistory' => $this->historyIterator($products),
            'nCount' => $nCount,
            'nNowPage' => $nNowPage
        );
    }

    /**
     * @Route("/fix/res/show", name="goods_fix_res_show", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.search.ajax.html.twig")
     * @Method("POST")
     */
    public function goodsFixResShowAction (Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->find('WoojinGoodsBundle:GoodsPassport', $request->request->get('nGoodsPassportId'));

        $rGoodsPassport[0] = $product;

        return array(
            'GoodsPassports' => $rGoodsPassport,
            'rGoodsSnHistory' => $this->historyIterator($product),
            'bGoods' => $request->request->get('bGoods', 0)
        );
    }

    /**
     * @Route("/fix/res/show/json", name="goods_fix_res_show_json", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.search.ajax.html.twig")
     * @Method("POST")
     */
    public function goodsFixResShowJsonAction (Request $request)
    {
        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get( $key );
        }

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
            ->where(
                $qb->expr()->in('gd.id', (is_array($nGoodsPassportId)) ? $nGoodsPassportId : json_decode($nGoodsPassportId))
            )
        ;

        $rGoodsPassport = $qb->getQuery()->getResult();

        return array(
            'GoodsPassports' => $rGoodsPassport,
            'rGoodsSnHistory' => $this->historyIterator($rGoodsPassport),
            'bGoods' => $request->request->get('bGoods', 0)
        );
    }

    /**
     * @Route("/ajax/edit", name="goods_ajax_edit", options={"expose"=true})
     */
    public function goodsAjaxEditAction (Request $request)
    {
        if ( !$this->postCRSFCheck($request) ) {
            return new Response('error');
        }

        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        try {
            $oUser = $this->get('security.token_storage')->getToken()->getUser();

            $userStore = $oUser->getStore();

            $sStoreSn = $userStore->getSn();

            $nGoodsLevelId = $goods_search_goods_level[0];

            $oGoodsPassport = $em->find('WoojinGoodsBundle:GoodsPassport', $nGoodsPassportId);

            $sym = $this->get('base_method')->getSymbol();

            $theRate = ($sym == 'NT') ? self::SN_RATE : 10;

            $sGoodsCost = isset($sGoodsCost) ? $sGoodsCost : $oGoodsPassport->getCost();
            $sGoodsPrice = isset($sGoodsPrice) ? $sGoodsPrice : $oGoodsPassport->getPrice();
            $sGoodsSn = $oGoodsPassport->genSn($sStoreSn);

            $oGoodsLevel = $this
                ->getDoctrine()
                ->getRepository('WoojinGoodsBundle:GoodsLevel')
                ->find($nGoodsLevelId)
            ;

            $oGoodsMT = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsMT')->find($nGoodsMTId);
            $oGoodsSource = $this
                ->getDoctrine()
                ->getRepository('WoojinGoodsBundle:GoodsSource')
                ->find($nGoodsSourceId)
            ;

            $oImg = $oGoodsPassport->getImg();

            $sOldCreatedAt = is_null($oGoodsPassport->getCreatedAt())
                ? null
                : $oGoodsPassport->getCreatedAt()->format('Y-m-d');

            $sMsg = '修改商品資訊:['.$nGoodsPassportId.']';
            $sMsg.= '{產編:['.$oGoodsPassport->getSn().']['.$sGoodsSn.']}';
            $sMsg.= '{品名:['.$oGoodsPassport->getName().']['.$sGoodsName.']}';
            $sMsg.= '{序號:['.$oGoodsPassport->getOrgSn().']['.$sGoodsOrgSn.']}';
            $sMsg.= '{成本:['.$oGoodsPassport->getCost().']['.$sGoodsCost.']}';
            $sMsg.= '{訂價:['.$oGoodsPassport->getPrice().']['.$sGoodsPrice.']}';
            //$sMsg.= '{品牌:['.$oGoodsPassport->getBrandSn()->getBrandSnName().'][';
            $sMsg.= $oGoodsPassport->getModel().']}';
            $sMsg.= '{新舊:['.$oGoodsPassport->getLevel()->getName().']['.$oGoodsLevel->getName().']}';
            $sMsg.= '{進貨時間:['.$sOldCreatedAt.']['.$sCreatedAt.']}';

            if ($oGoodsPassport->getSource()) {
                $sMsg.= '{來源:['.$oGoodsPassport->getSource()->getName().']['.$oGoodsSource->getName().']}';
            }

            $sMsg.= '{備註:['.$oGoodsPassport->getMemo().']['.$sGoodsMemo.']}';

            $oMetaRecord = $this->get('my_meta_record_method');
            $oMetaRecord->recordMeta($sMsg);

            $color = $em->find('WoojinGoodsBundle:Color', $color);
            $pattern = $em->find('WoojinGoodsBundle:Pattern', $pattern);
            $brand = $em->find('WoojinGoodsBundle:Brand', $brand);

            $oGoodsPassport
                ->setColor($color)
                ->setPattern($pattern)
                ->setColorSn($colorSn)
                ->setModel($model)
                ->setBrand($brand)
                ->setLevel($oGoodsLevel)
                ->setSource($oGoodsSource)
                ->setName($sGoodsName)
                ->setSn($sGoodsSn)
                ->setOrgSn($sGoodsOrgSn)
                ->setCost($sGoodsCost)
                ->setPrice($sGoodsPrice)
                ->setMt($oGoodsMT)
                ->setCreatedAt(new \DateTime($sCreatedAt))
                ->setMemo($sGoodsMemo)
            ;

            $files = $request->files->get('img');

            if ($files) {
                $sMsg.= '{圖片修改}';

                $sRoot          = $request->server->get('DOCUMENT_ROOT');
                $sDirHead       = '/img/' . date('Y-m').'/';
                $sDirHeadRoot   = $sRoot . $sDirHead;
                $sImgName       = $nGoodsPassportId . $files->getClientOriginalName();
                $sWebSrc        = $sDirHead . $sImgName;

                if (!is_dir($sDirHeadRoot)) {
                    mkdir($sDirHeadRoot);
                }

                $oImg = $oGoodsPassport->getImg();

                if ($oImg) {
                    $sFilePath = $sRoot . $oImg->getPath();

                    if (file_exists($sFilePath)) {
                        unlink($sFilePath);
                    }
                } else {
                    $oImg = new Img();
                }

                if ($files->move($sDirHeadRoot, $sImgName)) {
                    $oImg->setPath($sWebSrc);
                    $em->persist($oImg);
                    $em->flush();
                    $nImgId = $oImg->getImgId();
                }

                $oGoodsPassport->setImg($oImg);

                $sImgPath = $oGoodsPassport->getImg()->getPath();

            } else if (isset($oImg)) {
                $nImgId     = $oGoodsPassport->getImg()->getId();
                $sImgPath   = $oGoodsPassport->getImg()->getPath();
            }

            $em->persist($oGoodsPassport);
            $em->flush();
            $em->getConnection()->commit();

        } catch (Exception $e) {
            $em->getConnection()->rollback();

            throw $e;
        }

        $h['sGoodsSn'] = $sGoodsSn;

        if (!isset($nImgId)) {
            $nImgId = '';
        }

        $h['nImgId'] = $nImgId;

        if (!isset($sImgPath)) {
            $sImgPath = '/img/nothing.png';
        }

        $h['sImgPath'] = $sImgPath;

        return new Response(json_encode($h));
    }

    /**
     * @Route("/ajax/refresh/goods_info", name="goods_ajax_refresh_goods_info", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.ajax.refresh.goodsInfo.html.twig")
     * @Method("POST")
     */
    public function goodsAjaxRefreshGoodsInfoAction(Request $request)
    {
        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }

        $product = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsPassport')->find($nGoodsPassportId);

        return array(
            'oGoodsPassport' => $product,
            'rGoodsSnHistory' => $this->historyIterator($product)
        );
    }

    /**
     * @Route("/search/ajax_img", name="goods_search_ajax_img", options={"expose"=true})
     * @Method("POST")
     */
    public function goodsSearchAjaxImgAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $img = $em->find('WoojinGoodsBundle:Img', $request->request->get('img_id'));

        return new Response($img->getPath());
    }

    /**
     * @Route("/search/ajax_name", name="goods_search_ajax_name", options={"expose"=true})
     */
    public function goodsSearchAjaxNameAction(Request $request)
    {
        if ($request->getMethod() != 'GET')
            return false;

        $goods_name = $request->query->get('term');
        $dql = 'SELECT g FROM WoojinGoodsBundle:GoodsPassport g WHERE g.name like :goods_name ORDER BY g.id DESC';
        $em = $this->getDoctrine()->getManager();
        $goods = $em->createQuery($dql)
            ->setParameter('goods_name', '%' . $goods_name . '%')
            ->setMaxResults(12)
            ->setFirstResult(0)
            ->getResult();

        if (is_array($goods)) {
            $name_container = array();
            foreach ($goods as $good)
                $name_container[] = $good->getName();

            $name_json_str = json_encode($name_container);

            return new Response($name_json_str);
        }

        return new Response('');
    }

    /**
     * @Route("/source", name="goods_source")
     * @Template("WoojinGoodsBundle:Goods:goods.source.html.twig")
     */
    public function goodsSourceAction()
    {
        $sources = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsSource')->findAll();

        return array('rGoodsSource' => $sources);
    }

    /**
     * @Route("/level", name="goods_level")
     * @Template("WoojinGoodsBundle:Goods:goods.level.html.twig")
     */
    public function goodsLevelAction ()
    {
        $levels = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsLevel')->findAll();

        return array('rGoodsLevel' => $levels);
    }

    /**
     * @Route("/move/assignday/list", name="goods_assigndaylist")
     * @Template("WoojinGoodsBundle:Goods:goods.assigndaylist.html.twig")
     */
    public function goodsAssignTodayList ()
    {
        return array();
    }

    /**
     * @Route("/search/assignday/movelist/ajax", name="goods_ajax_assignday_list", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.ajax.assignday.list.html.twig")
     */
    public function ajaxGetAssignDayMoveOut (Request $request)
    {
        $store = $this->get('security.token_storage')->getToken()->getUser()->getStore();
        $sStartTime = $request->request->get('sStartTime') . ' 00:00:00';
        $sEndTime = $request->request->get('sEndTime') . ' 23:59:59';
        $oEnd = new \DateTime($sEndTime);

        $em = $this->getDoctrine()->getManager();
        $destination = $em->find('WoojinStoreBundle:Store', $request->request->get('store_id'));

        $qb = $em->createQueryBuilder();
        $moves = $qb
            ->select('m')
            ->from('WoojinGoodsBundle:Move' , 'm')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->in('m.status', array(1,2)),
                    $qb->expr()->eq('m.from', $store->getId()),
                    $qb->expr()->eq('m.destination', $destination->getId()),
                    $qb->expr()->between('m.createAt', ':sStartTime', ':sEndTime')
                )
            )
            ->setParameter('sStartTime', new \DateTime($sStartTime) )
            ->setParameter('sEndTime', $oEnd->add(new \DateInterval("P1D")))
            ->getQuery()
            ->getResult()
        ;

        return array(
            'moves' => $moves,
            'store' => $destination,
            'queryDate' => '[' . $sStartTime . '~' . substr($sEndTime, 0, 10) . ' 23:59:59' . ']'
        );
    }

    /**
     * @Route("/material", name="goods_material")
     * @Template("WoojinGoodsBundle:Goods:goods.material.html.twig")
     */
    public function goodsMaterialAction ()
    {
        $rGoodsMT = $this->getDoctrine()
            ->getRepository('WoojinGoodsBundle:GoodsMT')
            ->findAll();

        return array('rGoodsMT' => $rGoodsMT);
    }

    /**
    * @Route("/crud/mt", name="crud_mt", options={"expose"=true})
    * @Method("POST")
    */
    public function goodsMTCUDAction (Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        switch ($request->request->get('flow')) {
            case 'add':
                $oMT = new GoodsMT();
                $oMT->setName($request->request->get('name'));
                $em->persist($oMT);
                $em->flush();
                $id = $oMT->getId();

                break;

            case 'update':
                $oMT = $em->getRepository('WoojinGoodsBundle:GoodsMT')->find($request->request->get('id'));
                $oMT->setName($request->request->get('name'));
                $em->persist($oMT);
                $em->flush();
                $id = $oMT->getId();

                break;

            case 'delete':
                $oMT = $em->getRepository('WoojinGoodsBundle:GoodsMT')->find($request->request->get('id'));
                $em->remove($oMT);
                $em->flush();
                $id = 0;

                break;
        }

        return new Response($id);
    }

    /**
    * @Route("/ajax/add/GoodsSource", name="goods_ajax_add_goodsSource", options={"expose"=true})
    * @Method("POST")
    */
    public function goodsAjaxAddGoodsSourceAction (Request $request)
    {
        $source = new GoodsSource();
        $source->setName($request->request->get('sGoodsSourceName'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($source);
        $em->flush();

        $sMsg = '新增來源:['.$source->getId().']';
        $sMsg.= '{名稱:['.$source->getName().']}';

        $oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta($sMsg);

        return new Response($source->getId());
    }

    /**
    * @Route("/ajax/update/GoodsSource", name="goods_ajax_update_goodsSource", options={"expose"=true})
    * @Method("POST")
    */
    public function goodsAjaxUpdateGoodsSourceAction (Request $request)
    {
        if (is_null($sGoodsSourceName = $request->request->get('sGoodsSourceName', null))) {
            return new Response ('Bad Request!');
        }

        $em = $this->getDoctrine()->getManager();

        $oGoodsSource = $em->getRepository('WoojinGoodsBundle:GoodsSource')->find($request->request->get('nGoodsSourceId'));

        $oGoodsSource->setName($request->request->get('sGoodsSourceName'));

        $sMsg = '';
        $sMsg .= '更改來源:['.$oGoodsSource->getId().']';
        $sMsg .= '{名稱:['.$oGoodsSource->getName().']['.$request->request->get('sGoodsSourceName').']}';

        $em->persist($oGoodsSource);
        $em->flush();

        $oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta($sMsg);

        return new Response($sMsg);
    }

    /**
     * @Route("/ajax/delete/GoodsSource", name="goods_ajax_delete_goodsSource", options={"expose"=true})
     * @Method("POST")
     */
    public function goodsAjaxDeleteGoodsSourceAction (Request $request)
    {
        $sMsg = '';
        $dc = $this->getDoctrine();
        $oGoodsSource = $dc->getRepository('WoojinGoodsBundle:GoodsSource')->find($request->request->get('nGoodsSourceId'));

        $em = $dc->getManager();
        $em->remove($oGoodsSource);
        $em->flush();

        $sMsg = '移除來源:['.$oGoodsSource->getId().']';
        $sMsg.= '{產地:['.$oGoodsSource->getName().']}';

        $oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta($sMsg);

        return new Response('ok');
    }

    /**
     * @Route("/ajax/add/GoodsLevel", name="goods_ajax_add_GoodsLevel", options={"expose"=true})
     * @Method("POST")
     */
    public function goodsAjaxAddGoodsLevelAction ( Request $request )
    {
        $oGoodsLevel = new GoodsLevel;

        $oGoodsLevel->setName($request->request->get('sGoodsLevelName'));

        $em = $this->getDoctrine()->getManager();
        $em->persist( $oGoodsLevel );
        $em->flush();

        $sMsg = '新增新舊程度:['.$oGoodsLevel->getId().']';
        $sMsg.= '{名稱:['.$oGoodsLevel->getName().']}';
        $oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta($sMsg);

        return new Response($oGoodsLevel->getId());
    }

    /**
     * @Route("/ajax/update/GoodsLevel", name="goods_ajax_update_GoodsLevel", options={"expose"=true})
     * @Method("POST")
     */
    public function goodsAjaxUpdateGoodsLevelAction (Request $request)
    {
        $sMsg = '';
        $dc = $this->getDoctrine();

        $oGoodsLevel = $dc->getRepository('WoojinGoodsBundle:GoodsLevel')->find($request->request->get('nGoodsLevelId'));

        if ($request->request->get('sGoodsLevelName')) {
          $oGoodsLevel->setName($request->request->get('sGoodsLevelName'));

          $sMsg .= '更改新舊程度:['.$oGoodsLevel->getId().']';
          $sMsg .= '{名稱:['.$oGoodsLevel->getName().']['.$request->request->get('sGoodsLevelName').']}';
        }

        $em = $dc->getManager();
        $em->persist($oGoodsLevel);
        $em->flush();

        $oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta($sMsg);

        return new Response($sMsg);
    }

    /**
     * @Route("/ajax/delete/GoodsLevel", name="goods_ajax_delete_GoodsLevel", options={"expose"=true})
     * @Method("POST")
     */
    public function goodsAjaxDeleteGoodsLevelAction (Request $request)
    {
        $sMsg = '';

        $dc = $this->getDoctrine();

        $oGoodsLevel = $dc->getRepository('WoojinGoodsBundle:GoodsLevel')->find($request->request->get('nGoodsLevelId'))
            ;

        $em = $dc->getManager();
        $em->remove($oGoodsLevel);
        $em->flush();

        $sMsg = '移除新舊程度:['.$oGoodsLevel->getId().']';
        $sMsg.= '{名稱:['.$oGoodsLevel->getName().']}';

        $oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta($sMsg);

        return new Response('ok');
    }

    /**
     * @Route("/edit/form/loaded", name="goods_edit_form_loaded", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.edit.form.html.twig")
     * @Method("POST")
     */
    public function goodsEditFormLoadedAction (Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }

        $_token = $this->get('security.csrf.token_manager')->getToken('unknown');

        $oGoodsPassport = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsPassport')->find($nGoodsPassportId);

        return array(
            '_token' => $_token,
            'oGoodsPassport' => $oGoodsPassport,
            'bGoods' => $request->request->get('bGoods', 0),
            'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findAll(),
            'colors' => $em->getRepository('WoojinGoodsBundle:Color')->findAll(),
            'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findAll()
        );
    }

    /**
     * @Route("/one/detail/info", name="goods_one_detail_info", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.one.detail.info.html.twig")
     * @Method("POST")
     */
    public function goodsOneDetailInfoAction (Request $request)
    {
        foreach($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }

        $oGoodsPassport = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsPassport')->find($nGoodsPassportId);

        return array(
            'oGoodsPassport' => $oGoodsPassport,
            'rGoodsSnHistory' => $this->historyIterator( $oGoodsPassport )
        );
    }

    /**
     * @Route("/check/onsale", name="goods_checkonsale")
     * @Template("WoojinGoodsBundle:Goods:goods.checkonsale.html.twig")
     */
    public function goodsCheckonsaleAction ()
    {
        $sStoreSn = $this->get('security.token_storage')->getToken()->getUser()->getStore()->getSn();

        $em = $this->getDoctrine()->getManager();

        $rGoods = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findStoreOnSale($sStoreSn);

        return array('rGoods' => $rGoods);
    }

    /**
     * @Route("/batch", name="goods_batch")
     * @Template("WoojinGoodsBundle:Goods:goods.batch.html.twig")
     */
    public function goodsBatchAction ()
    {
        $_token = $this->get('security.csrf.token_manager')->getToken('unknown');

        return array('_token' => $_token);
    }

    /**
     * @Route("/batch/process", name="goods_batch_process", options={"expose"=true})
     */
    public function goodsBatchProcessAction(Request $request)
    {
        // CRSF攻擊檢查
        if ($this->postCRSFCheck($request) !== true) {
            return new Response('error');
        }

        // 取得上傳檔案以及檢驗是否合法
        $files = $request->files->get('fBatchUploadGoods');

        if (!$files->isValid()) {
            return new Response('無上傳檔案');
        }

        // 取得目前使用者實體
        $this->oUser = $this->get('security.token_storage')->getToken()->getUser();

        // 目前使用者所屬店家
        $sStoreSn = $this->oUser->getStore()->getSn();

        // 欄位對應判斷陣列
        $rFlag = array();

        // 資料夾前段字串
        $sDirHead = '/csv/' . $sStoreSn . '/';

        // 根目錄和資料夾前段字串組合 = 完整存放路徑
        $sDirHeadRoot = $request->server->get('DOCUMENT_ROOT') . $sDirHead;

        // 亂數形成檔案名稱
        $sFileName = rand(1000000, 9999999) . '_goods_batch.xls';

        // 移動檔案到完整存放路徑
        $files->move($sDirHeadRoot, $sFileName);

        // 將實體管理員存放至屬性方便其他方法直接調用 ( 其實好像不是好作法 )
        $this->em = $em = $this->getDoctrine()->getManager();

        // 取得PHPExcel物件
        $excelService = $this->get('xls.load_xls5');

        // 取得excel資料並且轉換成陣列 ,
        // 以此陣列最迭代進行上傳程序
        $objPHPExcel = $excelService->load($sDirHeadRoot . $sFileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();

        foreach ($objWorksheet->getRowIterator() as $nRows => $row) {
            $i = 0;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                // 如果是第一次 , 則行程表單頭
                if ($nRows == 1) {
                    $sBrandName = false;
                    $rData = array();
                    $rData['time'] = date('Y-m-d H:i:s');
                    $rData['sStoreSn'] = $sStoreSn;
                    $rData['nRate'] = self::SN_RATE;
                    $rData['sGoodsName'] = true;
                    $rData['sGoodsOrgSn'] = true;
                    $rData['nGoodsPrice'] = true;
                    $rData['nGoodsCost'] = true;
                    $rData['sGoodsMemo'] = true;
                    $rData['pattern'] = true;
                    $rFlag[$i] = strval($cell);

                    $i ++;
                } else {
                    $colorName = '';

                    // Data size limit
                    if (($nRows > self::BATCH_LIMIT)) {
                        break;
                    }

                    switch ($rFlag[$i]) {
                        case '包名':
                            $rData['sGoodsName'] = strval($cell);
                            break;

                        case '品牌':
                            $sBrandName = strval($cell);
                            break;

                        case '包型':
                            $sBrandTypeName = strval($cell);
                            break;

                        case '序號':
                            $rData['sGoodsOrgSn'] = strval($cell);
                            break;

                        case '訂價':
                            $rData['nGoodsPrice'] = intval(strval($cell));
                            break;

                        case '成本':
                            $rData['nGoodsCost'] = intval(strval($cell));
                            break;

                        case '備註':
                            $rData['sGoodsMemo'] = strval($cell);
                            break;

                        case '色號':
                            $rData['colorSn'] = strval($cell);
                            break;

                        case '型號':
                            $rData['model'] = strval($cell);
                            break;

                        case '狀態':
                            $sGoodsLevelName = strval($cell);
                            break;

                        case '顏色':
                            $colorName = strval($cell);
                            break;

                        default:
                            break;
                    }

                    $i++;

                    if (($i < count($rFlag))) {
                        continue;
                    }

                    if (
                        $rData['sGoodsName']== '' ||
                        $sBrandName == '' ||
                        $rData['model'] == ''
                    ) {
                        continue;
                    }

                    $this->oGoodsStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE);
                    $this->oOrdersKind = $em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_IN);
                    $this->oOrdersStatus = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE);
                    $this->oPayType = $em->find('WoojinOrderBundle:PayType', Avenue::PT_CASH);

                    $rGoodsLevel = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsLevel')->findBy(array('name' => $sGoodsLevelName));

                    $this->oGoodsLevel = isset($rGoodsLevel[0])
                        ? $rGoodsLevel[0]
                        : $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsLevel')->find(self::LV_DEFAULT);

                    $this
                        ->brandAutoProcess($sBrandName, $sBrandTypeName, (array_key_exists('model', $rData)) ? $rData['model'] : null, (array_key_exists('colorSn', $rData)) ? $rData['colorSn'] : null, $colorName)
                        ->batchAddEachProcess($rData)
                    ;
                }
            }
        }

        $oFilesManager = $this->get('files_manager');
        $oFilesManager->deleteDirFiles($sDirHeadRoot . '*');

        return new Response($rData['time']);
    }

    /**
     * 取消上傳動作, 套用orphanRemove 以及佔位子用法
     * 按照傳入的訂單時間, 種類, 操作者為條件進行還原
     *
     * @Route("/batch/rolback", name="goods_batch_rollback", options={"expose"=true})
     * @Method("POST")
     */
    public function goodsBatchRollbackAction (Request $request)
    {
        if (is_null($sTime = $request->request->get('time', null))) {
            return new Response('error!');
        }

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $rGoods = $qb
                ->select('g')
                ->from('WoojinGoodsBundle:GoodsPassport', 'g')
                ->join('g.orders', 'od')
                ->join('od.opes', 'op')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->eq('op.datetime', ':sTime'),
                        $qb->expr()->eq('op.act', ':sOpeAct'),
                        $qb->expr()->eq('op.user', ':nId'),
                        $qb->expr()->eq('od.kind', ':nOrdersKindId')
                    )
                )
                ->setParameter('nId', $this->get('security.token_storage')->getToken()->getUser()->getId())
                ->setParameter('sOpeAct', '批次成立進貨訂單')
                ->setParameter('sTime', new \DateTime($sTime))
                ->setParameter('nOrdersKindId', Avenue::OK_IN)
                ->getQuery()
                ->getResult()
            ;

        foreach ($rGoods as $oGoods) {
            $em->remove($oGoods);
        }

        $em->flush();

        return new Response('ok');
    }

    /**
    * Get the specify goods detail information, 這邊其實可以用 JMS  serialize 直接序列物件
    *
    * @Route("/api/{id}", requirements={"id" = "\d+"}, name="api_get_goods_detail", options={"expose"=true})
    * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
    * @Method("GET")
    */
    public function apiShowActivityGoodsDetailAction(Request $request, GoodsPassport $goods)
    {
        $rData = array();

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $role = $user->getRole();

        $oMt = $goods->getMt();
        $oImg = $goods->getImg();
        $oGoodsLevel = $goods->getLevel();
        $oGoodsSource = $goods->getSource();
        $oGoodsStatus = $goods->getStatus();

        $rData['id'] = $goods->getId();
        $rData['sn'] = $goods->getSn();
        $rData['name'] = $goods->getName();
        $rData['price'] = $goods->getPrice();
        $rData['cost'] = (
            ($role->hasAuth('READ_COST_OWN') && $goods->isOwnProduct($user))
            || $role->hasAuth('READ_COST_ALL'))
            ? $goods->getCost() : null;

        $rData['memo'] = $goods->getMemo();
        $rData['level'] = (is_object($oGoodsLevel)) ? $oGoodsLevel->getName() : '未設定';
        $rData['material'] = (is_object($oMt)) ? $oMt->getName() : '未設定';
        $rData['source'] = (is_object($oGoodsSource)) ? $oGoodsSource->getName() : '未設定';
        $rData['imgURL'] = (is_object($oImg)) ? $oImg->getPath() : '';
        $rData['status'] = (is_object($oGoodsStatus)) ? $oGoodsStatus->getName() : '未知';
        $rData['model'] = $goods->getModel();
        $rData['pattern'] = $goods->getPattern()->getName();
        $rData['brand'] = $goods->getBrand()->getName();

        $response = new Response(json_encode($rData));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * 查貨api, 回傳商品的json字串
     *
     * @Route("api/query/{jCondition}", name="goods_api_query", options={"expose"=true})
     * @Method("GET")
     */
    public function apiSearchConditionAction ($jCondition)
    {
        $rCondition = json_decode($jCondition, true);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->join('g.brand', 'b')
            ->join('p.pattern', 'p')
            ->where( $qb->expr()->neq('g.id', 0) )
        ;

        if (!empty($rCondition['goodsName'])) {
            $qb->andWhere(
                $qb->expr()->like(
                    'g.name',
                    $qb->expr()->literal('%' . $rCondition['goodsName'] . '%')
                )
            );
        }

        if (!empty( $rCondition['goodsSn'] )) {
            $qb->andWhere(
                $qb->expr()->like(
                    'g.sn',
                    $qb->expr()->literal('%' . $rCondition['goodsSn'] . '%')
                )
            );
        }

        if (!empty( $rCondition['brandSn'] )) {
            $qb->andWhere(
                $qb->expr()->like(
                    'g.model',
                    $qb->expr()->literal('%' . $rCondition['brandSn'] . '%')
                )
            );
        }

        if (!empty( $rCondition['storeSn'] )) {
            $qb->andWhere(
                $qb->expr()->in(
                    $qb->expr()->substring('g.sn', 1, 1),
                    $rCondition['storeSn']
                )
            );
        }

        if (!empty( $rCondition['goodsStatus'] )) {
            $qb->andWhere($qb->expr()->in('g.status', $rCondition['goodsStatus'] ));
        }

        if (!empty( $rCondition['goodsLevel'] )) {
            $qb->andWhere($qb->expr()->in('g.level', $rCondition['goodsLevel']));
        }

        if (!empty( $rCondition['brand'] )) {
            $qb->andWhere($qb->expr()->in('bt.brand', $rCondition['brand']));
        }

        if (!empty( $rCondition['activity'] )) {
            $qb->andWhere($qb->expr()->in('g.activity', $rCondition['activity']));
        }

        $qb->groupBy('g.id');

        $rGoods = $qb->getQuery()->getArrayResult();

        $rTmp = array();

        if (count($rGoods) < 500 && count($rGoods) > 0) {
            foreach ($rGoods as $key => $eachGoods ) {
                $rTmp[$key]['id'] = $eachGoods['id'];
                $rTmp[$key]['goods_name'] = $eachGoods['goods_name'];
                $rTmp[$key]['goods_sn'] = $eachGoods['goods_sn'];
            }
        } else if (count($rGoods) > 500) {
            $rTmp[0]['goods_name'] = '查詢資料過多請使用更精準的條件重新查詢';
        } else {
            $rTmp[0]['goods_name'] = '沒有符合條件的商品';
        }

        return new Response(urldecode(json_encode($rTmp)));
    }

    /**
     * 品牌批次上傳自動添加程序
     *
     * @param  [string] $sBrandName     [品牌名稱]
     * @param  [string] $sBrandTypeName [款式名稱]
     * @param  [string] $sBrandSnName   [型號名稱]
     * @param  [string] $sBrandSnColor  [型號顏色]
     * @return [object]                 [this]
     */
    protected function brandAutoProcess($sBrandName, $sBrandTypeName, $sBrandSnName, $sBrandSnColor, $colorName)
    {
        // 取得實體管理員
        $em = $this->em;

        // 判斷品牌是否存在
        // 若存在則繼續檢查款式是否存在
        // 若不存在則新增傳入的品牌, 款式, 型號
        // $this->oBrandSn 這行是用來讓商品綁上此型號物件用 ,
        // 透過屬性比較快, 直接回傳也是可以可是失去鏈結性有點不喜歡
        $rBrand = $this
            ->getDoctrine()
            ->getRepository('WoojinGoodsBundle:Brand')
            ->findBy(array('name' => $sBrandName))
          ;

        if ($rBrand) {
            $oBrand = $rBrand[0];
        } else {
            $oBrand = new Brand();
            $oBrand->setName($sBrandName);

            $em->persist($oBrand);
        }

        if ($colorName != '') {
            $colors = $this->getDoctrine()->getRepository('WoojinGoodsBundle:Color')->findBy(array('name' => $colorName));

            if ($colors) {
                $color = $colors[0];
            } else {
                $color = new Color;
                $color
                    ->setName($colorName)
                    ->setCode('#FFFFFF')
                ;

                $em->persist($color);
            }
        } else {
            $color = null;
        }

        if ($sBrandTypeName != '') {
            // 判斷款式是否存在
            // 若存在則繼續檢查型號是否存在
            // 若不存在則新增傳入的 款式, 型號
            $patterns = $this
                ->getDoctrine()
                ->getRepository('WoojinGoodsBundle:Pattern')
                ->findBy(array('name' => $sBrandTypeName))
            ;

            if ($patterns) {
                $pattern = $patterns[0];
            } else {
                $pattern = new Pattern();
                $pattern->setName($sBrandTypeName);

                $em->persist($pattern);
            }
        }

        $em->flush();

        $this->brand = $oBrand;
        $this->color = $color;
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * 取得商品轉店歷程記錄, 共有兩層迭代
     * 先是商品資訊的迭代->各個商品
     * 各個商品的入境記錄陣列迭代->各個身分
     *
     * 1.商品結果陣列迭代
     * 2.每個商品分別根據指紋( goods_index_id )找出自己的所有入境記錄( 進貨類型定單)
     * 3.根據入境記錄陣列迭代, 取出產編部分組成陣列
     * 4.組成的陣列放入 和商品資料同層級的歷史紀錄陣列內, 輸出到模版
     *
     * @param  [array] $rGoodsPassport [商品資料物件陣列]
     * @return [array]                 [歷史紀錄二維陣列]
     */
    protected function historyIterator($rGoodsPassport)
    {
        // 記錄轉店歷程的陣列
        $rGoodsSnHistory = array();

        // 如果傳入為單一物件, 將其轉化為陣列
        if ( !is_array( $rGoodsPassport ) ) {
            $rTmp[] = $rGoodsPassport;
            $rGoodsPassport = $rTmp;
        }

        foreach ($rGoodsPassport as $key => $oGoodsPassport) {
            // 若非物件則跳過
            if (!is_object($oGoodsPassport)) {
                continue;
            }

            // 存放該商品的所有身分
            $rGoodsPassportHistory = array();

            // 產編字串陣列
            $rGoodsSn = array();

            // 依照商品索引找出其所有身分
            // 迭代組成goods_passport_id的陣列,
            // 按照入境記錄排序
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb
                ->select('od')
                ->from('WoojinOrderBundle:Orders', 'od')
                ->join('od.goods_passport', 'g')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->in('od.kind', array(
                                Avenue::OK_MOVE_IN,
                                Avenue::OK_IN,
                                Avenue::OK_CONSIGN_IN,
                                Avenue::OK_EXCHANGE_IN
                            )
                        ),
                        $qb->expr()->neq('od.status', Avenue::OS_CANCEL),
                        $qb->expr()->eq('g.parent', $oGoodsPassport->getParent()->getId())
                    )
                )
                ->orderBy('od.id', 'ASC')
            ;

            $rRes = $qb->getQuery()->getResult();

            // 依照訂單取得商品護照放入歷史紀錄陣列
            foreach ($rRes as $oRes) {
                array_push($rGoodsPassportHistory, $oRes->getGoodsPassport());
            }

            // 所有產品護照迭代
            foreach ($rGoodsPassportHistory as $key_ => $oGoodsPassportHistory) {
                if (!is_object($oGoodsPassportHistory)) {
                    break;
                }
                $rGoodsSn[] = $oGoodsPassportHistory->getSn();

                // 如果迭代進行到最後一個身分時,
                // 對其產編字首做分析,
                // 得到該店店名,並接在產編後面一同塞入陣列
                // @ 2013-12-31 客戶的要求
                if ($key_ == (count( $rGoodsPassportHistory ) - 1)) {

                    // 取得產編字首
                    $cStoreSn = substr( $oGoodsPassportHistory->getSn(), 0, 1);

                    // 根據產編字首取得商店實體
                    $userStore = $this
                            ->getDoctrine()
                            ->getRepository('WoojinStoreBundle:Store')
                            ->findOneBy(array('sn' => $cStoreSn))
                        ;

                    // 連同產編一起置入陣列
                    $rGoodsSn[(count($rGoodsSn) - 1)] .= '(所在店:' .  $userStore->getName() . ')';
                }
            }

            // 商品身分陣列存入該商品的歷史紀錄陣列
            $rGoodsSnHistory[$key] = $rGoodsSn;
        }

        return (is_array($rGoodsSnHistory)) ? $rGoodsSnHistory : array();
    }

    protected function batchAddEachProcess($rData)
    {
        $em = $this->em;

        $oOrders = new Orders();

        $oOpe = new Ope();

        $oGoodsPassport = new GoodsPassport();

        $oGoodsPassport
            ->setBrand($this->brand)
            ->setModel($rData['model'])
            ->setColor($this->color)
            ->setPattern($this->pattern)
            ->setColorSn((array_key_exists('colorSn', $rData)) ? $rData['colorSn'] : '')
            ->setLevel($this->oGoodsLevel)
            ->setGoodsStatus($this->oGoodsStatus)
            ->setName($rData['sGoodsName'])
            ->setCost($rData['nGoodsCost'])
            ->setPrice($rData['nGoodsPrice'])
            ->setOrgSn((array_key_exists('sGoodsOrgSn', $rData)) ? $rData['sGoodsOrgSn'] : '')
            ->setMemo((array_key_exists('sGoodsMemo', $rData)) ? $rData['sGoodsMemo'] : '')
        ;


        $em->persist($oGoodsPassport);
        $em->flush();

        $sym = $this->get('base_method')->getSymbol();

        $theRate = ($sym == 'NT') ? self::SN_RATE : 10;

        $nGoodsId = $oGoodsPassport->getId();
        $sGoodsSn = $oGoodsPassport->genSn($rData['sStoreSn']);

        $oGoodsPassport
            ->setSn($sGoodsSn)
            ->setParent($em->find('WoojinGoodsBundle:GoodsPassport', $nGoodsId))
        ;

        $em->persist($oGoodsPassport);
        $em->flush();
        $oOrders
            ->setGoodsPassport($oGoodsPassport)
            ->setPayType($this->oPayType)
            ->setOrdersKind($this->oOrdersKind)
            ->setOrdersStatus($this->oOrdersStatus)
            ->setRequired($rData['nGoodsCost'])
            ->setPaid($rData['nGoodsCost'])
        ;

        $em->persist($oOrders);
        $em->flush();
        $nOrdersId = $oOrders->getId();

        //Ope實體新增
        $oOpe
            ->setOrders($oOrders)
            ->setUser($this->oUser )
            ->setAct('批次成立進貨訂單')
            ->setDatetime(new \DateTime($rData['time']))
        ;

        $em->persist($oOpe);
        $em->flush();
    }

    protected function postCRSFCheck($req)
    {
        if ($req->getMethod() != 'POST') {
            return 'Not Post!';
        }

        $form = $this->get('form.factory')->createBuilder('form')->getForm();

        $form->bind($req);

        return !$form->isValid();
    }

    /**
     * @Route("/search/v2", name="goods_search_v2")
     * @Template()
     */
    public function newSearchAction()
    {
        return array();
    }

    /**
     * @Route("/edit/{id}/v2", requirements={"id" = "\d+"}, name="goods_edit_v2", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
     * @Template()
     * @Method("GET")
     */
    public function editV2Action(GoodsPassport $goods)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        /**
         * 該商品的所有身分
         *
         * @var array
         */
        $goodsFamilys = $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->eq('g.parent', $goods->getParent()->getId())
            )
            ->getQuery()->getResult();

        /**
         * Yahoo api 的一個 facade
         *
         * @var \Woojin\Utility\YahooApi\Client
         */
        $apiClient = $this->get('yahoo.api.client');

        /**
         * 店內分類
         *
         * @var array(\StdClass)
         */
        $sc = $apiClient->storeCategoryGet();

        $storeCategorys = NULL === $sc || 'fail' === $sc->Response->Status ? $sc : $sc->Response->StoreCategoryList->StoreCategory;

        /**
         * 店內允許付費方式
         *
         * @var array(\StdClass)
         */
        $sp = $apiClient->storePaymentGet();
        $storePayments = NULL === $sp || 'fail' === $sp->Response->Status ? $sp : $sp->Response->PayTypeList->PayType;//$apiClient->storePaymentGet()->Response->PayTypeList->PayType;

        /**
         * 店內允許物流方式
         *
         * @var array(\StdClass)
         */
        $ss = $apiClient->storeShippingGet();
        $storeShippings = NULL === $ss || 'fail' === $ss->Response->Status ? $ss : $ss->Response->ShippingTypeList->ShippingType;//$apiClient->storeShippingGet()->Response->ShippingTypeList->ShippingType;

        return array(
            'goods' => $goods,
            'goodsFamilys' => $goodsFamilys,
            'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findBy(array(), array('name' => 'ASC')),
            'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findBy(array(), array('name' => 'ASC')),
            'sources' => $em->getRepository('WoojinGoodsBundle:GoodsSource')->findBy(array(), array('name' => 'ASC')),
            'colors' => $em->getRepository('WoojinGoodsBundle:Color')->findBy(array(), array('name' => 'ASC')),
            'sizes' => $em->getRepository('WoojinGoodsBundle:GoodsSize')->findBy(array(), array('name' => 'ASC')),
            'mts' => $em->getRepository('WoojinGoodsBundle:GoodsMT')->findBy(array(), array('name' => 'ASC')),
            'levels' => $em->getRepository('WoojinGoodsBundle:GoodsLevel')->findBy(array(), array('name' => 'ASC')),
            'payTypes' => $em->getRepository('WoojinOrderBundle:PayType')->findAll(),
            'categorys' => $em->getRepository('WoojinGoodsBundle:Category')->findAll(),
            'yahooCategorys' => $em->getRepository('WoojinGoodsBundle:YahooCategory')->findAll(),
            'storeCategorys' => $storeCategorys,
            'storePayments' => $storePayments,
            'storeShippings' => $storeShippings,
            'seoSlogans' => $em->getRepository('WoojinGoodsBundle:SeoSlogan')->findAll(),
            'shippingOptions' => $em->getRepository('WoojinStoreBundle:ShippingOption')->findAll()
        );
    }

    /**
     * @Route("/edit/{sn}/v2", name="goods_edit_v2_from_sn", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport", options={"sn" = "sn"})
     * @Template("WoojinGoodsBundle:Goods:editV2.html.twig")
     * @Method("GET")
     */
    public function editV2SnAction($goods)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        /**
         * 該商品的所有身分
         *
         * @var array
         */
        $goodsFamilys = $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->eq('g.parent', $goods->getParent()->getId())
            )
            ->getQuery()->getResult();

        /**
         * Yahoo api 的一個 facade
         *
         * @var \Woojin\Utility\YahooApi\Client
         */
        $apiClient = $this->get('yahoo.api.client');

        /**
         * 店內分類
         *
         * @var array(\StdClass)
         */
        $sc = $apiClient->storeCategoryGet();
        $storeCategorys = NULL === $sc || 'fail' === $sc->Response->Status ? $sc : $sc->Response->StoreCategoryList->StoreCategory;

        /**
         * 店內允許付費方式
         *
         * @var array(\StdClass)
         */
        $sp = $apiClient->storePaymentGet();
        $storePayments = NULL === $sp || 'fail' === $sp->Response->Status ? $sp : $sp->Response->PayTypeList->PayType;//$apiClient->storePaymentGet()->Response->PayTypeList->PayType;

        /**
         * 店內允許物流方式
         *
         * @var array(\StdClass)
         */
        $ss = $apiClient->storeShippingGet();
        $storeShippings = NULL === $ss || 'fail' === $ss->Response->Status ? $ss : $ss->Response->ShippingTypeList->ShippingType;//$apiClient->storeShippingGet()->Response->ShippingTypeList->ShippingType;

        return array(
            'goods' => $goods,
            'goodsFamilys' => $goodsFamilys,
            'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findBy(array(), array('name' => 'ASC')),
            'patterns' => $em->getRepository('WoojinGoodsBundle:Pattern')->findBy(array(), array('name' => 'ASC')),
            'sources' => $em->getRepository('WoojinGoodsBundle:GoodsSource')->findBy(array(), array('name' => 'ASC')),
            'colors' => $em->getRepository('WoojinGoodsBundle:Color')->findBy(array(), array('name' => 'ASC')),
            'sizes' => $em->getRepository('WoojinGoodsBundle:GoodsSize')->findBy(array(), array('name' => 'ASC')),
            'mts' => $em->getRepository('WoojinGoodsBundle:GoodsMT')->findBy(array(), array('name' => 'ASC')),
            'levels' => $em->getRepository('WoojinGoodsBundle:GoodsLevel')->findBy(array(), array('name' => 'ASC')),
            'payTypes' => $em->getRepository('WoojinOrderBundle:PayType')->findAll(),
            'categorys' => $em->getRepository('WoojinGoodsBundle:Category')->findAll(),
            'yahooCategorys' => $em->getRepository('WoojinGoodsBundle:YahooCategory')->findAll(),
            'storeCategorys' => $storeCategorys,
            'storePayments' => $storePayments,
            'storeShippings' => $storeShippings,
            'seoSlogans' => $em->getRepository('WoojinGoodsBundle:SeoSlogan')->findAll(),
            'shippingOptions' => $em->getRepository('WoojinStoreBundle:ShippingOption')->findAll()
        );
    }

    /**
     * Event:
     *  -> emit updateProductEvent
     *  -> paramHandler
     *  -> updateHandler
     *  -> logHandler
     *  -> imgHandler
     *  -> desimgHandler
     *  
     * GenParamPool, UpdateService, MetaService,ImgService, DesImgService
     * 
     * @Route("/edit/{id}/v2/update", requirements={"id" = "\d+"}, name="goods_update_v2", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
     * @Template()
     * @Method("POST")
     */
    public function updateV2Action(Request $request, GoodsPassport $goods)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        try {
            /**
             * 商品成本
             *
             * @var integer
             */
            $cost = (int) $request->request->get('goods_cost', $goods->getCost());

            /**
             * 商品售價
             *
             * @var integer
             */
            $price = (int) $request->request->get('goods_price', $goods->getPrice());

            $isPriceDiff = (
                ((int) $cost !== (int) $goods->getCost())
                || ((int) $price !== (int) $goods->getPrice())
            );

            /**
             * 目前登入的使用者實體
             *
             * @var \Woojin\UserBundle\Entity\User
             */
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if ($isPriceDiff) {
                $clue = new AvenueClue;
                $clue->setUser($user);

                $sculp = $this->get('sculper.clue');
                $sculp->initProductModify()->setBefore($goods);
            }

            /**
             * 目前使用者的所屬店家
             *
             * @var \Woojin\UserBundle\Entity\Store
             */
            $store = $user->getStore();

            /**
             * 貨幣符號
             *
             * @var string
             */
            $sym = $this->get('base_method')->getSymbol();

            /**
             * 產編換算使用位數
             *
             * @var integer
             */
            $minDigit = ($sym == 'NT') ? self::SN_RATE : 10;

            /**
             * 商品id
             *
             * @var integer
             */
            $id = $goods->getId();

            /**
             * 產編是否會有更動
             *
             * @var boolean
             */
            $isSnDiff = ($goods->getCost() !== $cost || $goods->getPrice() !== $price);

            /**
             * 網路售價
             *
             * @var integer
             */
            $webPrice = $request->request->get('web_price', $goods->getWebPrice());

            //$webPrice = ($webPrice > $price) ? $price : $webPrice;
            //echo $webPrice; exit();

            /**
             * 型號
             *
             * @var string
             */
            $model = $request->request->get('model', $goods->getModel());

            /**
             * 品名
             *
             * @var string
             */
            $name = $request->request->get('goods_name', $goods->getName());

            /**
             * 品牌序號
             *
             * @var string
             */
            $colorSn = $request->request->get('color_sn', $goods->getColorSn());

            /**
             * 自定義品牌內碼
             *
             * @var string
             */
            $customSn = $request->request->get('custom_sn', $goods->getCustomSn());

            /**
             * 品牌序號
             *
             * @var string
             */
            $orgSn = $request->request->get('org_sn', $goods->getOrgSn());

            /**
             * 商品備註
             *
             * @var string
             */
            $memo = $request->request->get('goods_memo', $goods->getMemo());

            /**
             * 進貨時間
             *
             * @var datetime
             */
            $createAt = is_null($goods->getCreatedAt()) ? null : $goods->getCreatedAt()->format('Y-m-d');

            /**
             * 商品新舊實體
             *
             * @var \Woojin\GoodsBundle\Entity\GoodsLevel
             */
            $level = $em->find('WoojinGoodsBundle:GoodsLevel', $request->request->get('level'));

            /**
             * 商品材質實體
             *
             * @var \Woojin\GoodsBundle\Entity\GoodsMT
             */
            $mt = $em->find('WoojinGoodsBundle:GoodsMT', $request->request->get('mt'));

            /**
             * 商品來源實體
             *
             * @var \Woojin\GoodsBundle\Entity\GoodsSource
             */
            if ($goods->getSource()) {
                $source = $em->find('WoojinGoodsBundle:GoodsSource', $request->request->get('source', $goods->getSource()->getId()));
            } else {
                if (!$request->request->get('source')) {
                    $source = null;
                }
            }

            /**
             * 顏色實體
             *
             * @var \Woojin\GoodsBundle\Entity\Color
             */
            $color = $em->find('WoojinGoodsBundle:Color', $request->request->get('color'));

            /**
             * 尺寸實體
             *
             * @var \Woojin\GoodsBundle\Entity\GoodsSize
             */
            $size = $em->find('WoojinGoodsBundle:GoodsSize', $request->request->get('size'));

            /**
             * 款式實體
             *
             * @var \Woojin\GoodsBundle\Entity\Pattern
             */
            $pattern = $em->find('WoojinGoodsBundle:Pattern', $request->request->get('pattern'));

            /**
             * 品牌實體
             *
             * @var \Woojin\GoodsBundle\Entity\Brand
             */
            $brand = $em->find('WoojinGoodsBundle:Brand', $request->request->get('brand'));

            /**
             * 圖片實體
             *
             * @var \Woojin\GoodsBundle\Entity\Img
             */
            $img = $goods->getImg();

            /**
             * 副圖實體
             *
             * @var \Woojin\GoodsBundle\Entity\Img
             */
            $desimg = $goods->getDesimg();

            /**
             * 是否允許官網上架
             *
             * @var boolean
             */
            $isAllowWeb = ($request->request->get('isAllowWeb', null) == 1) ? true : false;

            /**
             * 是否允許批發
             *
             * @var boolean
             */
            $isAllowWholesale = ($request->request->get('isAllowWholesale', null) == 1) ? true : false;

            /**
             * 批發價
             *
             * @var integer
             */
            $wholesalePrice = $request->request->get('wholesale_price', $goods->getWebPrice());

            /**
             * 是否允許刷卡付費
             *
             * @var boolean
             */
            $isAllowCreditCard = ($request->request->get('isAllowCreditCard', null) == 1) ? true : false;

            /**
             * 是否允許競拍
             *
             * @var boolean
             */
            $isAllowAuction = ($request->request->get('isAllowAuction', null) == 1) ? true : false;

            /**
             * 是否為Alan進貨
             *
             * @var boolean
             */
            $isAlanIn = ($request->request->get('isAlanIn', null) == 1) ? true : false;


            /**
             * 上傳的圖片檔案
             *
             * @var \Symfony\Component\HttpFoundation\File\UploadedFile
             */
            $files = $request->files->get('img');

            /**
             * 上傳的副圖片檔案
             *
             * @var \Symfony\Component\HttpFoundation\File\UploadedFile
             */
            $desFiles = $request->files->get('desimg');

            /**
             * 商品描述
             * @var [text/html]
             */
            $_description = $request->request->get('description', '<p>待補</p>');

            if (!($description = $goods->getDescription())) {
                $description = new Description($_description);

                $goods->setDescription($description);
            }

            $description->setContent($_description);
            $em->persist($description);

            /**
             * 賣點
             * @var [string]
             */
            $_brief = trim($request->request->get('brief', '<p>待補</p>'));

            if (!($brief = $goods->getBrief())) {
                $brief = new Brief($_brief);

                $goods->setBrief($brief);
            }

            $brief->setContent($_brief);
            $em->persist($brief);

            /**
             * POST 過來的category ids
             *
             * @var [array]
             */
            $postCategoryIds = $request->request->get('category', array());

            $goodsCategorys = $goods->getCategorys();

            $postCategorys = array();

            foreach ($goodsCategorys as $category) {
                if (!in_array($category->getId(), $postCategoryIds)) {
                    $goods->removeCategory($category);
                }
            }

            if (!empty($postCategoryIds)) {
                $qb = $em->createQueryBuilder();
                $postCategorys = $qb->select('c')
                    ->from('WoojinGoodsBundle:Category', 'c')
                    ->where(
                        $qb->expr()->in('c.id', $postCategoryIds)
                    )
                    ->getQuery()
                    ->getResult()
                ;
            }

            foreach ($postCategorys as $category) {
                if (!$goods->hasCategory($category)) {
                    $goods->addCategory($category);
                }
            }

            $isDiffMoney = false;
            if ($price != $goods->getPrice() || $cost != $goods->getCost()) {
                $isDiffMoney = true;
            }

            // 更改商品屬性
            $goods
                ->setSeoSlogan($em->find('WoojinGoodsBundle:SeoSlogan', $request->request->get('seoSlogan_id')))
                ->setSeoSlogan2($em->find('WoojinGoodsBundle:SeoSlogan', $request->request->get('seoSlogan2_id')))
                ->setSeoWord($request->request->get('seoWord'))
                ->setColor($color)
                ->setSize($size)
                ->setPattern($pattern)
                ->setColorSn($colorSn)
                ->setCustomSn($customSn)
                ->setModel($model)
                ->setBrand($brand)
                ->setLevel($level)
                ->setSource($source)
                ->setName($name)
                ->setOrgSn($orgSn)
                ->setCost($cost)
                ->setPrice($price)
                ->setMt($mt)
                ->setCreatedAt(new \DateTime($createAt))
                ->setMemo($memo)
                ->setIsAllowWeb($isAllowWeb)
                ->setIsAllowCreditCard($isAllowCreditCard)
                ->setIsAllowWholesale($isAllowWholesale)
                ->setWholesalePrice($wholesalePrice)
                ->setWebPrice($webPrice)
            ;

            if (in_array($goods->getStatus()->getId(), array(
                constant('Woojin\\Utility\\Avenue\\Avenue::GS_ONSALE'),
                constant('Woojin\\Utility\\Avenue\\Avenue::GS_ACTIVITY')
            ))) {
                $goods
                    ->setIsAllowAuction($isAllowAuction)
                    ->setIsAlanIn($isAlanIn)
                    ->setBsoCustomPercentage($request->request->get('bso_custom_percentage', Auction::DEFAULT_CUSTOM_PERCENTAGE))
                ;
            }

            /**
             * 商品產編
             *
             * @var string
             */
            $sn = $goods->genSn(substr($goods->getSn(), 0, 1));

            if ($isDiffMoney) {
                $goods->setSn($sn);
            }

            /**
             * 商品變更記錄儲存
             *
             * @var [object]
             */
            $metaRecorder = $this->get('my_meta_record_method');
            $metaRecorder->recordMeta($this->createGoodsUpdateMsg($goods, $sn, $cost, $price, $model, $createAt));

            $rootDir = $request->server->get('DOCUMENT_ROOT');
            $sepDir = $goods->getImgRelDir($user);
            $comDir = $rootDir . $sepDir;

            //--- 圖片路徑更動 ---//
            if (!is_dir($comDir)) {
                mkdir($comDir, 0777, true);
            }

            //--- 新的圖片蓋掉舊的圖片 ---//
            // 如果有上傳主圖
            if (is_object($files)) {
                /**
                 * 根據上傳檔案取得圖片名稱
                 *
                 * @var string
                 */
                $fileName = $goods->getImgName($files);

                /**
                 * 存放的圖片路徑
                 *
                 * @var string
                 */
                $srcPath = $sepDir . '/' . $fileName;

                /**
                 * 如果原本沒有圖片，新增一個圖片實體
                 */
                if (!$img) {
                    $goods->setImg($img = new Img());

                    $inherits = $goods->getInherits();

                    foreach ($inherits as $inherit) {
                        $inherit->setImg($img);

                        $em->persist($inherit);
                    }

                    if ($files->move($comDir, $fileName)) {
                        $img
                            ->setPath($srcPath)
                            ->setIsTrashed(false)
                        ;

                        $em->persist($img);
                    }
                } else {
                    if (file_exists($request->server->get('DOCUMENT_ROOT') . $img->getPath())) {
                        unlink($request->server->get('DOCUMENT_ROOT') . $img->getPath());

                        $files->move($request->server->get('DOCUMENT_ROOT') . $img->getPurePath(), $img->getName());
                    } else {
                        if ($files->move($comDir, $fileName)) {
                            $img->setPath($srcPath)->setIsTrashed(false);

                            $em->persist($img);
                        }
                    }
                }

                $imgFactory = $this->get('factory.img');
                $imgFactory->createRemoveBorder($img);
            }

            if (is_object($desFiles)) {
                $fileName = $goods->getImgName($desFiles, 'des');
                $srcPath = $sepDir . '/' . $fileName;

                if (!$desimg) {
                    $goods->setDesimg($desimg = new Desimg());

                    $inherits = $goods->getInherits();

                    foreach ($inherits as $inherit) {
                        $inherit->setDesimg($desimg);

                        $em->persist($inherit);
                    }

                    if ($desFiles->move($comDir, $fileName)) {
                        $desimg
                            ->setPath($srcPath)
                            ->setIsTrashed(false)
                        ;

                        $em->persist($desimg);
                    }
                } else {
                    if (file_exists($request->server->get('DOCUMENT_ROOT') . $desimg->getPath())) {
                        unlink($request->server->get('DOCUMENT_ROOT') . $desimg->getPath());

                        $desFiles->move($request->server->get('DOCUMENT_ROOT') . $desimg->getPurePath(), $desimg->getName());
                    } else {
                        if ($desFiles->move($comDir, $fileName)) {
                            $desimg->setPath($srcPath)->setIsTrashed(false);

                            $em->persist($desimg);
                        }
                    }
                }
            }
            //--- End 新的圖片蓋掉舊的圖片 ---//

            if ($isPriceDiff) {
                $sculp->setAfter($goods);
                $clue->setContent($sculp->getContent());
                $em->persist($clue);
            }

            $em->persist($goods);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();

            throw $e;
        }

        // 若產編有更動，同步更新yahoo商城資訊
        if ($isSnDiff && $goods->getYahooId()) {
            $apiClient = $this->get('yahoo.api.client');
            $apiClient->updateMain($apiClient->getProvider()->genR($goods)->getR($goods));
        }

        $this->get('passport.syncer')->sync($goods);

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $id)));
    }

    /**
     * @Route("/behalf/{id}/update", requirements={"id" = "\d+"}, name="admin_goods_behalflize")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("PUT")
     */
    public function toBehalfAction(Request $request, GoodsPassport $product)
    {
        if (!$this->get('security.csrf.token_manager')->isCsrfTokenValid('to_behalf', $request->request->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $em = $this->getDoctrine()->getManager();

        $product
            ->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_BEHALF))
            ->setIsBehalf(true)
        ;
        $em->persist($product);
        $em->flush();

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $product->getId())));
    }

    /**
     * @Route("/customSn/import", name="customSn_import")
     * @Template()
     */
    public function customSnImportAction(Request $request)
    {
        $snArr = array();
        $count = new \stdClass();
        $foundProducts = array();

        $file = $request->files->get('file');

        if ($file) {
            $snMappingCustomSnArr = array(array());
            $storeSn = $this->getUser()->getStore()->getSn();

            $em = $this->getDoctrine()->getManager();

            $dir = '/csv/' . $storeSn . '/';

            // 根目錄和資料夾前段字串組合 = 完整存放路徑
            $dirRoot = $request->server->get('DOCUMENT_ROOT') . $dir;

            // 亂數形成檔案名稱
            $fileName = rand(1000000, 9999999) . 'custom_sn_import.xls';

            // 移動檔案到完整存放路徑
            $file->move($dirRoot, $fileName);

            // 將實體管理員存放至屬性方便其他方法直接調用 ( 其實好像不是好作法 )
            $this->em = $em = $this->getDoctrine()->getManager();

            // 取得excel資料並且轉換成陣列 ,
            // 以此陣列最迭代進行上傳程序
            $phpExcel = $this->get('phpexcel')->createPHPExcelObject("{$dirRoot}{$fileName}");
            $workSheet = $phpExcel->getActiveSheet();

            foreach ($workSheet->getRowIterator() as $rowIndex => $row) {
                $sn = $workSheet->getCellByColumnAndRow(1, $rowIndex)->getValue();
                $snArr[] = $sn;
                $snMappingCustomSnArr[$sn] = $workSheet->getCellByColumnAndRow(0, $rowIndex)->getValue();
            }

            $qb = $em->createQueryBuilder();
            $qb
                ->select('g')
                ->from('WoojinGoodsBundle:GoodsPassport', 'g')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->in('g.sn', $snArr),
                        $qb->expr()->eq(
                            $qb->expr()->substring('g.sn', 1, 1),
                            $qb->expr()->literal($storeSn)
                        )
                    )
                )
            ;

            $foundProducts = $qb->getQuery()->getResult();

            // 去除找得到的商品產編
            foreach ($foundProducts as $product) {
                $index = array_search($product->getSn(), $snArr);

                unset($snArr[$index]);

                $product->setCustomSn($snMappingCustomSnArr[$product->getSn()]);
                $em->persist($product);
            }

            $em->flush();
        }

        return array(
            'snArr' => $snArr,
            'foundProducts' => $foundProducts
        );
    }

    /**
     * @Route("/isbehalf/{id}/toggle", requirements={"id" = "\d+"}, name="admin_goods_toggle_isbehalf", options={"expose"=true})
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("PUT")
     */
    public function updateIsBehalf(Request $request, GoodsPassport $product)
    {
        $em = $this->getDoctrine()->getManager();

        $isBehalf = ($request->request->get('isBehalf') == 1);

        $product->setIsBehalf($isBehalf);

        $em->persist($product);
        $em->flush();

        return new Response(json_encode(array('status' => 1, 'is_behalf' => $isBehalf)));
    }

    /**
     * Find No img
     *
     * @Route("/findnoimg", name="admin_goods_findnoimg")
     * @Method("GET")
     * @Template()
     */
    public function findNoImgAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '128M');

        $products = array();
        $i = 0;
        $batchSize = 20;
        $date = new \DateTime;
        $date->modify('-2 months');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();


        $qb->select(['g','d'])
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.desimg', 'd')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('g.created_at', $qb->expr()->literal($date->format('Y-m-d H:i:s'))),
                    $qb->expr()->isNotNull('g.img'),
                    $qb->expr()->isNotNull('g.desimg'),
                    $qb->expr()->neq('d.path', $qb->expr()->literal('_____'))
                )
            )
            ->groupBy('g.parent')
            ->orderBy('g.sn', 'ASC')
            ->addOrderBy('g.id', 'DESC')
        ;

        $iterableResult = $qb->getQuery()->iterate();
        foreach ($iterableResult as $row) {
            $product = $row[0];
            // do stuff with the data in the row, $row[0] is always the object

            // detach from Doctrine, so that it can be Garbage-Collected immediately
            if ($this->filterNoImg($product)) {
                $products[] = $product;
            }

            if (($i % $batchSize) === 0) {
                $em->clear(); // Detaches all objects from Doctrine!
            }

            $em->detach($row[0]);

            ++$i;
        }

        return array('products' => $products);
    }

    /**
     * Goods Storage
     *
     * @Route("/goods-storage", name="admin_goods_goods_storage")
     * @Method("GET")
     * @Template()
     */
    public function goodsStorage(Request $request)
    {
        $sn = $request->query->get('sn');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $products = $qb
            ->select(['g','b'])
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.batch', 'b')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('g.sn', $qb->expr()->literal($sn))
                )
            )
            ->getQuery()
            ->getResult()
        ;

        $ta = $products ? $products[0] : null;
        $colors = array();
        $sizes = array();
        $table = array();

        if (is_null($ta) || is_null($ta->getBatch())) {
            return array(
                'product' => null,
                'table' => array(),
                'batch' => null,
                'product_sn' => $request->query->get('sn'),
                'colors' => array(),
                'sizes' => array()
            );
        }

        foreach ($ta->getBatch()->getProducts() as $product) {
            $color = $product->getColor() ? $product->getColor()->getName() : '無';
            $size = $product->getSize() ? $product->getSize()->getName() : '無';
            $colors[] = $color;
            $sizes[] = $size;
            
            if (!array_key_exists($color, $table)) {
                $table[$color] = array();
            }

            if (!array_key_exists($size, $table[$color])) {
                $table[$color][$size] = 0;
            }

            if (in_array($product->getStatus()->getId(), array(1, 9))) {
                $table[$color][$size] ++;
            }
        }

        return array(
            'product' => $products ? $products[0] : null,
            'table' => $table,
            'product_sn' => $request->query->get('sn'),
            'batch' => is_null($product) ? null : $product->getBatch(),
            'colors' => array_unique($colors),
            'sizes' => array_unique($sizes)
        );
    }

    protected function filterNoImg(GoodsPassport $product)
    {
        return (!file_exists($_SERVER['DOCUMENT_ROOT'] . $product->getImg()->getPath()) || !file_exists($_SERVER['DOCUMENT_ROOT'] . $product->getDesimg()->getPath()));
    }

    protected function createGoodsUpdateMsg(GoodsPassport $goods, $sn, $cost, $price, $model, $createAt)
    {
        $msgArr = array(
            'title' => 'update-goods',
            'id' => $goods->getId(),
            'sn' => array($goods->getSn(), $sn),
            'cost' => array($goods->getCost(), $cost),
            'price' => array($goods->getPrice(), $price),
            'model' => array($goods->getModel(), $model)
        );

        return json_encode($msgArr);
    }
}
