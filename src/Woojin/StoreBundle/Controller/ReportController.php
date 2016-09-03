<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use PHPExcel_Style_Fill;

use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/", name="admin_store_report_reports")
     * @Template("WoojinStoreBundle:Report:ordersReport.html.twig")
     */
    public function reportsAction()
    {
        $em = $this->getDoctrine()->getManager();

        return array(
            '_token' => $this->get('form.csrf_provider')->generateCsrfToken('unknown'),
            'activitys' => $em->getRepository('WoojinStoreBundle:Activity')->findAll(),
            'brands' => $em->getRepository('WoojinGoodsBundle:Brand')->findAll(),
            'stores' => $em->getRepository('WoojinStoreBundle:Store')->findAll()
        );
    }

    /**
     * 1. 店的選擇改成使用勾選的 stores[]
     * 2. 寄賣/銷售 客戶電話 gcMobil/scMobil
     * 3. 品牌選擇不動, 改參數名稱 brands[]
     * 4. 活動改成multi select, 改參數名稱 activitys[]
     * 
     * @Route("/instore", name="admin_store_stock_report", options={"expose"=true})
     * @Template("WoojinStoreBundle:Report:_instore.html.twig")
     * @Method("POST")
     */
    public function instoreAction(Request $request)
    {
        set_time_limit(0); // time out
        ini_set('memory_limit', '512M');

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.custom', 'c')
        ;
        
        $and = $qb->expr()->andX();

        if (($brands = $request->request->get('brand')) and $brands[0] != 0) {
            $and->add(
                $qb->expr()->in('g.brand', $brands)
            );
        }

        if (($mobil = trim($request->request->get('custom_mobil'))) != '') {
            $and->add($qb->expr()->eq('c.mobil', $qb->expr()->literal($mobil)));
        }

        if (($activitys = $request->request->get('activity')) and $activitys[0] != '0') {
            $and->add($qb->expr()->in('g.activity', $activitys));
        }

        if (($storeSns = $request->request->get('store', array($user->getStore()->getSn())))) {
            /**
             * 表示不限店家
             */
            if (!in_array('0', $storeSns)) {
                $and->add($qb->expr()->in($qb->expr()->substring('g.sn', 1, 1), $storeSns));
            }
        }

        if ($statuses = $request->request->get('status')) {
            $and->add($qb->expr()->in('g.status', $statuses));
        }

        $qb->andWhere($and);
        
        $qb->orderBy('g.updateAt', 'ASC');
        $qb->addOrderBy('g.id', 'ASC');

        $products = $qb->getQuery()->getResult();

        if ((int) $request->request->get('bExport') === 0) {
            return array('products' => $products);
        }   

        $exporter = $this->get('exporter.stock');
        $exporter->create($products);

        return $exporter->getResponse();
    }

    /**
     * @Route("/profit", name="admin_store_profit_report", options={"expose"=true})
     * @Template("WoojinStoreBundle:Report:_profit.html.twig")
     */
    public function profitAction(Request $request)
    {
        set_time_limit(0); // time out
        ini_set('memory_limit', '512M');

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $qb
            ->select('o')
            ->from('WoojinOrderBundle:Orders', 'o')
            ->leftJoin('o.goods_passport', 'gd')
            ->leftJoin('gd.custom', 'gc')
            ->leftJoin('o.custom', 'oc')
            ->leftJoin('o.opes', 'p')
        ;

        $and = $qb->expr()->andX();
        
        $startAt = new \DateTime($request->request->get('startAt'));

        $stopAt = new \DateTime($request->request->get('stopAt'));
        $stopAt->add(new \DateInterval('P1D'));

        $and->add(
            $qb->expr()->between(
                'p.datetime', 
                $qb->expr()->literal($startAt->format('Y-m-d')), 
                $qb->expr()->literal($stopAt->format('Y-m-d'))
            )
        );

        if (($storeSns = $request->request->get('store', array($user->getStore()->getSn())))) {
            /**
             * 表示不限店家
             */
            if (!in_array('0', $storeSns)) {
                $and->add($qb->expr()->in($qb->expr()->substring('gd.sn', 1, 1), $storeSns));
            }
        }

        if (($mobil = trim($request->request->get('custom_mobil'))) != '') {
            $and->add($qb->expr()->eq('gc.mobil', $qb->expr()->literal($mobil)));
        }

        if (($name = trim($request->request->get('custom_name'))) != '') {
            $and->add($qb->expr()->eq('gc.name', $qb->expr()->literal($name)));
        }

        if (($mobil = trim($request->request->get('buy_custom_mobil'))) != '') {
            $and->add($qb->expr()->eq('oc.mobil', $qb->expr()->literal($mobil)));
        }

        if (($name = trim($request->request->get('buy_custom_name'))) != '') {
            $and->add($qb->expr()->eq('oc.name', $qb->expr()->literal($name)));
        }

        if (($activitys = $request->request->get('activity')) and $activitys[0] != '0') {
            $and->add($qb->expr()->in('gd.activity', $activitys));
        }

        if (($kind = $request->request->get('reportKind', array(Avenue::OK_OUT, Avenue::OK_EXCHANGE_OUT, Avenue::OK_TURN_OUT))) and $kind != '') {
            if ((int) $kind === Avenue::OK_OUT) {
                $kind = array(Avenue::OK_OUT, Avenue::OK_EXCHANGE_OUT, Avenue::OK_TURN_OUT);
            }

            $and->add($qb->expr()->in('o.kind', $kind));
        }   

        $and->add($qb->expr()->eq('o.status', Avenue::OS_COMPLETE));
        $and->add($qb->expr()->eq('gd.status', Avenue::GS_SOLDOUT));
        $and->add($qb->expr()->neq('o.pay_type', Avenue::PT_STAFF));

        $qb->andWhere($and)->groupBy('gd.id');
        $orders = $qb->getQuery()->getResult();

        $products = array();

        foreach ($orders as $order) {
            if ($order->getOpes()->last()->getDatetime() >= $startAt 
                && $order->getOpes()->last()->getDatetime() <= $stopAt
            ) {
                $products[] = $order->getProduct();
            }
        }

        if ($request->request->get('bExport') == 0) {
            return array('products' => $products);
        }

        $exporter = $this->get('exporter.profit');
        $exporter->setMap($em->getRepository('WoojinStoreBundle:Store')->genStoreSnMap())->create($products);
        
        return $exporter->getResponse();
    }
}
