<?php

namespace Woojin\GoodsBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Ope;
use Woojin\GoodsBundle\Entity\Move;
use Woojin\GoodsBundle\Form\MoveType;
use Woojin\Utility\Avenue\Avenue;

/**
 * Move controller.
 *
 * @Route("/move/v2")
 */
class MoveController extends Controller
{
    const PERPAGE = 100;

    /**
     * Lists all Move entities.
     *
     * @Route("/list/{page}", requirements={"id"="\d+"}, defaults={"page"="1"}, name="move")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page)
    {
        $user = $this->get('security.context')->getToken()->getUser(); 

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb->select(array('m, s, g, b, t, f'))
            ->from('WoojinGoodsBundle:Move', 'm')
            ->leftjoin('m.status', 's')
            ->leftjoin('m.orgGoods', 'g')
            ->leftjoin('g.brand', 'b')
            ->leftjoin('m.thrower', 't')
            ->leftjoin('m.from', 'f')
        ;

        if (!$user->getRole()->hasAuth('MOVE_REPORT_ALL')) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('m.from', $user->getStore()->getId()),
                $qb->expr()->eq('m.destination', $user->getStore()->getId())
            ));
        }
        
        $query = $qb->orderBy('m.id', 'DESC')
            ->setFirstResult(self::PERPAGE *($page - 1))
            ->setMaxResults(self::PERPAGE)
        ;

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        return array(
            'entities' => $paginator,
            'count' => $count,
            'currentPage' => $page,
            'pageNum' => ceil($count/self::PERPAGE)
        );
    }
    /**
     * Creates a new Move entity.
     *
     * @Route("", name="move_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:Move:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Move();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('move_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Move entity.
     *
     * @param Move $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Move $entity)
    {
        $form = $this->createForm(new MoveType(), $entity, array(
            'action' => $this->generateUrl('move_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Move entity.
     *
     * @Route("/new", name="move_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Move();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Move entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="move_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Move')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Move entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Move entity.
     *
     * @Route("/{id}/edit", name="move_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Move')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Move entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Move entity.
    *
    * @param Move $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Move $entity)
    {
        $form = $this->createForm(new MoveType(), $entity, array(
            'action' => $this->generateUrl('move_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Move entity.
     *
     * @Route("/{id}", name="move_update")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:Move:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Move')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Move entity.');
        }
        
        $post = $request->request->get('woojin_goodsbundle_move');
        $entity->setMemo($post['memo']);
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('move_edit', array('id' => $id)));
    }
    /**
     * Deletes a Move entity.
     *
     * @Route("/{id}", name="move_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinGoodsBundle:Move')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Move entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('move'));
    }

    /**
     * Creates a form to delete a Move entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('move_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * 發請調出貨請求
     * 
     * @Route("/backOrder/request", name="order_backOrder_request", options={"expose"=true})
     */
    public function backOrderRequestAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $move = new Move();

        $user = $this->get('security.context')->getToken()->getUser();

        $from = $user->getStore();

        $goods = $em->find('WoojinGoodsBundle:GoodsPassport', $request->request->get('nGoodsPassportId'));

        $destination = $em->getRepository('WoojinStoreBundle:Store')->findOneBy(array('sn' => $request->request->get('sStoreSn')));

        if ($from->getId() === $destination->getId()) {
            return new \Exception('調出貨目的不可為所在店!');
        }

        if (!$destination) {
            return new \Exception('店碼輸入錯誤!');
        }

        $move
            ->setOrgGoods($goods)
            ->setCreater($user)
            ->setFrom($from)
            ->setDestination($destination)
            ->setThrower($user)
            ->setStatus($em->find('WoojinGoodsBundle:MoveStatus', Avenue::MV_CONFIRM))
        ;

        $goods->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_MOVING));

        $em->persist($move);
        $em->flush();

        return new Response('ok');
    }

    /**
     * 同意調出貨請求
     * 
     * @Route("/backOrder/{id}/agree", name="order_backOrder_agree", options={"expose"=true})
     * @ParamConverter("move", class="WoojinGoodsBundle:Move")
     * @Method("GET")
     */
    public function backOrderAgreeAction (Request $request, $move) 
    {
        if (!$move->isModifyble()) {
            return new \Exception("請求已經為終止狀態{$move}，不可更改!");
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $move
            ->setStatus($em->find('WoojinGoodsBundle:MoveStatus', Avenue::MV_CONFIRM))
            ->setThrower($user)
        ;

        $em->persist($move);
        $em->flush();

        return new Response('ok');
    }

    /**
     * 調出貨請求確認
     * 
     * @Route("/backOrder/{id}/confirm", name="order_backOrder_confirm", options={"expose"=true})
     * @ParamConverter("move", class="WoojinGoodsBundle:Move")
     * @Method("GET")
     */
    public function backOrderConfirmAction (Request $request, Move $move) 
    {            
        if (!$move->isModifyble()) {
            return new \Exception("請求已經為終止狀態{$move}，不可更改!");
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $session = $this->get('session');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $em->getConnection()->beginTransaction();

        try {
            $oGoodsStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_OTHERSTORE);
            $oGoodsStatusIn = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE);

            // 從調貨請求出抓出被調出的商品
            $product = $move->getOrgGoods();

            // 將商品狀態設定為他店
            $product->setStatus($oGoodsStatus);

            // 尋找是否已經存在屬於該店的護照(產編字首和商品指紋)
            $qb 
                ->select('gd')
                ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
                ->where( 
                    $qb->expr()->andX(
                        $qb->expr()->eq('SUBSTRING(gd.sn, 1, 1)', $qb->expr()->literal($user->getStore()->getSn())),
                        $qb->expr()->eq('gd.parent', $product->getParent()->getId())
                    )
                )
            ;

            // Query 結果為調進貨的商品，若不存在該商品則反為 NULL
            $newProducts = $qb->getQuery()->getResult();

            // 如果為空則建立新護照, 直接將原本護照的內容copy到新護照上
            if (empty($newProducts)) {
                $productFactory = $this->get('factory.product');

                // 複製調出商品實體
                $productFactory->_clone($product);
                
                // 從工廠提取複製品
                $newProduct = $productFactory->getProduct();

                // 改變複製品的產編和狀態(上架)
                $newProduct
                    ->setSn($newProduct->genSn($user->getStore()->getSn()))
                    ->setStatus($oGoodsStatusIn)
                ;

                $em->persist($newProduct);
            } else {// 若不為空則直接使用取得的舊護照
                /* 
                 * 商品回國將狀態改為國內( 上架 ), 
                 * 寄賣金額改為0 表示不用做二段通知 
                 */
                
                // 取得護照
                $newProduct = $newProducts[0];

                // 將商品狀態改為上架
                $newProduct->setStatus($oGoodsStatusIn);
                $em->persist($newProduct);

                // 取得該護照的所有訂單記錄
                $newOrders = $newProduct->getOrders();

                // 依照訂單記錄進行迭代
                foreach ($newOrders as $key => $order) {
                    if (Avenue::OK_FEEDBACK === $order->getId()) {
                        // 將寄賣金額從 -1 改為 0
                        $order->setPaid(0);
                        $em->persist($order);
                        
                        break;
                    }
                }
            }       

            $move
                ->setStatus($em->find('WoojinGoodsBundle:MoveStatus', Avenue::MV_COMPLETE))
                ->setNewGoods($newProduct)
                ->setCatcher($user)
            ;
            
            $em->persist($move);

            $complete = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE);
            $cashType = $em->find('WoojinOrderBundle:PayType', Avenue::PT_CASH);

            $ordersMoveOut = new Orders;
            $ordersMoveOut
                ->setStatus($complete)
                ->setPaid($product->getCost())
                ->setRequired($product->getCost())
                ->setPayType($cashType)
                ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_MOVE_OUT))
                ->setGoodsPassport($product)
            ; 

            $ordersMoveIn = new Orders;
            $ordersMoveIn
                ->setStatus($complete)
                ->setPaid($product->getCost())
                ->setRequired($product->getCost())
                ->setPayType($cashType)
                ->setKind($em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_MOVE_IN))
                ->setGoodsPassport($newProduct)
            ; 

            $ordersMoveIn->addRelate($ordersMoveOut);
            $ordersMoveOut->setParent($ordersMoveIn);

            $em->persist($ordersMoveIn);
            $em->persist($ordersMoveOut);

            // 記錄調貨進操作記錄
            $opeIn = new Ope;
            $opeOut = new Ope;

            $opeIn
                ->setUser($move->getCatcher())
                ->setDatetime(new \DateTime(date('Y-m-d H:i:s')))
                ->setOrders($ordersMoveIn)
                ->setAct('成立調進貨訂單')
            ;

            $opeOut
                ->setUser($move->getThrower())
                ->setDatetime(new \DateTime(date('Y-m-d H:i:s')))
                ->setOrders($ordersMoveOut)
                ->setAct('成立調出貨訂單')
            ;

            $newProduct->setSn($newProduct->genSn($user->getStore()->getSn()));

            $em->persist($opeIn);
            $em->persist($opeOut);
            $em->persist($newProduct);
            $em->flush();
            
            $em->getConnection()->commit();

            $session->getFlashBag()->add('success', $product->getSn() . ' -> ' . $newProduct->getSn());
        } catch (Exception $e){
            $em->getConnection()->rollback();
            
            throw $e;
        } 

        // 若已經上傳至yahoo, 
        if ($newProduct->getYahooId()) {
            $apiClient = $this->get('yahoo.api.client');
            $apiClient->updateMain(
                $apiClient->getProvider()->genR($newProduct)->getR()
            );

            $session->getFlashBag()->add('success', 'Yahoo商城同步更新完成');

            $product->setYahooId(NULL);
            $em->persist($product);
            $em->flush();
        } else {
            $session->getFlashBag()->add('error', 'Yahoo商城同步未進行');
        }

        return $this->redirect($this->generateUrl('move'));
    }

    /**
     * 取消調出貨請求
     * 
     * @Route("/backOrder/{id}/cancel", name="order_backOrder_cancel", options={"expose"=true})
     * @ParamConverter("move", class="WoojinGoodsBundle:Move")
     * @Method("GET")
     */
    public function backOrderCancelAction(Request $request, $move)
    {
        if (!$move->isModifyble()) {
            return new \Exception("請求已經為終止狀態{$move}，不可更改!");
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $move
            ->setCloser($user)
            ->setStatus($em->find('WoojinGoodsBundle:MoveStatus', Avenue::OK_MOVE_IN))
        ;

        $goods = $move->getOrgGoods();

        $goods->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE));

        $em->persist($goods);
        $em->persist($move);
        $em->flush();

        return $this->redirect($this->generateUrl('move'));
    }

    /**
     * 拒絕調出貨請求
     * 
     * @Route("/backOrder/{id}/reject", name="order_backOrder_reject", options={"expose"=true})
     * @ParamConverter("move", class="WoojinGoodsBundle:Move")
     * @Method("GET")
     */
    public function backOrderRejectAction(Request $request, $move)
    {
        if (!$move->isModifyble()) {
            return new \Exception("請求已經為終止狀態{$move}，不可更改!");
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $move
            ->setCloser($user)
            ->setStatus($em->find('WoojinGoodsBundle:MoveStatus', Avenue::MV_REJECT))
        ;

        $goods = $move->getOrgGoods();

        $goods->setStatus($em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE));

        $em->persist($goods);
        $em->persist($move);
        $em->flush();

        return $this->redirect($this->generateUrl('move'));
    }
}
