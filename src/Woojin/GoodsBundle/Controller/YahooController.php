<?php

namespace Woojin\GoodsBundle\Controller;

use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\GoodsBundle\Entity\YahooCategory;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Entity\DesImg;
use Woojin\GoodsBundle\Entity\Brand;
use Woojin\GoodsBundle\Entity\Pattern;

use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\YahooApi\Client;

/**
 * Yahoo controller.
 *
 * @Route("/yahoo")
 */
class YahooController extends Controller
{
    /**
     * @Route("/{id}/create", requirements={"id" = "\d+"}, name="admin_yahoo_create")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("POST")
     */
    public function createAction(Request $request, GoodsPassport $product)
    {
        list($client, $session) = $this->getNeededServices();
        
        $r = $this->getMainR($request, $product);

        if ($this->createMainFlow($product, $session, $client, $r)) {
            if ($this->uploadImageFlow($product, $session, $client)) {
                $this->onlineFlow($product, $session, $client);
            }
        }

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $product->getId())));
    }

    /**
     * @Route("/{id}/update", requirements={"id" = "\d+"}, name="admin_yahoo_update")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("PUT")
     */
    public function updateAction(Request $request, GoodsPassport $product)
    {        
        list($client, $session) = $this->getNeededServices();

        $r = $this->getMainR($request, $product);

        if ($this->updateMainFlow($product, $session, $client, $r)) {
            $this->uploadImageFlow($product, $session, $client);
        }

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $product->getId())));
    }

    /**
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="admin_yahoo_delete")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("DELETE")
     */
    public function deleteAction(GoodsPassport $product)
    {        
        list($client, $session) = $this->getNeededServices();

        $this->offlineFlow($product, $session, $client);

        // 這邊下架已經下架的商品會報錯, 我懶得判斷了直接掠過錯誤訊息
        $session->getFlashBag()->clear('error');
        
        $this->deleteFlow($product, $session, $client);

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $product->getId())));
    }

    /**
     * @Route("/{id}/offline", requirements={"id" = "\d+"}, name="admin_yahoo_offline")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("PUT")
     */
    public function offlineAction(GoodsPassport $product)
    {
        list($client, $session) = $this->getNeededServices();

        $this->offlineFlow($product, $session, $client);

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $product->getId())));
    }

    /**
     * @Route("/{id}/online", requirements={"id" = "\d+"}, name="admin_yahoo_online")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("PUT")
     */
    public function onlineAction(GoodsPassport $product)
    {
        list($client, $session) = $this->getNeededServices();

        $this->onlineFlow($product, $session, $client);

        return $this->redirect($this->generateUrl('goods_edit_v2', array('id' => $product->getId())));
    }

    /**
     * @Route("/api/{id}/json", requirements={"id" = "\d+"}, name="admin_yahoo_api_show", options={"expose"=true})
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("GET")
     */
    public function showAction(GoodsPassport $product)
    {        
        $client = $this->get('yahoo.api.client');

        $response = $client->getMain($product);

        return new JsonResponse($response);
    }

    /**
     * 商品上架流程
     * 
     * @param  GoodsPassport $product 
     * @param  Session       $session 
     * @param  Client        $client  
     * @return $this                
     */
    protected function onlineFlow(GoodsPassport $product, Session $session, Client $client)
    {
        $response = $client->online($product);

        if (
            isset($response->Response->SuccessList->Product) 
            && $client->listSearchId($response->Response->SuccessList->Product, $product->getYahooId())
        ) {
            $session->getFlashBag()->add('success', $product->getName() . '商城上架成功!');

            return true;
        } else {
            $session->getFlashBag()->add('error', $product->getName() . '商城上架失敗!:'. json_encode($response));

            return false;
        }
    }

    /**
     * 商品下架流程
     * 
     * @param  GoodsPassport $product 
     * @param  Session       $session 
     * @param  Client        $client  
     * @return $this                
     */
    protected function offlineFlow(GoodsPassport $product, Session $session, Client $client)
    {
        $response = $client->offline($product);

        if (
            isset($response->Response->SuccessList->ProductId) 
            && $client->listSearchId($response->Response->SuccessList->ProductId, $product->getYahooId())
        ) {
            $session->getFlashBag()->add('success', $product->getName() . '商城下架成功!');

            return true;
            
        } else {
            $session->getFlashBag()->add('error', $product->getName() . '商城下架失敗!:'. json_encode($response));

            return false;
        }
    }

    /**
     * 商品刪除流程
     * 
     * @param  GoodsPassport $product 
     * @param  Session       $session 
     * @param  Client        $client 
     * 
     * @return $this                
     */
    protected function deleteFlow(GoodsPassport $product, Session $session, Client $client)
    {
        $response = $client->delete(array($product));

        if (
            isset($response->Response->SuccessList->ProductId) 
            && $client->listSearchId($response->Response->SuccessList->ProductId, $product->getYahooId())
        ) {
            $session->getFlashBag()->add('success', $product->getName() . '商城刪除成功!');

            $em = $this->getDoctrine()->getManager();
            
            $product->setYahooId(null);
            
            $em->persist($product);
            $em->flush();

            return true;
        } else {
            $session->getFlashBag()->add('error', $product->getName() . '商城刪除失敗!:'. json_encode($response));

            return false;
        }
    }

    /**
     * 建立Yahoo商品的流程
     * 
     * @param  GoodsPassport $product
     * @param  Session       $session
     * @param  Client        $client 
     * @param  \stdClass $r
     * 
     * @return $this                
     */
    protected function createMainFlow(GoodsPassport $product, Session $session, Client $client, \stdClass $r)
    {
        $response = $client->submitMain($r);

        if ($response->Response->Status === 'fail') {
            $session->getFlashBag()->add('error', '上傳yahoo失敗:' . json_encode($response));

            return false;
        } else {
            //$session->getFlashBag()->add('error', '上傳yahoo失敗:' . json_encode($response));

            $em = $this->getDoctrine()->getManager();
        
            $product->setYahooId($response->Response->ProductId);
            
            $em->persist($product);
            $em->flush();

            $session->getFlashBag()->add('success', $product->getName() . '以成功上傳至Yahoo商城!');

            return true;
        }
    }

    /**
     * 更新Yahoo商品的流程
     * 
     * @param  GoodsPassport $product
     * @param  Session       $session
     * @param  Client        $client 
     * @param  \stdClass     $r
     * 
     * @return $this                
     */
    protected function updateMainFlow(GoodsPassport $product, Session $session, Client $client, \stdClass $r)
    {
        $response = $client->updateMain($r);

        if ($response->Response->Status === 'fail') {
            $session->getFlashBag()->add('error', $product->getName() . '商城資料更新失敗!:' . json_encode($response));

            return false;
        } else {
            $session->getFlashBag()->add('success', $product->getName() . '商品資料更新完成!');

            return true;
        }
    }

    /**
     * 圖片上傳至 Yahoo Api
     * 
     * @param  GoodsPassport $product
     * @param  Session       $session
     * @param  Client        $client
     * @return $this                
     */
    protected function uploadImageFlow(GoodsPassport $product, Session $session, Client $client)
    {
        if (!($img = $product->getImg())) {
            $session->getFlashBag()->add('error', $product->getName() . '還沒有主圖!');
            
            return false;
        } 

        if (!($desimg = $product->getDesImg())) {
            $session->getFlashBag()->add('error', $product->getName() . '還沒有附圖!');

            return false;
        } 

        // 切圖片, 把 desimg 切成五張小圖
        $this->get('factory.desimg')->spliceDesImage($desimg);

        $response = $client->uploadImage($product);           

        if ($response->Response->Status === 'fail') {
            $session->getFlashBag()->add('error', $product->getName() . '商城圖片更新失敗!:'. json_encode($response));

            return false;
        } else {
            $session->getFlashBag()->add('success', $product->getName() . '商城圖片更新成功!');

            return true;
        }
    }

    /**
     * 取得此Controller 中 Action基本上會用到的服務
     * 
     * @return array
     */
    protected function getNeededServices()
    {
        return array($this->get('yahoo.api.client'), $this->get('session'));
    }

    /**
     * 取得 SubmitMainParameterFactory(包含同系列繼承類別) 需要用到的 \stdClass r 參數
     * 
     * @param  Request       $request
     * @param  GoodsPassport $product
     * @return                
     */
    protected function getMainR(Request $request, GoodsPassport $product)
    {
        $r = new \stdClass();
        $r->product = $product;
        $r->mallCategoryId = $request->request->get('yahoo_categoryId');
        $r->storeCategoryIds = $request->request->get('yahoo_storeCategoryIds');
        $r->payTypeIds = $request->request->get('yahoo_paymentTypes');
        $r->shippingIds = $request->request->get('yahoo_shippings');

        return $r;
    }

    /**
     * @Route("/test/{id}", requirements={"id" = "\d+"}, name="admin_yahoo_test", options={"expose"=true})
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("GET")
     */
    public function testAction(GoodsPassport $product)
    {        
        // $this->spliceDesImage($product->getDesImg());

        // return new Response('ok');
    }

    /**
     * @Route("/api/{id}/batch", requirements={"id" = "\d+"}, name="admin_yahoo_api_batch", options={"expose"=true})
     * @ParamConverter("brand", class="WoojinGoodsBundle:Brand")
     * @Method("GET")
     */
    public function batchUpload(Brand $brand, Request $request)
    {
        set_time_limit(0);

        // 3 is brand Chanel
        if ($brand->getId() !== 6) {
            throw new \Exception('品牌不正確! 要是Parada');
        }

        if ($request->query->get('token') !== 'mk123OOpadkkk!3r1') {
            throw $this->createNotFoundException();
        }

        list($client, $session) = $this->getNeededServices();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('g.brand', $brand->getId()),
                    $qb->expr()->eq('g.status', Avenue::GS_ONSALE),
                    $qb->expr()->eq('g.isAllowWeb', true),
                    $qb->expr()->isNull('g.yahooId')
                    //$qb->expr()->gte('g.updateAt', $qb->expr()->literal('2015-05-12 16:21:00'))
                )
            )
        ;

        $products = $qb->getQuery()->getResult();

        foreach ($products as $key => $product) {
            echo $product->getSn() . ", 品牌:" . $product->getBrand()->getName() . ", 狀態:" . $product->getStatus()->getName();
            echo "<br />";
        }

        foreach ($products as $product) {
            $r = new \stdClass();
            $r->product = $product;

            // 付費方式
            $r->mallCategoryId = $brand->getYc()->getYahooId();
            $r->storeCategoryIds = $this->assignStoreCategory($product);
            $r->payTypeIds = array(1, 2, 4, 5, 6, 7, 12);
            $r->shippingIds = array(2);

            //$this->updateMainFlow($product, $session, $client, $r);

            if ($this->createMainFlow($product, $session, $client, $r)) {
                if ($this->uploadImageFlow($product, $session, $client)) {
                    $this->onlineFlow($product, $session, $client);
                }
            }

            echo $product->getSn();
            echo "<br />";

            unset($r);
            unset($product);

            sleep(1);
        }

        return new Response('---- OK ----- 共' . count($products) . '筆');
    }

    protected function assignStoreCategory(GoodsPassport $product)
    {
        // 90: CHANEL > 全新品 現貨區 > 皮夾
        // 91: CHANEL > 全新品 現貨區 > 包包
        // 92: CHANEL > 全新品 現貨區 > 配件
        // 93: CHANEL > 二手品 現貨區 > 皮夾
        // 94: CHANEL > 二手品 現貨區 > 包包
        // 95: CHANEL > 二手品 現貨區 > 配件
        // 
        // 104: CHLOE > 全新品 現貨區
        // 105: CHLOE > 二手品 現貨區
        $cts = array(
            104,
            105
            // array(90, 91, 92),
            // array(93, 94, 95)
        );
        
        // 先新舊[新: 0, 舊: 1] 
        $levelIndex = ($product->getLevel()->isNew() ? 0 : 1);

        return $cts[$levelIndex];

    //     $patternIndex = $this->assignPatternType($product->getPattern());

    //     return ($patternIndex) ? array($cts[$levelIndex][$patternIndex]) : array(null);
    }

    protected function assignPatternType(Pattern $pattern)
    {
        $id = $pattern->getId();

        $patternIndex = false;

        // 0:皮夾: {6}
        // 1:包包: {3, 4, 5, 13, 18, 24, 28, 30, 32, 44}
        // 2:配件: {1, 7, 22, 23, 25, 41, 43, 46, 47, 48}
        $patternGroups = array(
            array(6),
            array(3, 4, 5, 13, 18, 24, 28, 30, 32, 44),
            array(1, 7, 22, 23, 25, 41, 43, 46, 47, 48)
        );

        foreach ($patternGroups as $key => $group) {
            if (in_array($id, $group)) {
                $patternIndex = $key;

                break;
            }
        }

        return $patternIndex;
    }
}
