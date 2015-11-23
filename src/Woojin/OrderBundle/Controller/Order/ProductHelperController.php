<?php

namespace Woojin\OrderBundle\Controller\Order;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\Utility\Avenue\Avenue;

/**
 * Autocomplete controller.
 *
 * @Route("/producthelper")
 */
class ProductHelperController extends Controller
{
    /**
     * @Route("/onsale_sn", name="order_autoComplete_goodsSn", options={"expose"=true})
     * @Method("GET")
     */
    public function orderAutoComplaeteGoodsSnAction(Request $request)
    {                
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $sns = array();
        
        $products = $qb->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->like('g.sn', $qb->expr()->literal('%' . $request->query->get('term') . '%')),
                    $qb->expr()->eq('g.status', Avenue::GS_ONSALE)
                )
            )
            ->orderBy('g.id', 'desc')
            ->setFirstResult(Avenue::START_FROM)
            ->setMaxResults(Avenue::MAX_RES)
            ->getQuery()
            ->getResult();

        foreach ($products as $product) {
            $sns[] = $product->getSn();
        }

        return new JsonResponse($sns);
    }

    /**
     * @Route("/sold_sn", name="order_autoComplete_goodsSn_selled", options={"expose"=true})
     * @Method("GET")
     */
    public function orderAutoComplaeteGoodsSnSelledAction(Request $request)
    {        
        $em = $this->getDoctrine()->getManager();
        
        $qb = $em->createQueryBuilder();

        $sns = array();
        
        $products = $qb
                ->select('g')
                ->from('WoojinGoodsBundle:GoodsPassport', 'g')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->like('g.sn', $qb->expr()->literal('%' . $request->query->get('term') . '%')),
                        $qb->expr()->eq('g.status', Avenue::GS_SOLDOUT)
                    )
                )
                ->orderBy('g.id', 'desc')
                ->setFirstResult(Avenue::START_FROM)
                ->setMaxResults(Avenue::MAX_RES)
                ->getQuery()
                ->getResult()
            ;

        foreach ($products as $product) {
            $sns[] = $product->getSn();
        }
            
        return new JsonResponse($sns);
    }

    /**
    * @Route("/activity_goods", name="order_activity_search_by_goodsSn", options={"expose"=true})
    * @Template("WoojinGoodsBundle:Goods:goods.search.ajax.html.twig")
    */
    public function orderSearchActivityGoodsBySnAction(Request $request)
    {
        $sn = $request->request->get('sGoodsSn');

        $em = $this->getDoctrine()->getManager();
           
        $products = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findIsActivityByGoodsSn($sn);

        return array('GoodsPassports' => $products);
    }

    /**
     * @Route("/search/goodsSn", name="order_search_by_goodsSn", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.search.ajax.html.twig")
     */
    public function orderSearchByGoodsSnAction(Request $request)
    {            
        $sn = $request->request->get('sGoodsSn');

        $user = $this->get('security.context')->getToken()->getUser();

        $products = array();

        if (substr($sn, 0, 1) === $user->getStore()->getSn()) {
            $products = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsPassport')
                ->findBy(array('sn' => $sn));
        }

        return array('GoodsPassports' => $products);
    }

    /**
     * @Route("/selled_product", name="order_search_by_goodsSn_selled", options={"expose"=true})
     * @Template("WoojinGoodsBundle:Goods:goods.search.ajax.html.twig")
     */
    public function orderSearchByGoodsSnSelledAction(Request $request)
    {            
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
            ->where( 
                $qb->expr()->andX( 
                    $qb->expr()->eq('gd.sn', $qb->expr()->literal($request->request->get('sGoodsSn'))), 
                    $qb->expr()->eq('gd.status', Avenue::GS_SOLDOUT)
                )  
            )
        ;
        
        $products = $qb->getQuery()->getResult();

        return array('GoodsPassports'  => $products);
    }

    /**
     * @Route("/selled_sn", name="order_selled_search_by_goodsSn", options={"expose"=true})
     */
    public function orderSelledSearchByGoodsSn(Request $request)
    {            
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $order = $qb->select('o')
            ->from('WoojinOrderBundle:Orders' , 'o')
            ->join('o.goods_passport', 'g')
            ->where( 
                $qb->expr()->andX( 
                    $qb->expr()->eq('g.sn', $qb->expr()->literal($request->request->get('sGoodsSn'))),
                    $qb->expr()->eq('g.status', Avenue::GS_SOLDOUT),
                    $qb->expr()->neq('o.status', Avenue::OS_CANCEL),
                    $qb->expr()->in('o.kind', array(
                            Avenue::OK_OUT,
                            Avenue::OK_TURN_OUT,
                            Avenue::OK_EXCHANGE_OUT,
                            Avenue::OK_WEB_OUT,
                            Avenue::OK_SPECIAL_SELL,
                            Avenue::OK_SAME_BS
                        ) 
                    )
                ) 
            )
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return new Response(($order) ? $order->getPaid() : '');
    }
}
