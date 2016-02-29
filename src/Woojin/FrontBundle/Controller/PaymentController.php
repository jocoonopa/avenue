<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\AllPay\AllInOne;
use Woojin\Utility\Handler\ResponseHandler;
use Woojin\Utility\Helper\PaymentHelper;

use Avenue\Adapter\Adapter;

/**
 * ----------------------------- 主要的流程 -------------------------------------
 * 
 * (1)選擇使用歐付寶付款[front_payment_checkout]
 * (2)訂單產生[front_payment_generate]  
 * (3)將訂單傳給歐付寶處理[front_payment_passAllpay]
 * (4)付款結果通知 + 對訂單進行處理[front_payment_return]
 *
 * -----------------------------------------------------------------------------
 */
class PaymentController extends Controller implements AuthenticatedController
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

        $custom = json_decode($session->get('custom'));

        if ($custom->is_active == 0) {
            throw new \Exception('帳號尚未通過認證，請完成帳號認證步驟!');
        }

        if (!$this->get('security.context')->getToken()->getUser()) {
             throw new \Exception('建構中尚未開放!');
        }
    }

    /**
     * (1)選擇使用歐付寶付款
     * 購物車結帳頁面，選擇歐付寶付款前往歐付寶頁面
     * 
     * @Route("/payment/checkout", name="front_payment_checkout", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function checkoutAction(Request $request)
    {
        $mobileDetector = $this->get('resolver.device');

        if ($mobileDetector->isM()) {
            return $this->redirect($this->generateUrl('mobile_front_cart'));
        }
        
        $paymentHelper = new PaymentHelper();

        $em = $this->getDoctrine()->getManager();

        /**
         * 購物車存放的商品索引陣列
         * 
         * @var array
         */
        $ids = json_decode($request->cookies->get('avenueCart', '[]'));

        /**
         * 購物車商品陣列
         * 
         * @var array
         */
        $products = (!empty($ids)) 
            ? $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findByIds($ids)
            : array()
        ;
        
        /**
         * 購物車總金額
         * 
         * @var integer
         */
        $total = $paymentHelper->getCartTotalCost($products);

        /**
         * Symfony session object
         * 
         * @var \Symfony\Component\HttpFoundation\Session\Session;
         */
        $session = $this->get('avenue.session')->get();

        /**
         * Custom object
         * 
         * @var \stdClass
         */
        $custom = json_decode($session->get('custom'), true);

        return array(
            'custom' => $custom,
            'products' => $products,
            'total' => $total 
        );
    }

    /**
     * 訂單派送頁面，包含有購物車派送明細(可派送與不可派送的表單)，清除購物車
     * 
     * ------------------------ 產生訂單流程 --------------------------
     *
     * ->建立發票
     * 
     * ->迭代商品
     *       建立訂單
     *
     * ---------------------------------------------------------------
     * 
     * @Route("/payment/generate", name="front_payment_generate", options={"expose"=true})
     * @Method("POST")
     */
    public function generateAction(Request $request)
    {
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('invoice', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $paymentHelper = new PaymentHelper();

        /**
         * 商品的索引陣列
         * 
         * @var array
         */
        $ids = json_decode($request->cookies->get('avenueCart'));

        if (empty($ids)) {
            return $this->redirect($this->get('router')->generate('front_payment_checkout'));
        }

        /**
         * Entity Manager
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * 商品實體陣列
         * 
         * @var array{\Woojin\GoodsBundle\Entity\GoodsPassport}
         */
        $products = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findByIds($ids);

        if (empty($products)) {
            $this->redirect($this->get('router')->generate('front_payment_checkout'));
        }

        /**
         * 商品狀態不合法因此無法加入訂單的商品陣列
         * 
         * @var array{\Woojin\GoodsBundle\Entity\GoodsPassport}
         */
        $failedProducts = array();

        $paymentHelper->dropFail($products, $failedProducts);

        /**
         * Session
         * 
         * @var Session
         */
        $session = $this->get('session'); 

        /**
         * 客戶index
         * 
         * @var integer
         */
        $customId = $paymentHelper->getCustomIdFromSession($session);

        /**
         * Custom
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        if (!($custom = $paymentHelper->getValidCustom($em, $request, $customId))) {
            return $this->redirect($this->get('router')->generate('front_payment_checkout'));
        }
    
        /**
         * 此元件將會自動處理整個販售流程
         * 
         * @var \Woojin\Utility\Que\Sell\Seller
         */
        $seller = $this->get('seller');

        /**
         * 發票
         * 
         * @var \Woojin\OrderBundle\Entity\Invoice
         */
        $invoice = !empty($products) ? $seller->sell($custom, $products, $request) : null;

        $creditInstallment = $request->request->get('creditInstallment', 0);

        if ($creditInstallment > 0) {
            $invoice->setCreditInstallment($creditInstallment);
        }

        $em->persist($invoice);
        $em->flush();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $session->set('custom', $serializer->serialize($custom, 'json'));
        // $session->set('avenue_token', $custom->getCsrf());
        
        // 這邊不直接使用 Template() 而改用 Response render是因為要使用 response 物件的 clear Cookie 方法。
        // generate.html.twig 其實只是一個中繼頁面，該頁面讀取完畢後會自動 submit form 前往 'front_payment_passAllpay   '
        $engine = $this->container->get('templating');
        
        $content = $engine->render('WoojinFrontBundle:Payment:generate.html.twig', array('invoice' => $invoice));

        /**
         * Response
         * 
         * @var Symfony\Component\HttpFoundation\Response
         */
        $response = new Response($content);

        $response->headers->clearCookie('avenueCart');

        return $response;
    }

    /**
     * 將訂單送交給歐付寶處理(新開視窗)
     * 
     * @Route("/payment/allpay", name="front_payment_passAllpay", options={"expose"=true})
     * @Method("POST")
     */
    public function passAllPayAction(Request $request)
    {        
        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('invoice', $request->request->get('avenue_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $paymentHelper = new PaymentHelper();

        /**
         * Entity Manager
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * Session
         * 
         * @var Session
         */
        $session = $this->get('session'); 

        /**
         * 訂單總金額
         * 
         * @var integer
         */
        $price = 0;

        /**
         * Items to AllPay
         * 
         * @var array
         */
        $items = array();

        /**
         * 客戶index
         * 
         * @var integer
         */
        $customId = $paymentHelper->getCustomIdFromSession($session);

        $invoiceId = $request->request->get('id');

        /**
         * Custom
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        if (!($custom = $paymentHelper->getValidCustom($em, $request, $customId))) {
            return $this->redirect($this->get('router')->generate('front_payment_checkout'));
        }

        /**
         * Invoice
         * 
         * @var \Woojin\OrderBundle\Entity\Invoice
         */
        $invoice = $paymentHelper->getValidInvoice($em, $invoiceId, $customId);
        $invoice->shiftSn();
        $em->persist($invoice);
        $em->flush();

        $logger = $this->get('logger.custom');
        $logger->write($invoice->getCustom(), array(
            'entity' => 'invoice',
            'id' => $invoice->getId(),
            'method' => 'cancel',
            'url' => 'front_invoice_cancel'
        ));

        // 建立發送給歐付寶的資訊封包( items & price passby reference )
        $paymentHelper->setBenefitHelper($this->get('helper.benefit'));
        $paymentHelper->buildAllPayInfo($invoice, $items, $price);

        $adapter = new Adapter;
        $adapter->init(
            $this->container->getParameter('allpay_hashkey'), 
            $this->container->getParameter('allpay_hashiv'), 
            $this->container->getParameter('allpay_merchantid'), 
            true
        );

        if ($invoice->getCreditInstallment() > 0) {
            $adapter->allInOne->Send['ChoosePayment'] = 'Credit';
            $adapter->allInOne->SendExtend['CreditInstallment'] = $invoice->getCreditInstallment();
        }

        // 發送通知信, 請大家注意該單後續有無結帳
        $notifier = $this->get('avenue.notifier');
        $notifier->noticeOrder($invoice);

        return new Response($adapter->pay(array(
            'ReturnURL' => $this->get('router')->generate('front_que_return', array(), true),
            'ClientBackURL' => $this->get('router')->generate('front_profile_orders', array(), true),
            'IgnorePayment' => 'CVS#BARCODE',
            'Items' => $items,
            'TotalAmount' => (int) $price, // 應該是運算完購物金的數字
            'MerchantTradeNo' => $invoice->getSn(),
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'TradeDesc' => '描述'
        )));
    }

    /**
     * 客戶訂單記錄頁面
     * 
     * @Route("/payment/list")
     */
    public function listAction()
    {
        return new Response();
    }

    public function errorAction()
    {
        
    }
}
