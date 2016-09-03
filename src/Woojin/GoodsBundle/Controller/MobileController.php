<?php

namespace Woojin\GoodsBundle\Controller;

use Woojin\Utility\Avenue\Avenue;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Woojin\Utility\Handler\ResponseHandler;

class MobileController extends Controller
{
    const PER_PAGE = 50;
    /**
     * @Route("/mobile", name="admin_goods_mobile_index", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $baseMethod = $this->get('base_method');

        if (!$baseMethod->isMobile()) {
            throw new HttpException('僅限行動裝置使用');
        }

        if (!($user->getRole()->hasAuth('READ_COST_OWN') && $user->getRole()->hasAuth('READ_COST_ALL'))) {
            throw new AuthenticationException('沒有足夠權限!');
        }

        $em = $this->getDoctrine()->getManager();

        $brands = $em->getRepository('WoojinGoodsBundle:Brand')->findAll();

        $mts = $em->getRepository('WoojinGoodsBundle:GoodsMT')->findAll();

        $levels = $em->getRepository('WoojinGoodsBundle:GoodsLevel')->findAll();

        $patterns = $em->getRepository('WoojinGoodsBundle:Pattern')->findAll();

        $colors = $em->getRepository('WoojinGoodsBundle:Color')->findAll();

        $statuses = $em->getRepository('WoojinGoodsBundle:GoodsStatus')->findAll();

        $stores = $em->getRepository('WoojinStoreBundle:Store')->findAll();

        $activitys = $em->getRepository('WoojinStoreBundle:Activity')->findAll();

        return array(
            'brands' => $brands,
            'mts' => $mts,
            'levels' => $levels,
            'patterns' => $patterns,
            'colors' => $colors,
            'statuses' => $statuses,
            'stores' => $stores,
            'activitys' => $activitys
        );
    }

    /**
     * @Route("/mobile/fetch", name="admin_goods_mobile_fetch", options={"expose"=true})
     * @Method("POST")
     */
    public function fetchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $request->request;

        $qb = $em->createQueryBuilder();
        $qb
            ->select(array('g, od, op'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.orders', 'od')
            ->leftJoin('od.opes', 'op')
            ->where($qb->expr()->neq(
                $qb->expr()->substring('g.sn', 1, 1), $qb->expr()->literal('C')
            ))
        ;

        $and = $qb->expr()->andX();

        if ($brandIds = $post->get('brands')) {
            $and->add($qb->expr()->in('g.brand', $brandIds));
        }

        if ($patternIds = $post->get('patterns')) {
            $and->add($qb->expr()->in('g.pattern', $patternIds));
        }

        if ($colorIds = $post->get('colors')) {
            $and->add($qb->expr()->in('g.color', $colorIds));
        }

        if ($levelIds = $post->get('levels')) {
            $and->add($qb->expr()->in('g.level', $levelIds));
        }

        if ($mtIds = $post->get('mts')) {
            $and->add($qb->expr()->in('g.mt', $mtIds));
        }

        if ($statuseIds = $post->get('statuses')) {
            $and->add($qb->expr()->in('g.status', $statuseIds));
        }

        if ($activityIds = $post->get('activitys')) {
            $and->add($qb->expr()->in('g.activity', $activityIds));
        }

        if ($text = $post->get('name')) {
            $and->add($qb->expr()->orX(
                $qb->expr()->like('g.name', $qb->expr()->literal('%' . $text . '%')),
                $qb->expr()->eq('g.sn', $qb->expr()->literal($text)),
                $qb->expr()->eq('g.org_sn', $qb->expr()->literal($text)),
                $qb->expr()->eq('g.model', $qb->expr()->literal($text))
            ));
        }

        if ($storeIds = $post->get('stores')) {
            $map = array('M', 'Y', 'Z', 'X', 'P', 'L', 'C', '$', '#', '!', 'T');

            $res = array();
            foreach ($storeIds as $id) {
                if (9 === $id) {
                    continue;
                }
                
                $res[] = $map[$id];
            }

            $and->add($qb->expr()->in($qb->expr()->substring('g.sn', 1, 1), $res));
        }

        $qb
            ->andWhere($and)
            ->groupBy('g.id')
            ->orderBy('g.id', 'DESC')
        ;

        $qb
            ->setFirstResult(($post->get('page', 1) - 1) * 50)
            ->setMaxResults(50)
        ;

        $queryRes = $qb->getQuery()->getResult();

        $res = array();
        $res['data'] = $queryRes;

        /**
         * Starting with version 2.2 Doctrine ships with a Paginator for DQL queries,
         * Much more ez way to get count of query result
         * 
         * @var object
         */
        $paginator = new Paginator($qb, $fetchJoinCollection = true);
        
        $res['total'] = count($paginator);
        $res['page_sum'] = ceil($res['total'] / 50);
        $res['current_page'] = $post->get('page', 1);
        $res['status'] = 1;

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonResponse = $serializer->serialize($res, 'json');

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getNoncached($request, $jsonResponse, 'json');
    }

    /**
     * @Route("/mobile/order", name="mobile_order", options={"expose"=true})
     * @Method("POST")
     */
    public function orderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->find('WoojinGoodsBundle:GoodsPassport', $request->request->get('id'));

        $orders = $this->hasReadableAuth($product) ? $product->getOrders() : array();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonResponse = $serializer->serialize($orders, 'json');

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getNoncached($request, $jsonResponse, 'json');
    }

    protected function hasReadableAuth($product)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        return ($user->getRole()->hasAuth('READ_ORDER_OWN') && $product->isOwnProduct($user)) || $user->getRole()->hasAuth('READ_ORDER_ALL');
    }
}