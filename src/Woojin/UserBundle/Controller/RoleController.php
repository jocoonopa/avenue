<?php

namespace Woojin\UserBundle\Controller;

use Woojin\UserBundle\Entity\Role;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RoleController extends Controller
{ 
    /**
    * @Route("/role", name="admin_role_index", options={"expose"=true})
    * @Template("WoojinUserBundle:Role:index.html.twig")
    * @Method("GET")
    */
    public function indexAction()
    {   
        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('WoojinUserBundle:Role')->findBy(array(), array('chmod' => 'DESC'));

        return array('roles' => $roles);
    }

    /**
    * @Route("/role/{id}/edit", requirements={"id"="\d+"}, name="admin_role_edit", options={"expose"=true})
    * @ParamConverter("role", class="WoojinUserBundle:Role")
    * @Template("WoojinUserBundle:Role:edit.html.twig")
    * @Method("GET")
    */
    public function editAction(Role $role)
    {   
        return array(
            'role' => $role,
            'lists' => $this->getList()
        );
    }

    /**
    * @Route("/role/{id}", requirements={"id"="\d+"}, name="admin_role_update", options={"expose"=true})
    * @ParamConverter("role", class="WoojinUserBundle:Role")
    * @Method("PUT")
    */
    public function updateAction(Request $request, Role $role)
    {   
        $em = $this->getDoctrine()->getManager();

        $lists = $this->getList();
        $a = array();

        for ($i = 0; $i <= 53; $i ++) {
            $a[] = null;
        }
                
        foreach ($lists as $key => $list) {
            $a[constant('Woojin\UserBundle\Entity\Role::' . strtoupper($key))] = (int) $request->request->get(strtolower($key), 0);
        }

        $role->setChmod(implode('', $a));

        $em->persist($role);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $role->getName() . '權限修改完成');

        return $this->redirect($this->generateUrl('admin_role_edit', array('id' => $role->getId())));
    }

    /**
    * @Route("/role/new", name="admin_role_new", options={"expose"=true})
    * @Template("WoojinUserBundle:Role:new.html.twig")
    * @Method("GET")
    */
    public function newAction()
    {   
        return array('lists' => $this->getList());
    }

    /**
    * @Route("/role", name="admin_role_add", options={"expose"=true})
    * @Method("POST")
    */
    public function addAction(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();

        $lists = $this->getList();
        $a = array();

        for ($i = 0; $i <= 53; $i ++) {
            $a[] = null;
        }
        
        foreach ($lists as $key => $list) {
            $a[constant('Woojin\UserBundle\Entity\Role::' . strtoupper($key))] = (int) $request->request->get(strtolower($key), 0);
        }

        $role = new Role;
        $role
            ->setName($request->request->get('name', '未命名'))
            ->setRole('ROLE_DEFAULT')
            ->setChmod(implode('', $a))
        ;

        $em->persist($role);
        $em->flush();

        $role->setRole('ROLE_DEFAULT_' . $role->getId());
        $em->persist($role);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $role->getName() . '新增完成');

        return $this->redirect($this->generateUrl('admin_role_index'));
    }

    /**
    * @Route("/role/{id}", requirements={"id"="\d+"}, name="admin_role_delete", options={"expose"=true})
    * @ParamConverter("role", class="WoojinUserBundle:Role")
    * @Method("DELETE")
    */
    public function deleteAction(Role $role)
    {   
        $em = $this->getDoctrine()->getManager();
        $em->remove($role);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $role->getName() . '權限刪除完成');

        return $this->redirect($this->generateUrl('admin_role_index', array('id' => $role->getId())));
    }

    protected function getList()
    {
        return array(
            'BRAND'                         => '編輯品牌',        
            'PATTERN'                       => '編輯款式',        
            'MT'                            => '編輯材質',        
            'LEVEL'                         => '編輯新舊',        
            'SOURCE'                        => '編輯來源',        
            'COLOR'                         => '編輯顏色',        
            'PAYTYPE'                       => '編輯付費方式',       

            'EDIT_WEBPRICE_OWN'             => '編輯本店商品網路售價',        
            'EDIT_WEBPRICE_ALL'             => '編輯所有商品網路售價',        
            'EDIT_PRICE_OWN'                => '編輯本店商品價格',        
            'EDIT_PRICE_ALL'                => '編輯所有店商品價格',
            'EDIT_PRODUCT_OWN'              => '編輯本店商品',
            'EDIT_PRODUCT_ALL'              => '編輯所有商品',
            'READ_COST_OWN'                 => '查看本店商品成本',
            'READ_COST_ALL'                 => '查看所有商品成本',
            'EDIT_COST_OWN'                 => '編輯本店商品成本',
            'EDIT_COST_ALL'                 => '編輯所有商品成本',
            'READ_PRODUCT_OWN'              => '查看本店商品',
            'READ_PRODUCT_ALL'              => '查看所有商品',
            'READ_ORDER_OWN'                => '查看本店訂單',
            'READ_ORDER_ALL'                => '查看所有訂單',
            'CANCEL_ORDER'                  => '取消訂單',
            'CANCEL_IN_TYPE_ORDER'          => '下架商品',
            'EDIT_OPE_DATETIME'             => '編輯操作時間',
            'CONSIGN_TO_PURCHASE'           => '寄賣轉訂單',
            'READ_CUSTOM'                   => '查看客戶資料',
            'EDIT_CUSTOM'                   => '編輯客戶資料',
            'MOVE_REQUEST'                  => '發起調貨請求',
            'MOVE_RESPONSE'                 => '接收調貨請求',
            'PURCHASE'                      => '進貨',
            'SELL'                          => '銷貨',
            'MULTI_SELL'                    => '多筆銷貨',
            'ACTIVITY_SELL'                 => '活動銷貨',
            'ACTIVITY_MANAGE'               => '活動管理',
            'WEB_ORDER_MANAGE'              => '官網訂單管理',
            'BEHALF_MANAGE'                 => '代購管理',
            'HAND_GEN_INVOICE'              => '手KEY建立銷貨憑證',
            'BATCH_UPLOAD'                  => '批次上傳',
            'CHECK_STOCK'                   => '店內盤點',
            'MOVE_RELATE'                   => '調貨相關',
            'CONSIGN_INFORM'                => '寄賣通知',
            'STORE_SETTING'                 => '門市設定',
            'PROMOTION_MANAGE'              => '促銷活動管理',
            'CUSTOM_SN_IMPORT'              => '自定義內碼匯入',
            'TIMELINESS_SETTINGS'           => '搶購活動設定',
            'BENEFIT_REPORT_OWN'            => '本店毛利報表',
            'BENEFIT_REPORT_ALL'            => '所有毛利報表',
            'STOCK_REPORT_OWN'              => '本店庫存報表',
            'STOCK_REPORT_ALL'              => '所有庫存報表',
            'MOVE_REPORT_OWN'               => '本店調貨報表',
            'MOVE_REPORT_ALL'               => '所有調貨報表',
            'USER_SELF_MANAGE'              => '個人帳號管理',
            'USER_OWN_MANAGE'               => '本店帳號管理',
            'USER_ALL_MANAGE'               => '所有帳號管理',
            'EDIT_OTHER_HOLIDAY'            => '修改門市成員假期',
            'ROLE_MANAGE'                   => '權限管理',
            'BENEFIT_MANAGE'                => '購物金管理',
            'EDIT_SEO_SLOGAN'               => '關鍵字管理'
        );
    }
}



