<?php

namespace Woojin\OrderBundle\Controller\Order;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\UserBundle\Entity\AvenueClue;
use Woojin\OrderBundle\Entity\Orders;

use Woojin\Utility\Avenue\Avenue;

/**
 * Modify controller.
 *
 * @Route("/modify")
 */
class ModifyController extends Controller
{
    /**
     * @Route("/multisale/rollback", name="admin_multisale_rollback", options={"expose"=true})
     * @Method("POST")
     */
    public function rollbackMultiSell (Request $request) 
    {
        $backs = $request->request->get('jRollBack');
        
        $em = $this->getDoctrine()->getManager();

        $gStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE);

        foreach ($backs as $id) {
            $order = $em->find('WoojinOrderBundle:Orders', $id);

            $product = $order->getProduct();
            $product->setStatus($gStatus);
            $em->remove($order);
            $em->persist($product);
        }

        $em->flush();

        return new Response('<div class="alert alert-block alert-success fade in">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4 class="alert-heading">取消銷售成功!</h4></div>');
    }

    /**
     * 完成寄賣訂單確認
     *
     * ========== 流程 ==========
     *
     * #傳入的訂單為寄賣回扣訂單:訂單
     * #取得'訂單'關連商品:商品
     * 
     * '商品'不為本店商品，拋出意外結束
     *
     * '商品'狀態為他店:
     *   ->找出末裔
     *   ->更改末裔成本
     *   ->找出末裔所屬調貨進訂單:調進單
     *   ->'調進單'的應付和已付
     *   ->修改'訂單' 狀態，已付，應付
     *   ->修改'商品'成本
     *     
     * '商品'狀態為售出:
     *   ->修改'訂單' 狀態，已付，應付
     *   ->修改'商品'成本
     * 
     * ========== End ==========
     * 
     * @Route("/feedback/ok/{id}", name="order_feedback_ok", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders")
     * @Method("PUT")
     */
    public function orderAjaxFeedbackOkAction (Request $request, Orders $order) 
    {
        /**
         * 取得'訂單'關連商品
         * 
         * @var \Woojin\GoodsBundle\Entity\GoodsPassports
         */
        $goods = $order->getProduct();

        $opeLogger = $this->get('logger.ope');

        /**
         * 目前登入的使用者
         * 
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.context')->getToken()->getUser();

        // 若'商品'不為本店商品，拋出意外結束
        if ($user->getStore()->getSn() !== substr($goods->getSn(), 0, 1)) {
            throw \Exception('非商品所屬店成員不可同意議價');
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {
            /**
             * 訂單完成狀態
             * 
             * @var \Woojin\OrderBundle\Entity\OrdersStatus
             */
            $status = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE);  
            
            /**
             * 議定價格
             * 
             * @var integer
             */
            $price = $request->request->get('require');

            switch ($goods->getStatus()->getId())
            {
                //'商品'狀態為他店:
                case Avenue::GS_OTHERSTORE:
                    // 找出末裔
                    $last = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(
                        array(
                            'parent' => $goods->getId(),
                            'status' => Avenue::GS_SOLDOUT
                        )
                    );

                    // 更改末裔成本(不更改產編，以利對照)
                    $last->setCost($price);
                    $em->persist($last);

                    // 找出末裔所屬調貨進訂單
                    $moveInOrder = $last->getOrders()->first();
                    $opeLogger->recordOpe($moveInOrder, '議價變更:' . $moveInOrder->getRequired() . '=>' . $price);

                    // '調進單'的應付和已付
                    $moveInOrder
                        ->setRequired($price)
                        ->setPaid($price)
                    ;
                    $em->persist($moveInOrder);

                    // 修改'訂單' 狀態，已付，應付
                    $order
                        ->setStatus($status)
                        ->setRequired($price)
                        ->setPaid($price)
                    ;
                    $em->persist($order);

                    // 修改'商品'成本
                    $goods->setCost($price);
                    $em->persist($goods);

                    break;

                // '商品'狀態為售出:
                case Avenue::GS_SOLDOUT:
                    // 修改'訂單' 狀態，已付，應付
                    $order
                        ->setStatus($status)
                        ->setRequired($price)
                        ->setPaid($price)
                    ;
                    $em->persist($order);

                    // 修改'商品'成本
                    $goods->setCost($price);
                    $em->persist($goods);

                    break;

                default:
                    break;
            }

            $em->flush();
            
            $msg = '完成寄賣回扣訂單:'. $goods->getSn() . '原訂' .  $order->getRequired() . '元，議價為:' . $price . '元';
            $opeLogger->recordOpe($order, $msg);

            $em->getConnection()->commit();

            $this->get('passport.syncer')->sync($goods);

            $session = $this->get('avenue.session')->get();
            $session->getFlashBag()->add('success', $msg);

            return $this->redirect($this->generateUrl('order_consign_done_list'));
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            
            throw $e;
        }    
    }

    /**
     * 將寄賣訂單轉換成進貨訂單
     * 
     * @Route("/inverse_to_purchase/{id}", name="order_inverse_to_purchase", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders")
     * @Method("POST")
     */
    public function inverseToPurchaseAction(Request $request, Orders $order)
    {
        if ($order->getStatus()->getId() == Avenue::OS_CANCEL) {
            throw new \Exception('該訂單已經取消');
        }

        if ($order->getKind()->getId() != Avenue::OK_CONSIGN_IN) {
            throw new \Exception('該訂單非寄賣訂單');
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        $price = $request->request->get('price');

        $sculper = $this->get('sculper.clue');
        $sculper->initConsignToPurchase();

        // Try and make the transaction
        try {
            // 訂單轉換動作歷程記錄
            $this->get('logger.ope')->recordOpe($order, $order->getCustom()->getName() . '-' . $order->getCustom()->getMobil() . '寄賣訂單轉換為店內');
            
            $goods = $order->getProduct();

            $sculper->setBefore($goods);

            $goods
                ->setCustom(null)
                ->setCost($price)
                ->setSn($goods->genSn(substr($goods->getSn(), 0, 1)))
            ;

            $em->persist($goods);
            $order
                ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_IN))
                ->setCustom(null)
                ->setRequired($price)
                ->setPaid($price)
            ;

            $em->persist($order);

            if ($feedBackOrder = $em->getRepository('WoojinOrderBundle:Orders')->findBy(array(
                'goods_passport' => $goods->getId(),
                'kind' => Avenue::OK_FEEDBACK
            ))) {
                $em->remove($feedBackOrder[0]);
            }

            $sculper->setAfter($goods);

            $clue = new AvenueClue;
            $clue
                ->setUser($user)
                ->setContent($sculper->getContent())
            ;
            $em->persist($clue);

            $em->flush();

            $syncer = $this->get('passport.syncer');
            $syncer->sync($goods);

            // Try and commit the transaction
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            // Rollback the failed transaction attempt
            $em->getConnection()->rollback();
            throw $e;
        }

        return new Response('ok');
    }

    /**
     * 還原取消之訂單
     *
     * ========== Flow ===========
     * 
     * ->訂單還原為處理中 | 完成，狀態視應付和實付不同，父訂單也要一起關連處理
     * ->操作記錄添加一筆還原訂單記錄
     *
     * 如果是進貨類
     *      ->商品還原成上架
     *
     * 如果是售出類
     *      非活動售出
     *          ->商品還原成售出
     *      活動售出
     *          ->還原成活動
     *
     * ========== End ============
     * 
     * @Route("/reverseCancel/{id}/", name="order_reverse_cancel", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders", options={"expose"=true})
     * @Method("POST")
     */
    public function reverseCancelAction(Orders $order)
    {
        if ($order->getStatus()->getId() != Avenue::OS_CANCEL) {
            throw new \Exception('該訂單非取消狀態');
        }

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        try {
            // 還原訂單狀態
            $order->setStatus($em->find('WoojinOrderBundle:OrdersStatus', $order->getLogicStatusCode()));
            $em->persist($order);
            $this->get('logger.ope')->recordOpe($order, '訂單取消還原');

            // 若父訂單存在，還原父訂單狀態
            // 
            // ===== 有些沒有綁定，因此還是用硬找法 ====
            // 
            // if ($relates = $order->getRelates()) {
            //  if ($relate = $relates->first()) {
            //      $relate->setStatus($em->find('WoojinOrderBundle:OrdersStatus', $relate->getLogicStatusCode()));
            //      $em->persist($relate);

            //      $this->recordOpe($relate, '訂單取消還原');
            //  }
            // }
            // 
            // ====================================

            /**
             * 關連之商品
             * 
             * @var \Woojin\GoodsBundle\Entity\GoodsPassport
             */
            $goods = $order->getProduct();

            if ($feedBackOrders = $em->getRepository('WoojinOrderBundle:Orders')->findBy(array(
                'goods_passport' => $goods->getId(),
                'kind' => Avenue::OK_FEEDBACK
            ))) {
                $feedBackOrder = $feedBackOrders[0];
                $feedBackOrder->setStatus($em->find('WoojinOrderBundle:OrdersStatus', $feedBackOrder->getLogicStatusCode()));
            }

            // 訂單型別: {0: '進貨類', 2: '售出類'}
            $type = $order->getKind()->getType();

            // 商品狀態還原
            if ($type == Avenue::OKT_OUT) {
                $goods->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_SOLDOUT));
            } else if ($type == Avenue::OKT_IN) {
                $goods->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE));
            }

            $em->flush();

            // Try and commit the transaction
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            // Rollback the failed transaction attempt
            $em->getConnection()->rollback();
            throw $e;
        }

        return new Response('ok');
    }

    /**
     * [進貨|寄賣]訂單轉換綁定客戶
     *
     * ======== 流程 =========
     *
     * 根據手機和所屬店找出客戶實體-> :客戶
     * 
     * 如果訂單為一般進貨:
     *  -> 將該訂單狀態改為寄賣訂單，成本不變。
     *  -> 將訂單客戶關連實體綁定'客戶'。    
     *  -> 新增一筆寄賣回扣訂單關連原本訂單，成本相同，已付為0。
     *  -> 將訂單客戶關連實體綁定'客戶'。    
     *  
     * 如果訂單為寄賣:
     *  -> 將訂單客戶關連實體綁定'客戶'。        
     * 
     * ======== End ==========
     * 
     * @Route("/custom/{id}/mobil/{mobil}", requirements={"id" = "\d+"}, name="orders_v2_custom_alter", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders")
     * @Method("POST")
     */
    public function v2ChangeCustomAction(Request $request, Orders $order, $mobil)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $order->getProduct();

        $user = $this->get('security.context')->getToken()->getUser();

        /**
         * 訂單關聯客戶
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(array(
                'mobil' => $mobil,
                'store' => $this->get('security.context')->getToken()->getUser()->getStore()->getId()
            ))
        ;

        switch ($order->getKind()->getId()) 
        {
            // 如果訂單為一般進貨
            case Avenue::OK_IN:

                $sculper = $this->get('sculper.clue');
                $sculper->initCancelConsignToPurchase();
                $sculper->setBefore($product);

                // 將原本的一般進貨訂單改成寄賣訂單
                $order
                    ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_CONSIGN_IN))
                    // 將訂單客戶關連實體綁定為手機查找到的客戶
                    ->setCustom($custom)
                ;
                
                $product->setCustom($custom);
                $em->persist($product);

                $sculper->setAfter($product);
                
                $clue = new AvenueClue;
                $clue->setUser($user)->setContent($sculper->getContent());

                $em->persist($clue);

                /**
                 * 新增的寄賣回扣訂單
                 * 
                 * @var \Woojin\OrderBundle\Entity\Orders
                 */
                $consignOrder = new Orders;
                $consignOrder
                    // 綁定商品
                    ->setGoodsPassport($order->getProduct())
                    // 付費方式: 現金
                    ->setPayType($em->find('WoojinOrderBundle:PayType', Avenue::PT_CASH))
                    // 應付金額等同於原本的進貨訂單應付金額
                    ->setRequired($order->getRequired())
                    // 已經給客戶的回扣為0
                    ->setPaid(0)
                    // 種類為寄賣回扣
                    ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_FEEDBACK))
                    // 訂單狀態為處理中
                    ->setStatus($em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_HANDLING))
                    // 將訂單客戶關連實體綁定為手機查找到的客戶
                    ->setCustom($custom)
                    // 綁定訂單
                    ->setParent($order)
                ;

                $em->persist($order);
                $em->persist($consignOrder);
                $em->flush($order);

                break;
            // 如果訂單為寄賣
            case Avenue::OK_CONSIGN_IN:
                $oldCustom = $order->getCustom();

                // 將訂單客戶關連實體綁定為手機查找到的客戶
                $order->setCustom($custom);

                $product = $order->getProduct();
                $product->setCustom($custom);
                $em->persist($product);

                foreach ($order->getRelates() as $relate) {
                    $em->persist($relate);
                }

                $em->persist($order);
                $em->flush();

                break;

            case Avenue::OK_OUT:
            case Avenue::OK_EXCHANGE_OUT:
            case Avenue::OK_TURN_OUT:         
            case Avenue::OK_WEB_OUT:       
            case Avenue::OK_SPECIAL_SELL:   
            case Avenue::OK_SAME_BS:
                $order->setCustom($custom);
                $em->persist($order);
                $em->flush();

                break;  

            default:
                break;
        }

        $this->get('passport.syncer')->sync($product);

        return new Response(json_encode(array(
            'status' => '0',
            'custom' => array(
                'name' => $custom->getName(),
                'mobil' => $custom->getMobil()
            )
        )));
    }

    /**
     * @Route("/v2/update/{id}", requirements={"id" = "\d+"}, name="orders_v2_update", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders")
     * @Method("POST")
     */
    public function v2UpdateAction(Request $request, Orders $order)
    {           
        if ($order->getStatus()->getId() !== 1) {
            return new \Exception('訂單非處理中狀態!');
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        try{
            /**
             * 訂單狀態:完成 實體
             *  
             * @var \Woojin\OrderBundle\Entity\OrdersStatus
             */
            $complete = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE);

            /**
             * 付費方式實體
             * 
             * @var \Woojin\OrderBundle\Entity\PayType
             */
            $payType = $em->find('WoojinOrderBundle:PayType', $request->request->get('pay_type', Avenue::PT_CASH));

            /**
             * 本次動作實際支付金額
             * 
             * @var integer
             */
            $paid = $request->request->get('paid');

            /**
             * 本次動作未計算折扣支付金額
             * 
             * @var integer
             */
            $paidOrg = $request->request->get('paid_org');

            /**
             * 今次動作優惠差額
             * 
             * @var integer
             */
            $diff = $paidOrg - $paid;

            /**
             * 目前總共支付金額 = 訂單已經支付 + 本次動作實際支付金額
             * 
             * @var integer
             */
            $totalPaid = $order->getPaid() + $paid;

            /**
             * 目前應付金額 = 訂單應付金額 - 今次動作優惠差額
             * 
             * @var integer
             */
            $required = $order->getRequired() - $diff;

            /**
             * 訂單備註
             * 
             * @var string
             */
            $memo = $request->request->get('memo');

            // 訂單資訊更新
            $order
                ->setPaid($totalPaid)
                ->setOrgPaid((int) $paidOrg + (int) $order->getOrgPaid())
                ->setRequired($required)
                ->setMemo($memo)
            ;

            // 檢查是否已經支付完畢，若為真則將訂單狀態設定為完成狀態
            if ($required <= $totalPaid) {
                $order->setStatus($complete);
            }
                
            $em->persist($order);

            $opeLogger = $this->get('logger.ope');
            $opeLogger->log($order, $user, $payType, (int) $paidOrg, '增加請款:['. $paid . '元][' . $payType->getName() . ']');
            
            $sculper = $this->get('sculper.clue');
            $sculper->initPatch();
            $sculper->setAfter($opeLogger->getOpe());

            $clue = new AvenueClue();
            $clue
                ->setUser($user)
                ->setContent($sculper->getContent())
            ;

            $em->persist($clue);
            $em->persist($opeLogger->getOpe());

            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e){
            $em->getConnection()->rollback();

            throw $e;
        }    
        
        return new Response('ok');
    }

    /**
     * @Route("/v2/cancel/{id}", requirements={"id" = "\d+"}, name="orders_v2_cancel", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders")
     * @Method("POST")
     */
    public function v2CancelAction(Request $request, Orders $order)
    {       
        if ($order->getStatus()->getId() === Avenue::OS_CANCEL) {
            throw new \Exception('訂單已經是取消狀態!');
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $opeLogger = $this->get('logger.ope');

        try{
            $sculper = $this->get('sculper.clue');

            // 取得訂單種類
            $kind = $order->getKind();

            // 根據此訂單取得商品
            $goods = $order->getProduct();

            $isIn = in_array($kind->getId(), array(Avenue::OK_IN, Avenue::OK_CONSIGN_IN, Avenue::OK_MOVE_IN));

            if ($isIn && !($user->getRole()->hasAuth('CANCEL_IN_TYPE_ORDER') && $user->getRole()->hasAuth('CANCEL_ORDER'))) {
                $session->getFlashBag()->add('error', '沒有下架商品權限!');

                throw new \Exception('沒有下架商品權限!');
            }

            // 由訂單種類判斷商品狀態應該是上架或是下架
            // 判斷基準是"是否為寄賣或是進貨"
            $goodsStatus = $em->find('WoojinGoodsBundle:GoodsStatus',   
                    in_array( 
                        $kind->getId(), 
                        array(Avenue::OK_IN, Avenue::OK_CONSIGN_IN, Avenue::OK_MOVE_IN) 
                    ) ? Avenue::GS_OFFSALE : Avenue::GS_ONSALE 
                );

            if (is_object($goods->getActivity())) {
                if (Avenue::ACTIVITY_CHUNGHSIAO_ID === $goods->getActivity()->getId()) {
                    $goodsStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ACTIVITY);
                }
            }

            // 取得訂單取消狀態
            $cancel = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_CANCEL);

            // 設定該訂單狀態為取消
            if (Avenue::OK_MOVE_IN !== $kind->getId()) {
                $order->setStatus($cancel);
                $em->persist($order); 
            }            

            // 設定該商品狀態為前面取得的商品狀態
            $goods->setStatus($goodsStatus);
            $em->persist($goods);

            // 若是寄賣訂單，
            // -> 則找到寄賣回扣訂單
            // -> 一同將其狀態改為取消
            // 
            // *若沒找到寄賣回扣訂單,丟出意外
            if (Avenue::OK_CONSIGN_IN === $kind->getId()) {
                // 根據商品id和訂單種類找到其關連訂單
                $relate = $this
                        ->getDoctrine()
                        ->getRepository('WoojinOrderBundle:Orders')
                        ->findOneBy( 
                            array( 
                                'goods_passport' => $goods->getId(),
                                'kind' => Avenue::OK_FEEDBACK
                            ) 
                        )
                    ;

                // 若沒找到則回應錯誤訊息
                if (!$relate) {
                    return new \Exception('未找到關連訂單!');
                }

                // 改變關連訂單狀態
                $relate->setStatus($cancel);
                
                $em->persist($relate);
                $em->flush();

                $opeLogger->recordOpe($relate, '取消' . $relate->getKind()->getName() . '訂單');
            }

            if ($isIn) {
                $sculper->initCancelPurchaseIn();
                $sculper->setAfter($goods);
            } else {
                $sculper->initCancelSoldOut();
                $sculper->setAfter($order);
            }

            $clue = new AvenueClue;
            $clue
                ->setUser($user)
                ->setContent($sculper->getContent())
            ;

            $em->persist($clue);           
            $em->flush();

            $opeLogger->recordOpe($order, '取消' . $order->getKind()->getName() . '訂單');

            $em->getConnection()->commit();
        }catch (\Exception $e){
            $em->getConnection()->rollback();

            throw $e;
        }    

        return new Response('ok');
    }

    /**
     * @Route("/v2/getback/{id}", requirements={"id" = "\d+"}, name="orders_v2_getback", options={"expose"=true})
     * @ParamConverter("order", class="WoojinOrderBundle:Orders")
     * @Method("PUT")
     */
    public function v2GetBackAction(Request $request, Orders $order) 
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $redirectUrl = $this->generateUrl(
                'goods_edit_v2', 
                array('id' => $order->getProduct()->getId())
            );
            
            $session = $this->get('session');

            $cancel = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_CANCEL);
            $getback = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_GETBACK);

            if (Avenue::OK_CONSIGN_IN !== $order->getKind()->getId()) {
                $session->getFlashBag()->add('error', '此訂單非寄賣訂單!');
                    
                return $this->redirect($redirectUrl);
            }
            
            $product = $order->getProduct();

            if (in_array($product->getStatus()->getId(), array(
                Avenue::GS_SOLDOUT, 
                Avenue::GS_OFFSALE, 
                Avenue::GS_BEHALF, 
                Avenue::GS_GETBACK
            ))) {
                $session->getFlashBag()->add('error', $product->getSn() . '商品不在店內!');
                
                return $this->redirect($redirectUrl);
            }

            // 根據商品id和訂單種類找到其關連訂單
            $relate = $this->getDoctrine()->getRepository('WoojinOrderBundle:Orders')->findOneBy(array( 
                    'goods_passport' => $product->getId(),
                    'kind' => Avenue::OK_FEEDBACK
                ));

            // 若沒找到則回應錯誤訊息
            if (!$relate) {
                $session->getFlashBag()->add('error', $product->getSn() . '未找到寄賣請款訂單!');
                
                return $this->redirect($redirectUrl);
            }

            $order->setStatus($cancel);
            $em->persist($order);

            // 改變關連訂單狀態
            $relate->setStatus($cancel);
            $em->persist($relate);

            $product->setStatus($getback);
            $em->persist($product);

            $em->flush();

            $sculper = $this->get('sculper.clue');
            $sculper->initCustomGetBack();
            $sculper->setAfter($order);

            $clue = new AvenueClue;
            $clue
                ->setUser($user)
                ->setContent($sculper->getContent())
            ;

            $em->persist($clue);

            $session->getFlashBag()->add('success', $product->getSn() . '客戶寄賣取回!');

            if ($product->getYahooId()) {
                $apiClient = $this->get('yahoo.api.client');
                $apiClient->delete($product);

                $session->getFlashBag()->add('success', 'Yahoo商城同步下架刪除完成!');
            }

            $opeLogger = $this->get('logger.ope');
            $opeLogger->recordOpe($relate, '客寄取回');

            $em->getConnection()->commit();
        } catch(\Exception $e) {
            $em->getConnection()->rollback();

            throw $e;
        }

        return $this->redirect($redirectUrl);
    }
}
