<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\Utility\Handler\ResponseHandler;

/**
 * Search controller.
 *
 * @Route("/search")
 */
class SearchController extends Controller
{
    /**
     * @Route("", name="admin_search")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {       
        return array('tabs' => $this->getViewTab());
    }

    protected function getViewTab()
    {
        return array(
            'brand' => '品牌',
            'pattern' => '款式',
            'color' => '顏色',
            'mt' => '材質',
            'level' => '新舊',
            'productStatus' => '商品狀態',
            'activity' => '活動',
            'isAllowWeb' => '官網顯示',
            'isYahoo' => '商城顯示',
            'store' => '店鋪',
            'textSeries' => '綜合查詢',
            'orderStatus' => '訂單狀態',
            'orderKind' => '訂單種類',
            'opeDatetime' => '操作時間',
            'customMobil' => '客戶電話',
            'price' => '售價'
        );
    }

    /**
     * @Route("", name="admin_search_res", options={"expose"=true})
     * @Method("POST")
     * @Template()
     */
    public function resAction(Request $request)
    {       
        $finder = $this->get('product.finder');
        $finder->find($request);

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $json = $serializer->serialize(array(
            'count' => $finder->getCount(),
            'page' => $finder->getPage(),
            'perpage' => $finder->getPerpage(),
            'data' => $finder->getData()
        ), 'json');

        $responseHandler = new ResponseHandler;
        
        return $responseHandler->getResponse($json, 'json');
    }
}
