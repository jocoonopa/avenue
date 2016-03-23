<?php

namespace Woojin\FrontBundle\Controller\Mobile;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\GoodsBundle\Entity\Promotion;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\OrderBundle\Entity\Custom;

use Woojin\Utility\Avenue\Avenue;

/**
 * @Route("/mobile")
 */
class MainController extends Controller
{
    /**
     * @Route("", name="mobile_front_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $mobileDetector = $this->get('resolver.device');
        $mobileDetector->setForce($request);

        $em = $this->getDoctrine()->getManager();

        $promotions = $em->getRepository('WoojinGoodsBundle:Promotion')->findValid();
        $tls = $em->getRepository('WoojinGoodsBundle:ProductTl')->findNotExpired();

        return array('promotions' => $promotions, 'tls' => $tls);
    }

    /**
     * queryParamter:{
     *     page: 1, 
     *     brand_id: null,
     *     pattern_group_id: null
     * }
     * 
     * @Route("/portfolio", name="mobile_front_portfolio", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function portfolioAction(Request $request)
    {
        $brandId = $request->query->get('brand_id', 0);
        $brandIds = array();
        $assumeArrayLength = 100;

        if (Avenue::BRAND_OTHER === (int) $brandId) {
            for ($i = 1; $i < $assumeArrayLength; $i ++) {
                if (!in_array($i, array(
                    Avenue::BRAND_HERMES,
                    Avenue::BRAND_CHANEL,
                    Avenue::BRAND_LV,
                    Avenue::BRAND_GUCCI,
                    Avenue::BRAND_PARADA,
                    Avenue::BRAND_CHLOE,
                    Avenue::BRAND_PARIS,
                    Avenue::BRAND_YSL
                ))) {
                    $brandIds[] = $i;
                }
            }
        } else {
            $brandIds[] = $brandId;
        }

        $request->request->set('patternGroup', array($request->query->get('pattern_group_id', 0)));
        $request->request->set('brand', $brandIds);
        $request->request->set('page', $request->query->get('page', 1));
        $request->request->set('productStatus', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY));
        $request->request->set('perpage', 10);
        $request->request->set('isAllowWeb', 1);

        return $this->get('product.finder')->find($request)->getMobileViewData();
    }

    /**
     * queryParamter:{search_text: null}
     * 
     * @Route("/products", name="mobile_front_search", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $request->request->set('textSeries', $request->query->get('search'));
        $request->request->set('page', $request->query->get('page', 1));
        $request->request->set('productStatus', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY));
        $request->request->set('perpage', 10);
        $request->request->set('isAllowWeb', 1);

        return $this->get('product.finder')->find($request)->getMobileViewData();
    }

    /**
     * @Route("/product/{id}", requirements={"id"="\d+"}, name="mobile_front_product")
     * @ParamConverter("product", class="WoojinGoodsBundle:GoodsPassport")
     * @Method("GET")
     * @Template()
     */
    public function productAction(GoodsPassport $product)
    {
        if (!$product->getIsAllowWeb()) {
            throw $this->createNotFoundException('The product does not exist');
        }
        
        $desimg = $product->getDesimg();

        if ($desimg) {
            for ($i = 0; $i < 5; $i ++) {
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $desimg->getSplitPath($i))) {
                    // 切圖片, 把 desimg 切成五張小圖
                    $this->get('factory.desimg')->spliceDesImage($desimg);

                    break;
                }
            }
        }

        return array('product' => $product);
    }

    /**
     * @Route("/promotion/{id}", name="mobile_front_promotion", options={"expose"=true})
     * @ParamConverter("promotion", class="WoojinGoodsBundle:Promotion")
     * @Method("GET")
     * @Template()
     */
    public function promotionAction(Request $request, Promotion $promotion)
    {
        $request->request->set('page', $request->query->get('page', 1));
        $request->request->set('promotion', array($promotion->getId()));
        $request->request->set('productStatus', array(Avenue::GS_ONSALE));
        $request->request->set('perpage', 10);
        $request->request->set('isAllowWeb', 1);

        $return = $this->get('product.finder')->find($request)->getMobileViewData();
        $return['promotion'] = $promotion;

        return $return; 
    }

    /**
     * @Route("/cart", requirements={"id"="\d+"}, name="mobile_front_cart")
     * @Method("GET")
     * @Template()
     */
    public function cartAction(Request $request)
    {
        $session = $this->get('avenue.session')->get();

        if ($session->get('custom')) {
            return $this->redirect($this->generateUrl('mobile_front_payment'));
        }

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

        return array('products' => $products);
    }

    /**
     * @Route("/payment", name="mobile_front_payment", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function paymentAction(Request $request)
    {
        $session = $this->get('avenue.session')->get();

        if (!$session->get('custom')) {
            return $this->redirect($this->generateUrl('mobile_front_login'));
        }

        $totalAmount = 0;

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

        foreach ($products as $product) {
            $totalAmount += $product->getPromotionPrice(true);
        }

        /**
         * Custom object
         * 
         * @var \stdClass
         */
        $custom = json_decode($session->get('custom'), true);

        return array(
            'products' => $products,
            'totalAmount' => $totalAmount,
            'custom' => $custom
        );
    }

    /**
     * 不論登入或註冊完成，一律先檢查購物車有無商品，若有直接導向結帳頁，
     * 若無導向首頁
     * 
     * @Route("/login", name="mobile_front_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        return array();
    }

    /**
     * 忘記密碼
     * 
     * @Route("forgot/{activeKey}/{email}", name="mobile_front_custom_forgot_nosession")
     * @Method("GET")
     * @Template()
     */
    public function forgotAction($activeKey, $email)
    {
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
}
