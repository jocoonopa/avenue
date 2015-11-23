<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Helper\PaymentHelper;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\OrderBundle\Form\CustomType;
use Woojin\OrderBundle\Form\CustomPasswordType;

class ProfileController extends Controller implements AuthenticatedController, TokenAuthenticatedController
{
    /**
     * 驗證是否已經登入，若尚未登入則返回意外
     */
    public function isValid()
    {   
        $session = $this->get('avenue.session')->get();

        if (!$session->get('custom')) {
            throw new \Exception('請先登入會員');
        }
    }

    /**
     * 會員中心首頁，直接導向到個人資料設定頁
     * 
     * @Route("/profile", name="front_profile_index", options={"expose"=true})
     */
    public function indexAction()
    {
        return $this->redirect($this->get('router')->generate('front_profile_settings'));
    }

    /**
     * 訂單記錄查詢
     * 
     * @Route("/profile/orders", name="front_profile_orders", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function ordersAction()
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();

        // 以下處理將未拋向GC的結帳訂單以script   
        $em = $this->getDoctrine()->getManager();

        $invoices = $em->getRepository('WoojinOrderBundle:Invoice')->findNotYetToGC($custom->getId());
        foreach ($invoices as $invoice) {
            $invoice->setHasPrint(true);
            $em->persist($invoice);
        }

        $em->flush();

        return array(
            '_active' => __FUNCTION__,
            'custom' => $custom,
            'invoices' => $invoices
        );
    }

    /**
     * 願望清單頁面
     * 
     * @Route("/profile/whishlist", name="front_profile_whishlist", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function whishlistAction()
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();

        $whishlist = json_decode($custom->getWhishlist(), true);

        $products = array();

        if (!empty($whishlist)) {
            $em = $this->getDoctrine()->getManager();

            $qb = $em->createQueryBuilder();
            $qb
                ->select('g')
                ->from('WoojinGoodsBundle:GoodsPassport', 'g')
                ->where(
                    $qb->expr()->in('g.id', $whishlist)
                )
            ;

            $products = $qb->getQuery()->getResult();
        }
        
        return array(
            '_active' => __FUNCTION__,
            'products' => $products
        );
    }

    /**
     * 個人資料設定
     * 
     * @Route("/profile/settings", name="front_profile_settings", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function settingsAction()
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();

        $form = $this->createEditForm($custom);

        return array(
            '_active' => __FUNCTION__,
            'edit_form' => $form->createView(),
            'custom' => $custom
        );
    }

    /**
     * 代購記錄
     * 
     * @Route("/profile/behalf", name="front_profile_behalf", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function behalfAction()
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();
        
        return array(
            '_active' => __FUNCTION__,
            'custom' => $custom
        );
    }

    /**
     * @Route("/profile/activeMail", name="front_profile_activeMail_send")
     * @Method("GET")
     */
    public function activeMailSendAction()
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();

        $notifier = $this->get('avenue.notifier');

        $notifier->active($custom);

        $this->get('session')->getFlashBag()->add('success', '帳號驗證信已發送至您的信箱');

        return $this->redirect($this->get('router')->generate('front_profile_settings'));
    }

    /**
     * 個人資料設定
     * 
     * @Route("/profile/passwordModification", name="front_profile_passwordModification", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function passwordModificationAction()
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();

        $editForm = $this->createPasswordForm($custom);

        return array(
            '_active' => __FUNCTION__,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * 個人資料設定
     * 
     * @Route("/profile/passwordModification", name="front_profile_passwordModification_update", options={"expose"=true})
     * @Method("PUT")
     * @Template("WoojinFrontBundle:Profile:passwordModification.html.twig")
     */
    public function passwordModificationUpdateAction(Request $request)
    {
        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->getCustomShortCut();

        $form = $this->createPasswordForm($custom);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($custom);
            $em->flush();

            $session = $this->get('session');

            // set flash messages
            $session->getFlashBag()->add('success', '密碼修改完成');
        }

        return array(
            '_active' => 'passwordModificationAction',
            'edit_form' => $form->createView()
        );
    }

    /**
     * @Route("/profile/custom/update/{id}", name="front_profile_custom_update")
     * @ParamConverter("custom", class="WoojinOrderBundle:Custom")
     * @Method("PUT")
     * @Template("WoojinFrontBundle:Profile:settings.html.twig")
     */
    public function updateAction(Request $request, Custom $custom)
    {
        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createEditForm($custom);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $custom
                ->setCounty($request->request->get('county'))
                ->setDistrict($request->request->get('district'))
            ;
            $em->persist($custom);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', '個人資訊修改完成');

            return $this->redirect($this->generateUrl('front_profile_settings'));
        }

        return array(
            '_active' => 'settings',
            'edit_form' => $editForm->createView(),
            'custom' => $custom
        );
    }

    /**
    * Creates a form to edit a Custom entity.
    *
    * @param Custom $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Custom $entity)
    {
        $form = $this->createFormBuilder($entity, array(
            'action' => $this->generateUrl('front_profile_custom_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr' => array(
                'class' => 'log-reg-block sky-form',
                'id' => 'sky-form4'
            )))
            ->add('name', 'text', array('label' => '姓名'))
            ->add('email', 'email', array('label' => 'Email', 'read_only' => true, 'required' => true))
            ->add('birthday', 'birthday', array(
                'label' => '生日',
                'view_timezone' => 'Asia/Taipei',
                'model_timezone' => 'Asia/Taipei'
            ))
            ->add('sex', 'choice', array('label' => '性別', 'choices' => array(
                '保密' => '保密',
                '先生' => '先生',
                '女士' => '女士'
            )))
            ->add('address', 'text', array('label' => '地址'))
            ->add('password', 'hidden')
            ->getForm();

        return $form;
    }

    /**
     * Creates a form to modify password.
     *
     * @param Custom $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createPasswordForm(Custom $entity)
    {
        $form = $this->createForm(new CustomPasswordType(), $entity, array(
            'action' => $this->generateUrl('front_profile_passwordModification_update'),
            'method' => 'PUT',
            'attr' => array(
                'class' => 'log-reg-block sky-form',
                'id' => 'sky-form4'
            ) 
        ));

        return $form;
    }

    protected function getCustomShortCut()
    {
        $paymentHelper = new PaymentHelper;

        /**
         * Session
         * 
         * @var Session
         */
        $session = $this->get('session');

        $em = $this->getDoctrine()->getManager(); 

        /**
         * 客戶index
         * 
         * @var integer
         */
        $customId = $paymentHelper->getCustomIdFromSession($session);

        /**
         * Custom entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        return $custom = $em->find('WoojinOrderBundle:Custom', $customId);
    }
}
