<?php

namespace Woojin\OrderBundle\Controller\Order;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\UserBundle\Entity\AvenueClue;
use Woojin\Utility\Avenue\Avenue;

/**
 * Out controller.
 *
 * @Route("/out")
 */
class OutController extends Controller
{
    /**
     * @Route("/ajax/turngoods_get_sellprice", name="turngoods_get_sellprice", options={"expose"=true})
     * @Method("POST")
     */
    public function orderTurnGoodsGetSellPrice (Request $request)
    {
        $product = $this->getDoctrine()->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $request->request->get('sn')));
        $orders = $product->getOrders();

        $data = array();

        $data['price'] = $orders->last()->getRequired();
        $data['payTypeId'] = $orders->last()->getPayType()->getId();
        $data['paid'] = $orders->last()->getPaid();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax/new_turnchange", name="orders_new_turnchange", options={"expose"=true})
     * @Method("POST")
     */
    public function orderNewTurnchangeAction(Request $request)
    {
        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }   

        $products = array();

        $sculper = $this->get('sculper.clue');
        $sculper->initTurnOut();

        $opeLogger = $this->get('logger.ope');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $em->getConnection()->beginTransaction();
        
        try{
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $paytype = $em->find('WoojinOrderBundle:PayType', $nPayTypeId);
            
            $productTurnIn = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $sGoodsTurnSn));
            
            $productTurnOut = $em->find('WoojinGoodsBundle:GoodsPassport', $nGoodsChangeId);

            $orderSold = $qb
                ->select('od')
                ->from('WoojinOrderBundle:Orders', 'od')
                ->join('od.goods_passport', 'gp')
                ->where(
                    $qb->expr()->andX( 
                        $qb->expr()->eq('od.goods_passport', $productTurnIn->getId()),
                        $qb->expr()->eq('gp.status', Avenue::GS_SOLDOUT),
                        $qb->expr()->in('od.kind', array(
                                Avenue::OK_OUT, 
                                Avenue::OK_TURN_OUT, 
                                Avenue::OK_EXCHANGE_OUT,
                                Avenue::OK_WEB_OUT,
                                Avenue::OK_SPECIAL_SELL,
                                Avenue::OK_SAME_BS
                            ) 
                        ),
                        $qb->expr()->neq('od.status', Avenue::OS_CANCEL)
                    ) 
                )
                ->orderBy('od.id', 'DESC')
                ->getQuery()
                ->getOneOrNullResult()
            ;
            
            if ($orderSold) {
                $orderSold->setStatus($em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_CANCEL));
            
                $em->persist($orderSold);
            }
            
            $opeLogger->recordOpe($orderSold, $orderSold->getKind()->getName() . '訂單取消[退款:' . $orderSold->getPaid() . '元]');           

            $ordersTurnIn = new Orders();
            $ordersTurnIn
                ->setGoodsPassport($productTurnIn)
                ->setCustom($orderSold->getCustom())
                ->setPayType($paytype)
                ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_TURN_IN))
                ->setStatus($em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE))
                ->setRequired(0)
                ->setPaid(0)
                ->setParent($orderSold)
                ->setMemo($sOrdersMemo)
            ;
            $em->persist($ordersTurnIn);

            $productTurnIn->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE));
            $em->persist($productTurnIn);
            
            $nBalanceCache = $nBalance;

            $nBalance = $nBalance - ($nOrdersPaidReal - $nOrdersPaid);
            
            $ostatusId = ($nBalance > $nOrdersPaid) ? Avenue::OS_HANDLING : Avenue::OS_COMPLETE;
            
            $invoice = new Invoice;
            $invoice
                ->setCustom($orderSold->getCustom())
                ->setStore($user->getStore())
                ->setHasPrint(0)
                ->setSn(uniqid())
            ;

            $em->persist($invoice);
            
            $orderTurnOut = new Orders();
            $orderTurnOut
                ->setInvoice($invoice)
                ->setGoodsPassport($productTurnOut)
                ->setCustom($orderSold->getCustom())
                ->setPayType($paytype)
                ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_TURN_OUT))
                ->setStatus($em->find('WoojinOrderBundle:OrdersStatus', $ostatusId))
                ->setRequired($nBalance)
                ->setPaid($nOrdersPaid)
                ->setOrgRequired($nBalanceCache)
                ->setOrgPaid($nOrdersPaidReal)
                ->setParent($ordersTurnIn)
                ->setMemo($sOrdersMemo)
            ;
            $em->persist($orderTurnOut);

            $productTurnOut->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_SOLDOUT));
            $em->persist($productTurnOut);
            
            $opeLogger->recordOpe($ordersTurnIn, '成立換貨(進)訂單:[' . $productTurnOut->getSn() . ']');
            $opeLogger->log($orderTurnOut, $user, $paytype, (int) $nOrdersPaidReal, '成立換貨(出)訂單:[' . $nOrdersPaidReal . '元][' . $paytype->getName() . '][' . $productTurnOut->getSn() . ']');

            $em->persist($opeLogger->getOpe());

            $sculper->setAfter($opeLogger->getOpe());

            $clue = new AvenueClue;
            $clue->setUser($user)->setContent($sculper->getContent());

            $em->persist($clue);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        $rRes['purIn'] = $productTurnIn->getId();
        $rRes['sellOut'] = $nGoodsChangeId; 
        
        return new JsonResponse($rRes);
    }

    /**
     * @Route("/sell_goods", name="order_sell_goods" , options={"expose"=true})
     */
    public function orderSellGoodsAction(Request $request)
    { 
        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }
            
        $dc = $this->getDoctrine();
        $em = $dc->getManager();
        $em->getConnection()->beginTransaction();

        $sculper = $this->get('sculper.clue');
        $sculper->initSoldOut();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        try{      
            $paytype = $em->find('WoojinOrderBundle:PayType', $nPayTypeId);
            $orderKind = $em->find('WoojinOrderBundle:OrdersKind', $nOrdersKindId);
            $productStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_SOLDOUT);
            $oCustom = $dc->getRepository('WoojinOrderBundle:Custom')->findOneBy( 
                        array(
                            'mobil' => $nCustomMobil, 
                            'store' => $user->getStore()->getId() 
                        ) 
                    )
                ;

            $product = $dc->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $sGoodsSn));

            if (!in_array($product->getStatus()->getId(), array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))) {
                throw $this->createNotFoundException('商品狀態非上架或活動中');
            }

            $product->setStatus($productStatus);
            $em->persist($product);

            $nDisRate = $paytype->getDiscount();
            $nOrdersRequired = $nOrdersRequired - ($nOrdersPaidReal - $nOrdersPaid);
            $nOrdersStatusId = (($nOrdersRequired - $nOrdersPaid)!= 0) ? Avenue::OS_HANDLING: Avenue::OS_COMPLETE;

            $orderStatus = $em->find('WoojinOrderBundle:OrdersStatus', $nOrdersStatusId);

            $order = new Orders();
            $order
                ->setGoodsPassport($product)
                ->setCustom($oCustom)
                ->setPayType($paytype)
                ->setKind($orderKind)
                ->setStatus($orderStatus)
                ->setRequired($nOrdersRequired)
                ->setOrgRequired($request->request->get('nOrdersRequired'))
                ->setOrgPaid($nOrdersPaidReal)
                ->setPaid($nOrdersPaid)
                ->setMemo($sOrdersMemo)
            ;

            // 找發票
            $Invoices = $dc->getRepository('WoojinOrderBundle:Invoice')->findBy(array('sn' => $request->request->get('invoice_key')));

            if (!$Invoices) {
                $Invoice = new Invoice;

                $Invoice
                    ->setCustom($oCustom)
                    ->setStore($this->getUser()->getStore())
                    ->setHasPrint(0)
                    ->setSn($request->request->get('invoice_key'))
                ;

                $em->persist($Invoice);
            } else {
                $Invoice = $Invoices[0];
            }
            
            $order->setInvoice($Invoice);

            $em->persist($order);

            $opeLogger = $this->get('logger.ope');
            $opeLogger->log($order, $user, $paytype, (int) $request->request->get('nOrdersRequired'), '成立售出訂單:[' . $request->request->get('nOrdersRequired') . '元][' . $paytype->getName() . '][' . $product->getSn() . ']');

            $em->persist($opeLogger->getOpe());

            $sculper->setAfter($opeLogger->getOpe());

            $clue = new AvenueClue;
            $clue->setUser($user)->setContent($sculper->getContent());

            $em->persist($clue);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            throw $e;
        }

        // Yahoo同步刪除商品
        if ($product->getYahooId()) {
            $adapter = $this->get('yahoo.syncer');
            $adapter->delete($product);
        }
        
        return new Response($product->getId());
    }

    /**
     * @Route("/multisale/sell", name="order_multisale_sell", options={"expose"=true})
     */
    public function orderMultieSell (Request $request) 
    {
        foreach ($request->request->keys() as $key) {
            $$key = $request->request->get($key);
        }

        $successProducts = array();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $opeLogger = $this->get('logger.ope');

        $opeLogger = $this->get('logger.ope');

        $dc = $this->getDoctrine();
        $em = $dc->getManager();
        $em->getConnection()->beginTransaction();

        try{
            

            // 訂單id陣列
            $rRollback = array();

            // 金額總計
            $nTotal = 0;

            // 回傳產編字串      
            $returnSn = '';

            // 取得付款方式
            $paytype = $em->find('WoojinOrderBundle:PayType', $nPayTypeId);

            // 根據付款方式取得折扣
            $nDisRate = $paytype->getDiscount();

            // 取得訂單狀態
            $orderKind = $em->find('WoojinOrderBundle:OrdersKind', $nOrdersKindId);

            // 取得商品狀態
            $productStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_SOLDOUT);

            // 取得客戶
            $oCustom = $dc->getRepository('WoojinOrderBundle:Custom')
                ->findOneBy(
                    array( 
                        'mobil' => (($mobil == '')? '00000' : $mobil), 
                        'store' => $user->getStore()->getId() 
                    ) 
                );

            // 若找尋的客戶不存在, 回覆錯誤訊息
            if (!is_object($oCustom)) {
                return new Response ('<div class="alert alert-block alert-danger fade in">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4 class="alert-heading">手機號碼不存在</h4>
          </div>');
            }

            // 檢查有無傳入資料
            if (!isset($rId)) {
                return new Response ('<div class="alert alert-block alert-danger fade in">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4 class="alert-heading">無傳入資料</h4>
          </div>');
            }
            if ( !is_array( $rId ) ) {
                return new Response ('<div class="alert alert-block alert-danger fade in">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4 class="alert-heading">無傳入資料</h4>
          </div>');
            }
            
            // 移除重複的id
            $rId = array_unique($rId);

            /**
             * 新增一張發票
             * 
             * @var Invoice
             */
            $invoice = new Invoice();
            $invoice
                ->setHasPrint(false)
                ->setSn(md5(time()))
            ;

            $em->persist($invoice);

            // 根據傳入的 rId 迭代
            foreach ($rId as $key => $id) {
                // 取得商品
                $product = $dc->getRepository('WoojinGoodsBundle:GoodsPassport')->find($id);

                if ($product->getStatus()->getId() != Avenue::GS_ONSALE) {
                    continue;
                }

                // 更新商品狀態
                $product->setStatus($productStatus);
                
                $em->persist($product);

                // 計算折扣後的應付金額
                $nOrdersRequired = ($rOrdersRequired[$key] * $nDisRate);

                // 根據應付金額與實付金額決定訂單狀態
                $nOrdersStatusId = (($nOrdersRequired - $rOrdersRequired[$key]) != 0) 
                    ? Avenue::OS_HANDLING 
                    : Avenue::OS_COMPLETE
                ;

                $orderStatus = $em->find('WoojinOrderBundle:OrdersStatus', $nOrdersStatusId);

                // 成立新的訂單
                $order = new Orders();

                // 設定新的訂單資料
                $order
                    ->setGoodsPassport($product)
                    ->setCustom($oCustom)
                    ->setPayType($paytype)
                    ->setKind($orderKind)
                    ->setStatus($orderStatus)
                    ->setRequired($nOrdersRequired)
                    ->setPaid($nOrdersRequired)
                    ->setOrgRequired($nOrdersRequired)
                    ->setOrgPaid($nOrdersRequired)
                    ->setInvoice($invoice)
                ;

                $em->persist($order);
                
                $nTotal += $nOrdersRequired;

                $opeLogger->log($order, $user, $paytype, $nOrdersRequired, '成立' . $orderKind->getName() . '訂單[' . $rOrdersRequired[$key] . '元][' . $paytype->getName() . ']');
                $em->persist($opeLogger->getOpe());

                $sculper->setAfter($opeLogger->getOpe());

                $clue = new AvenueClue;
                $clue->setUser($user)->setContent($sculper->getContent());

                $em->persist($clue);
                $em->flush();

                // 存入rollback陣列
                array_push($rRollback, $order->getId());
                
                if ($product->getYahooId()) {
                    $successProducts[] = $product;
                }
                
                $returnSn.= "<a href=\"" . $this->get('router')->generate('goods_edit_v2', array('id' => $product->getId())) . "?iframe=true&width=100%&height=100%\" rel=\"prettyPhoto[iframes]\">"  
                . $rGoodsSn[$key] 
                . ($product->getColor() ? $product->getColor()->getName() : '')
                .  '</a>,';
            }

            // Yahoo同步刪除
            if (!empty($successProducts)) {
                $adapter = $this->get('yahoo.syncer');
                $adapter->delete($successProducts);
            }

            $em->getConnection()->commit();
        }catch (Exception $e){
            $em->getConnection()->rollback();
            throw $e;
        }   

        if (empty($returnSn)) {
            return new Response( '<div class="alert alert-block alert-warning fade in">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4 class="alert-heading">此次操作無任何商品結帳完成</h4></div>');
        } else {
            return new Response( '<div class="alert alert-block alert-success fade in">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4 class="alert-heading">' . substr($returnSn, 0, -1) . '結帳完成, 合計' . $nTotal . '元</h4>
          <a class="btn btn-primary" href="' . $this->get('router')->generate('invoice_print', array('id' => $invoice->getId(), 'page' => 1)) . '" target="_blank">銷貨單列印</a><button type="button" class="rollback btn btn-inverse" data-roll="'. json_encode( $rRollback ) .'">取消販售</button></div>' );
        }
    }
}
