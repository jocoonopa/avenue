<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\GoodsBundle\Entity\Behalf;

class BehalfController extends Controller implements AuthenticatedController, TokenAuthenticatedController
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
     * @Route("/behalf", name="front_behalf_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $custom = $this->get('session.custom')->current();
        
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('behalf', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $options = array(
            'custom' => $custom,
            'want' => $this->get('facade.product')->find($request->request->get('product_id')),
            'phone' => $request->request->get('phone')
        );

        $this->get('factory.behalf')->create($options);

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $options['want']->getName() . '的代購請求已經發送');

        return $this->redirect($this->generateUrl('front_profile_behalf'));
    }

    /**
     * @Route("/behalf/{id}/bankinfo", requirements={"id"="\d+"}, name="front_behalf_bankinfo", options={"expose"=true})
     * @ParamConverter("behalf", class="WoojinGoodsBundle:Behalf")
     * @Method("PUT")
     */
    public function bankinfoAction(Request $request, Behalf $behalf)
    {
        $options = array(
            'behalf' => $behalf,
            'bankAccount' => $request->request->get('bankAccount'),
            'bankCode' => $request->request->get('bankCode') 
        );

        $this->get('factory.behalf')->nextStep($options);

        $session = $this->get('session');
        $session->getFlashBag()->add('success', '系統已更新' . $options['behalf']->getWant()->getName() . '狀態為款項已支付');
        
        return $this->redirect($this->generateUrl('front_profile_behalf'));
    }

    /**
     * @Route("/behalf/{id}", requirements={"id"="\d+"}, name="front_behalf_delete")
     * @ParamConverter("behalf", class="WoojinGoodsBundle:Behalf")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Behalf $behalf)
    {
        $custom = $this->get('session.custom')->current();
        
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('behalf_cancel', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        if ($behalf->getCustom()->getId() !== $custom->getId()) {
            throw new AccessDeniedHttpException('不可刪除他人的代購請求');
        }

        $options = array('behalf' => $behalf);

        $this->get('factory.behalf')->cancel($options);

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $behalf->getWant()->getName() . '的代購請求已經取消');

        return $this->redirect($this->generateUrl('front_profile_behalf'));
    }
}
