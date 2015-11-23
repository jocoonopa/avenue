<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\OrderBundle\Form\CustomType;
use Woojin\OrderBundle\Form\CustomMailType;
use Woojin\OrderBundle\Form\CustomPasswordType;

use Woojin\Utility\Avenue\Avenue;

/**
 * To-do
 *
 * 1. 現有帳號綁定 FB/Google 功能
 * 2. 現有Custom 欄位更新，增加 Fbtoken, googletoken,
 * api 登入改記錄再token欄位中
 *
 * 流程:
 *
 * 1. 登入FB/Google
 * ->若有token found
 *     -> loginSuccess
 *
 * ->若無token found
 *     ->若有email found result->auto binding && redirect
 *     ->若無 , redirect to register-light-simple page
 *
 * ->create user with email and name, sex, and token
 *
 * 2. 綁定->at register page, binding token
 *
 *
 * routemap:
 *
 * 1. add property for Custom v
 * 2. add token find logic v
 * 3. add token autobind login v
 * 4. add register-light-simple page v
 * 5. add bind token button 
 * 6. add bind token feature
 */
class CustomController extends Controller
{
    /**
     * 1. 根據email 和 密碼找客戶[這邊會需要一個 repository 方法 for 客製搜尋客戶這件事情]
     * 2. 找到後，檢查客戶的狀態 
     * [客戶介面要有一個顯示, 一個編輯]
     *  a. 線上狀態已啟用 -> 正常登入 -> 導向回登入頁面之前的那個頁面，header 載入未結帳購物車
     *  用 $_SERVER['HTTP_REFERER'] 記錄重導頁面
     *  b. 禁止-> 導向停權說明頁
     *  c. 查無此人or密碼錯誤 -> 導向登入頁
     * 
     * @Route("login", name="front_custom_verifyLogin")
     * @Method("POST")
     */
    public function verifyLoginAction(Request $request)
    {
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('custom_login', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $em = $this->getDoctrine()->getManager();
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findByEmailAndPasswordCheck($email, $password);

        $session = $this->get('session'); 

        $redirectUrlList = $this->getRedirectUrlList();

        //c. 查無此人
        if (!$custom) {
            // add flash messages
            $session->getFlashBag()->add('error', '信箱或是密碼有錯誤喔！');

            return $this->redirect($redirectUrlList['fail'], 302);
        }

        // b. 禁止-> 導向停權說明頁
        if ($custom->getIsProhibit()) {
            return $this->redirect($redirectUrlList['prohibit'], 301);
        }

        // a. 線上狀態已啟用 -> 正常登入 -> 導向回登入頁面之前的那個頁面，header 載入未結帳購物車
        if ($custom->isWeb()) { 
            return $this
                ->loginSuccessCallback($session, $custom, $em)
                ->redirect($redirectUrlList['success'])
            ;
        }
    }

    /**
     * @Route(
     *     "/login/behalf/product/{id}", 
     *     name="front_custom_login_behalf", 
     *     options={"expose"=true}
     * )
     * @Method("GET")
     * @Template("WoojinFrontBundle:Custom:login.html.twig")
     */
    public function loginForceRedirectAction(Request $request, $id)
    {
        $session = $this->get('session'); 
        $session->set('http_refer', $this->generateUrl('front_product_show', array('id' => $id)));

        if ($session->get('custom')) {
            return $this->redirect($url);
        }

        return array('routeName' => '');
    }

    /**
     * @Route(
     *     "/login/{routeName}", 
     *     defaults={"routeName"=null}, 
     *     name="front_custom_login", 
     *     options={"expose"=true}
     * )
     * @Method("GET")
     * @Template()
     */
    public function loginAction(Request $request, $routeName)
    {
        $href = ($routeName ? $this->generateUrl($routeName) : $request->server->get('HTTP_REFERER'));
        
        $session = $this->get('session'); 
        $session->set('http_refer', $href);

        if ($session->get('custom')) {
            return $this->redirect($href);
        }

        return array(
            'routeName' => $routeName
        );
    }

    /**
     * @Route("register", name="front_custom_create")
     * @Method("POST")
     * @Template("WoojinFrontBundle:Custom:register.html.twig")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session'); 

        $custom = new Custom();

        $preUrl = $request->request->get('preUrl');
        $county = $request->request->get('county');
        $district = $request->request->get('district');

        $form = $this->createCreateForm($custom);
        $form->handleRequest($request);

        $redirectUrlList = $this->getRedirectUrlList();

        if ($form->isValid()) {
            // 檢查該 email 是否已經註冊，若已經註冊過則導向登入頁
            if ($em->getRepository('WoojinOrderBundle:Custom')->findByEmailFromWebsite($custom->getEmail())) {
                $session->getFlashBag()->add('error', '您的電子郵件信箱已經註冊過囉，請由此登入！');

                return $this->redirect($redirectUrlList['fail'], 302); 
            }

            $custom
                ->setCounty($county)
                ->setDistrict($district)
                ->setAddress($custom->getAddress())
                ->setStore($em->find('WoojinStoreBundle:Store', Avenue::STORE_WEBSITE))
            ;
            
            $em->persist($custom);
            $em->flush();

            $session->set('isAgree', false);

            // 發送註冊成功通知信
            $this->get('avenue.notifier')->register($custom);
            
            return $this
                ->loginSuccessCallback($session, $custom, $em)
                ->redirect($redirectUrlList['success'])
            ;
        }
       
        $session->set('isAgree', true);

        return array(
            'custom' => $custom,
            'county' => $county,
            'district' => $district,
            'form' => $form->createView(),
            'isFb' => $request->request->get('isFb')
        );
    }

    /**
     * 透過token 取得 fb 的 uid 驗證登入。
     * 1. 如果 email 存在資料庫，則表示登入成功。
     * 2. 如果 email 不存在，則導入註冊頁。
     *
     * @Route("fblogin", name="front_custom_verifyFblogin", options={"expose"=true})
     * @Method("GET")
     */
    public function verifyFbloginAction(Request $request)
    {
        $session = $this->get('session');

        $redirectUrlList = $this->getRedirectUrlList();
        
        $fbParam = $this->container->getParameter('fb');

        $fb = new \Facebook\Facebook([
            'app_id' => $fbParam['app_id'],
            'app_secret' => $fbParam['app_secret'],
            'default_graph_version' => $fbParam['api_version'],
        ]);

        $helper = $fb->getRedirectLoginHelper();
        try {
          $accessToken = $helper->getAccessToken();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // add flash messages
            $session->getFlashBag()->add('error', 'Facebook 驗證發生錯誤' . $e->getMessage());

            return $this->redirect($redirectUrlList['fail'], 302);
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // add flash messages
            $session->getFlashBag()->add('error', 'Facebook 驗證發生錯誤' . $e->getMessage());

            return $this->redirect($redirectUrlList['fail'], 302);
        }

        if (isset($accessToken)) {
            if (!$accessToken->isLongLived()) {
                // Exchanges a short-lived access token for a long-lived one
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                    exit;
                }
            }
            // Logged in
            $fb->setDefaultAccessToken($accessToken);

            try {
                $response = $fb->get('/me', $accessToken);

                $userNode = $response->getGraphUser();
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                $session->getFlashBag()->add('error', 'Graph returned an error: ' . $e->getMessage());

                return $this->redirect($this->get('router')->generate('front_custom_create'));
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                $session->getFlashBag()->add('error', 'Facebook SDK returned an error: ' . $e->getMessage());

                return $this->redirect($this->get('router')->generate('front_custom_create'));
            }
        }

        $em = $this->getDoctrine()->getManager();

        //$custom = $em->getRepository('WoojinOrderBundle:Custom')->findByEmailFromWebsite($userNode['email']);

        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findByFaceBookUserNode($userNode);

        if ($custom && NULL === $custom->getFbToken()) {
            $custom->setFbToken($userNode['id']);

            $em->persist($custom);
            $em->flush();
        }

        if (!$custom) {
            // 這邊應該是導向一個輸入email的 Form 頁面
            if (!isset($userNode['email'])) {
                $session->set('userNode', $userNode);

                return $this->redirect($this->get('router')->generate('front_custom_light_simple'));
            }

            $custom = new Custom;
            $custom->setStore($em->find('WoojinStoreBundle:Store', Avenue::STORE_WEBSITE));
            $custom->handleFbResponse($userNode);
            
            $em->persist($custom);
            $em->flush();

            $this->get('avenue.notifier')->register($custom);
        }

        // 客戶若原本就存在則進行登入成功流程
        // 若客戶不存在則導向註冊頁
        return $this
            ->loginSuccessCallback($session, $custom, $em)
            ->redirect($redirectUrlList['success'])
        ; 
    }

    /**
     * Displays a form to create a new Custom entity.
     *
     * @Route("register_light_simple", name="front_custom_light_simple")
     * @Method("GET")
     * @Template()
     */
    public function lightSimpleAction(Request $request)
    {
        $custom = new Custom();
        $form   = $this->createLightSimpleForm($custom);

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * New Custom 
     *
     * @Route("register_light_simple", name="front_custom_light_simple_create")
     * @Method("POST")
     * @Template("WoojinFrontBundle:Custom:lightSimple.html.twig")
     */
    public function lightSimpleCreateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $redirectUrlList = $this->getRedirectUrlList();

        $custom = new Custom();
        
        $form = $this->createLightSimpleForm($custom);
        $form->handleRequest($request);
        
        $email = $custom->getEmail();

        if ($form->isValid()) {
            $userNode = $session->get('userNode');
                
            if (NULL === $userNode) {
                return $this->redirect($this->get('router')->generate('front_custom_login'));
            }

            // 檢查該 email 是否已經註冊，若已經註冊則更新其email
            if ($existCustom = $em->getRepository('WoojinOrderBundle:Custom')->findByEmailFromWebsite($email)) {
                $custom = $existCustom;
                $custom->setFbToken($userNode['id']);
            } else {
                $custom = new Custom;
                $custom->setStore($em->find('WoojinStoreBundle:Store', Avenue::STORE_WEBSITE));

                $session = $this->get('session');

                $userNode['email'] = $email;
                $custom->handleFbResponse($userNode);
                
                $session->remove('userNode');
            }
            
            $em->persist($custom);
            $em->flush();

            $this->get('avenue.notifier')->register($custom);
            
            return $this
                ->loginSuccessCallback($session, $custom, $em)
                ->redirect($redirectUrlList['success'])
            ;
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to create a new Custom entity.
     *
     * @Route("register", name="front_custom_register")
     * @Method("GET")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $custom = new Custom();
        $form   = $this->createCreateForm($custom);

        return array(
            'district' => null,
            'county' => null,
            'custom' => $custom,
            'form' => $form->createView(),
            'isFb' => $request->request->get('isFb')
        );
    }

    /**
     * @Route("logout", name="front_custom_logout")
     */
    public function logoutAction()
    {
        $session = $this->get('session'); 

        $session->clear();

        return $this->redirect($this->get('router')->generate('front_index'));
    }

    /**
     * @Route("passwordMail", name="front_custom_passwordMail")
     * @Method("GET")
     * @Template()
     */
    public function passwordMailAction()
    {
        $data = array();

        $form = $this->createPasswordMailForm(array());

        return array('edit_form' => $form->createView());
    }

    /**
     * @Route("passwordMail", name="front_passwordMail_send")
     * @Method("POST")
     * @Template("WoojinFrontBundle:Custom:passwordMail.html.twig")
     */
    public function passwordResetSendAction(Request $request)
    {
        $notifier = $this->get('avenue.notifier');

        $editForm = $this->createPasswordMailForm(array());
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $data = $editForm->getData();

            $email = $data['email'];

            $em = $this->getDoctrine()->getManager();

            /**
             * Custom entity
             * 
             * @var \Woojin\OrderBundle\Entity\Custom
             */
            $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(array('email' => $email, 'store' => Avenue::STORE_WEBSITE));

            if (!$custom) {
                $this->get('session')->getFlashBag()->add('error', '客戶信箱不存在! 請註冊新的帳號!');

                return $this->redirect($this->get('router')->generate('front_custom_create'));
            }
            
            // 發送信件
            $notifier->forgot($custom);

            $this->get('session')->getFlashBag()->add('success', '進一步指示已傳送至電子郵件 ' . $custom->getEmail());

            return $this->redirect($this->get('router')->generate('front_custom_passwordMail'));
        }

        return array('edit_form' => $editForm->createView());
    }

    /**
     * 忘記密碼
     * 
     * @Route("forgot/{activeKey}/{email}", name="front_custom_forgot_nosession")
     * @Method("GET")
     * @Template("WoojinFrontBundle:Custom:passwordModification.html.twig")
     */
    public function forgotAction($activeKey, $email)
    {
        $mobileDetector = $this->get('resolver.device');

        if ($mobileDetector->isM()) {
            return $this->redirect(
                $this->generateUrl('mobile_front_custom_forgot_nosession', array(
                    'activeKey' => $activeKey,
                    'email' => $email
                ))
            );
        }

        $em = $this->getDoctrine()->getManager();
        
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(array(
            'activeKey' => $activeKey,
            'email' => $email
        ));

        if (!$custom) {
            throw $this->createNotFoundException('用戶不存在或是連結失效!');
        }

        $form = $this->createPasswordForm($custom);

        return array('edit_form' => $form->createView());
    }

    /**
     * 忘記密碼
     * 
     * @Route("forgot/{activeKey}/{email}", name="front_custom_password_update")
     * @Method("PUT")
     * @Template("WoojinFrontBundle:Custom:passwordModification.html.twig")
     */
    public function updatePasswordAction(Request $request, $activeKey, $email)
    {
        $em = $this->getDoctrine()->getManager();
        
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(array(
            'activeKey' => $activeKey,
            'email' => $email
        ));

        if (!$custom) {
            throw $this->createNotFoundException('用戶不存在或是連結失效!');
        }

        $editForm = $this->createPasswordForm($custom);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $custom->setActiveKey($custom->genActiveKey());
            $em->persist($custom);
            $em->flush();

            // 完成登入動作
            $session = $this->get('session');
            $this->loginSuccessCallback($session, $custom, $em);
            
            $session->getFlashBag()->add('success', '密碼修改完成!');

            return $this->redirect($this->get('router')->generate('front_profile_settings'));
        }

        return array('edit_form' => $editForm->createView());
    }

    /**
     * 激活帳號
     * 
     * @Route("active/{activeKey}/{email}", name="front_custom_active")
     * @Method("GET")
     * @Template()
     */
    public function activeAction($activeKey, $email)
    {
        $em = $this->getDoctrine()->getManager();

        //檢查該 email 的官網用戶是否存在
        if (!$custom = $em->getRepository('WoojinOrderBundle:Custom')->findByEmailFromWebsite($email)) {
            return $this->createNotFoundException('用戶不存在!');
        }

        // 導向會員中心，重新請求發送驗證信
        if ($custom->getActiveKey() !== $activeKey) {
            return new Response('驗證失敗！');
        }

        // 設置屬性為激活狀態
        $custom
            ->setIsActive(true)
            ->setActiveKey($custom->genActiveKey())
        ;
        $em->persist($custom);
        $em->flush();

        // 完成登入動作
        $session = $this->get('session');
        $session->getFlashBag()->add('success', '帳號驗證完成!');

        $this->loginSuccessCallback($session, $custom, $em);

        // 導向會員中心，顯示激活成功訊息
        return array(
            '_active' => null,
            'custom' => $custom
        );
    }

    /**
     * Creates a form to modify password.
     *
     * @param Custom $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createPasswordMailForm(array $data)
    {
        $form = $this->createForm(new CustomMailType(), $data, array(
            'action' => $this->generateUrl('front_passwordMail_send'),
            'method' => 'POST',
            'attr' => array(
                'class' => 'log-reg-block sky-form',
                'id' => 'sky-form4'
            ) 
        ));

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
            'action' => $this->generateUrl('front_custom_password_update', 
                array(
                    'activeKey' => $entity->getActiveKey(),
                    'email' => $entity->getEmail()
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'class' => 'log-reg-block sky-form',
                'id' => 'sky-form4'
            ) 
        ));

        return $form;
    }

    /**
     * Creates a form to create a Custom entity.
     *
     * @param Custom $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Custom $entity)
    {
        $form = $this->createForm(new CustomType(), $entity, array(
            'action' => $this->get('router')->generate('front_custom_create'),
            'method' => 'POST',
            'attr' => array(
                'class' => 'log-reg-block sky-form',
                'id' => 'sky-form4'
            ) 
        ));

        return $form;
    }

    private function createLightSimpleForm(Custom $entity)
    {
        $form = $this->createForm(new CustomMailType(), $entity, array(
            'action' => $this->get('router')->generate('front_custom_light_simple'),
            'method' => 'POST',
            'data_class' => "Woojin\OrderBundle\Entity\Custom",
            'attr' => array(
                'class' => 'log-reg-block sky-form',
                'id' => 'sky-form4'
            ) 
        ));

        return $form;
    }

    /**
     * 登入成功後的回呼動作
     * 
     * @param  Session $session 
     * @param  Custom  $custom 
     * @param  Entity Manager $em
     * @return $this           
     */
    private function loginSuccessCallback(Session $session, Custom $custom, $em)
    {
        $custom->setCsrf('avenue2003');
        $custom->setPreCsrf($custom->getCsrf());
        $em->persist($custom);
        $em->flush();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $session->set('custom', $serializer->serialize($custom, 'json'));
        $session->set('avenue_token', $custom->getCsrf());

        return $this;
    }

    private function getReferUrl(Session $session)
    {
        $url = $session->get('http_refer');

        $arr = array('/product', '/payment', '/promotion', '/jewels', '/all', '/category', '/text');
        
        foreach ($arr as $string) {
            if (strpos($url, $string) !== false) {
                return $url;
            }
        }

        return $this->generateUrl('front_index');
    }

    protected function getRedirectUrlList()
    {
        $mobileDetector = $this->get('resolver.device');
        $session = $this->get('session');
        $loginUrl = $this->generateUrl('front_custom_login');
        $mobileLoginUrl = $this->generateUrl('mobile_front_login');

        return $redirectUrlList = ($mobileDetector->isM()) 
            ? array(
                'fail' => $mobileLoginUrl,
                'prohibit' => $mobileLoginUrl,
                'success' => $this->generateUrl('mobile_front_payment')
            ) 
            : array(
                'fail' => $loginUrl,
                'prohibit' => $loginUrl,
                'success' => $this->getReferUrl($session)
            );
    }
}
