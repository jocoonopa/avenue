<?php

namespace Woojin\ApiBundle\Controller;

//Third Party
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\Utility\Handler\ResponseHandler;

use Woojin\Utility\Avenue\Avenue;

class GoodsPassportController extends Controller
{
    /**
     * @Route("/goods_passport/testdrop", name="api_goodsPassport_deleteTest", options={"expose"=true})
     * @Method("GET")
     */
    public function deleteAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user->getId() !== Avenue::USER_ENGINEER_ID) {
            throw $this->createNotFoundException('Not exists');
        }

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->like('g.name', $qb->expr()->literal('%TestFor%'))
            )
        ;

        $products = $qb->getQuery()->getResult();

        foreach ($products as $product) {
            $em->remove($product);
        }

        $em->flush();

        return new Response('ok');
    }

    /**
     * @Route("/goods_passport/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_goodsPassport_show", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="取得商品資訊，ETag cache",
     *  requirements={
     *      {
     *          "name"="id",
     *          "requirement"="\d+",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="Entity GoodsPassport's id"
     *      },
     *      {
     *          "name"="_format",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="回傳的格式，支援 json, xml, html"
     *      }
     *  }
     * )
     */
    public function showAction(Request $request, $goods, $_format)
    {
        $goods->setCost(0);
        $goods->setSn($goods->getSn(true));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonGoodsPassport = $serializer->serialize($goods, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $jsonGoodsPassport, $_format);
    }

    /**
     * @Route("/goods_passport/sn/{sn}/{_format}", defaults={"_format"="json"}, name="api_goodsPassport_show_bySn", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport", options={"sn": "sn"})
     * @Method("GET")
     */
    public function showBySnAction(Request $request, GoodsPassport $goods = NULL, $sn, $_format)
    {
        if (is_null($goods)) {
            $em = $this->getDoctrine()->getManager();
            $goods = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => str_replace('%', '', $sn)));

            if (is_null($goods)) {
                throw $this->createNotFoundException();
            }
        }

        $goods->setCost(0);
        $goods->setSn($goods->getSn(true));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonGoodsPassport = $serializer->serialize($goods, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $jsonGoodsPassport, $_format);
    }

    /**
     * @Route("/goods_passport/multiSn/{longSn}/{_format}", defaults={"_format"="json"}, name="api_goodsPassport_show_byMultiSn", options={"expose"=true})
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="輸入多筆產編取得商品資訊",
     *  requirements={
     *      {
     *          "name"="longSn",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Entity GoodsPassport's sn"
     *      }
     *  }
     * )
     */
    public function showByMultiSnAction(Request $request, $longSn, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->in('g.sn', explode('@', str_replace('%', '', $longSn)))
            )
            ->orderBy('g.id', 'DESC')
        ;

        $products = $qb->getQuery()->getResult();

        foreach ($products as $product) {
            $product->setCost(0);
            $product->setSn($product->getSn(true));
        }

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonProducts = $serializer->serialize($products, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getETag($request, $jsonProducts, $_format);
    }

    /**
     * @Route("/goods_passport/{jIds}/{_format}", defaults={"_format"="json"}, name="api_goodsPassport_multishow", options={"expose"=true})
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="取得商品資訊",
     *  requirements={
     *      {
     *          "name"="id",
     *          "requirement"="\d+",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="Entity GoodsPassport's id"
     *      },
     *      {
     *          "name"="_format",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="回傳的格式，支援 json, xml, html"
     *      }
     *  }
     * )
     */
    public function showMultiAction(Request $request, $jIds, $_format)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = json_decode($jIds);

        if (empty($ids)) {
            return new Response('[]');
        }

        $products = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findByIds($ids);

        foreach ($products as $product) {
            $product->setCost(0);
            $product->setSn($product->getSn(true));
        }

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonProducts = $serializer->serialize($products, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getNoncached($request, $jsonProducts, $_format);
    }

    /**
     * @Route("/goods_passport/fetch/{_format}", defaults={"_format"="json"}, name="api_goodsPassport_fetchWithCondition", options={"expose"=true})
     * @Method("POST")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="根據搜尋條件取得商品資訊，不需要作 cache"
     * )
     */
    public function fetchAction(Request $request, $_format)
    {
        /**
         * 字串流
         *
         * @var [string]
         */
        $content = $request->getContent();

        /**
         * stdClass, 有一個 jsonCondition屬性，該屬性內容為 json 字串
         *
         * @var [\stdClass]
         */
        $conditionClass = json_decode($content);

        /**
         * 條件物件, 格式如下
         *
         * @example
         * stdClass Object
         *(
         *    [gd] => stdClass Object
         *        (
         *            [brands] => Array
         *                (
         *                    [0] => stdClass Object
         *                        (
         *                            [id] => 59
         *                            [name] => 3.1
         *                            [isChecked] => 1
         *                        )
         *
         *                    [1] => stdClass Object
         *                        (
         *                            [id] => 23
         *                            [name] => ADMJ
         *                            [isChecked] => 1
         *                        )
         *
         *                    [2] => stdClass Object
         *                        (
         *                            [id] => 66
         *                            [name] => agnes b
         *                            [isChecked] => 1
         *                        )
         *
         *                )
         *
         *        )
         *
         *)
         *
         * @var [\stdClass]
         */
        $condition = json_decode($conditionClass->jsonCondition);
        $this->addOnBoardAndIsWebConstraint($condition);

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        /**
         * 自製的 條件轉換 query 服務
         */
        $gdConverter = $this->get('gd.converter');
        $gdConverter->gen($condition, false, false, true);

        // 除錯的時候拿掉這段註解可以看 condition 的詳情和實際執行的 Dql 語句
        //
        //$gdConverter->dump()->_print();

        $goodses = $gdConverter->getResult();

        // 對於權限不夠的，成本必須要隱藏起來，否則客人看到成本不就好笑了?
        if (!$this->get('authority.judger')->isCostValid()) {
            foreach ($goodses as $key => $goods) {
                $goods->setCost('0');
                $goods->setSn($goods->getSn(true));
            }
        }

        $count = $gdConverter->getCount();

        $res = array();
        $res['goodses'] = $goodses;
        $res['count'] = $count;
        $res['status'] = '1';

        $jsonResponse = $serializer->serialize($res, $_format);

        $responseHandler = new ResponseHandler;

        return $responseHandler->getNoncached($request, $jsonResponse, $_format);
    }

    /**
     * @Route("/goods_passport/custom_cart/{_format}", defaults={"_format"="json"}, name="api_goodsPassport_customCart", options={"expose"=true})
     * @Method("POST")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="客戶端傳送 cookie 的購物車商品索引陣列，用該陣列取得商品資料，並且計算出總價和活動後價格"
     * )
     */
    public function customCartAction(Request $request, $_format){}

    /**
     * 查詢條件增加 "上架" 且 "允許官網顯示"，防止有心人士看到不該看的東西
     *
     * @param stdClass &$condition [查詢條件]
     */
    private function addOnBoardAndIsWebConstraint(&$condition)
    {
        $condition->isWeb = true;

        // if (!property_exists($condition, 'gd')) {
        //     $condition->gd = new \stdClass;
        // }

        // $condition->gd->status = new \stdClass;
        // $condition->gd->status->id = Avenue::GS_ONSALE;
    }

    // @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport", options={"sn": "sn"})

    /**
     * @Route("/goods_passport/print/label/{sn}/{_format}", defaults={"_format"="json"}, name="api_goodsPassport_printlabel", options={"expose"=true})
     * 
     * @Method("GET")
     */
    public function printlabel(Request $request, $_format)
    {
        $client = new \Predis\Client([
            'scheme' => 'tcp',
            'host' => $this->container->getParameter('redis.host'),
            'port' => $this->container->getParameter('redis.port'),
        ]);

        $client->publish('test', json_encode(['foo' => 'bar']));

        $responseHandler = new ResponseHandler;

        return $responseHandler->getResponse(json_encode(['foo' => 'bar']), $_format);
    }

    /**
     * * @Route("/goods_passport/{id}/storage/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_goodsPassport_storage", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
     */
    public function storage(Request $request, GoodsPassport $goods, $_format)
    {
        $responseHandler = new ResponseHandler;

        $em = $this->getDoctrine()->getManager();

        $em->persist($goods);
        $em->flush();

        return $responseHandler->getNoncached($request, json_encode(['status' => 200]), $_format);
    }

    /**
     * @Route("/goods_passport/{id}/shipment/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_goodsPassport_shipment", options={"expose"=true})
     * @ParamConverter("goods", class="WoojinGoodsBundle:GoodsPassport")
     * 
     * @Method("GET")
     */
    public function shipment(Request $request, GoodsPassport $goods, $_format)
    {
        $responseHandler = new ResponseHandler;

        $em = $this->getDoctrine()->getManager();

        $goods->setIsInShipment(1 == request('is_in_shipment'));
        $goods->save();

        $em->persist($goods);
        $em->flush();

        return $responseHandler->getNoncached($request, json_encode(['status' => 200]), $_format);
    }
}
