<?php

namespace Woojin\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\StoreBundle\Entity\Store;
use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\Handler\ResponseHandler;

class AuctionController extends Controller
{
    /**
     * @Route("/auction/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_auction_show", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("GET")
     */
    public function showAction($auction = NULL, $_format)
    {
        $responseArr = (NULL === $auction)
            ? array('status' => Avenue::IS_ERROR, 'msg' => '產編不存在', 'http_status_code' => Response::HTTP_NOT_FOUND) 
            : array('status' => Avenue::IS_SUCCESS, 'auction' => $auction)
        ;

        return $this->_getResponse($responseArr, $_format);
    }

    /**
     * @Route("/auction/{_format}", defaults={"_format"="json"}, name="api_new_auction", options={"expose"=true})
     * @Method("POST")
     */
    public function newAction(Request $request, $_format)
    {
        /**
         * The Current User
         * 
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        /**
         * DoctrineManager
         * 
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();
        
        /**
         * Fetch the bsoStore Entity
         * 
         * @var \Woojin\StoreBundle\Entity\Store
         */
        $bsoStore = $em->getRepository('WoojinStoreBundle:Store')->find(Store::STORE_BSO_ID);
        
        /**
         * The given product sn, will be used to fetch product entity
         * 
         * @var string
         */
        $sn = $request->request->get('sn', NULL);

        /**
         * Find product with sn
         * 
         * @var mixed \Woojin\GoodsBundle\Entity\GoodsPassport||NULL
         */
        $product = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $sn));

        if ($this->hasNoFoundProduct($product)) {
            return $this->_getResponse(array('status' => Avenue::IS_ERROR, 'msg' => '產編不存在', 'http_status_code' => Response::HTTP_NOT_FOUND), $_format);
        }

        if (!$this->isProductOnSale($product)) {
            return $this->_getResponse(array('status' => Avenue::IS_ERROR, 'msg' => '產品非上架狀態', 'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED), $_format);
        }

        $auction = $this->get('auction.service')->create([
            'product' => $product,
            'creater' => $user,
            'createStore' => $user->getStore(),
            'bsoStore' => $bsoStore
        ]);

        return $this->_getResponse(array('status' => Avenue::IS_SUCCESS, 'auction' => $auction), $_format);
    }

    /**
     * @Route("/auction/back/{_format}", defaults={"_format"="json"}, name="api_back_auction", options={"expose"=true})
     * @Method("PUT")
     */
    public function backAction(Request $request, $_format)
    {
        /**
         * The Current User
         * 
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        /**
         * DoctrineManager
         * 
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();
        
        /**
         * The given product sn, will be used to fetch product entity
         * 
         * @var string
         */
        $sn = $request->request->get('sn', NULL);

        /**
         * Find product with sn
         * 
         * @var mixed \Woojin\GoodsBundle\Entity\GoodsPassport||NULL
         */
        $product = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $sn));

        if ($this->hasNoFoundProduct($product)) {
            return $this->_getResponse(array('status' => Avenue::IS_ERROR, 'msg' => '產編不存在', 'http_status_code' => Response::HTTP_NOT_FOUND), $_format);
        }

        if (!$this->isProductBsoOnBoard($product)) {
            return $this->_getResponse(array('status' => Avenue::IS_ERROR, 'msg' => '產品非競拍狀態', 'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED), $_format);
        }

        if (!$user->getStore()->getSn() !== substr($product->getSn(), 0, 1)) {
            return $this->_getResponse(array('status' => Avenue::IS_ERROR, 'msg' => '非產品所屬門市人員', 'http_status_code' => Response::HTTP_FORBIDDEN), $_format);
        }

        $auction = $this->get('auction.service')->back([
            'product' => $product,
            'backer' => $user,
            'createStore' => $user->getStore(),
            'bsoStore' => $bsoStore
        ]);

        return $this->_getResponse(array('status' => Avenue::IS_SUCCESS, 'auction' => $auction), $_format);
    }

    /**
     * @Route("/auction/sold/{_format}", defaults={"_format"="json"}, name="api_sold_auction", options={"expose"=true})
     * @Method("PUT")
     */
    public function soldAction()
    {

    }

    /**
     * @Route("/auction/cancel/{_format}", defaults={"_format"="json"}, name="api_cancel_auction", options={"expose"=true})
     * @Method("PUT")
     */
    public function cancelAction()
    {

    }

    /**
     * Check whether given variable is NULL or NOT
     * 
     * @param  mixed  $product
     * @return boolean         
     */
    protected function hasNoFoundProduct($product)
    {
        return NULL === $product;
    }

    /**
     * Check whether the product status is on board or not
     * 
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport $product
     * @return boolean         
     */
    protected function isProductOnSale(GoodsPassport $product)
    {
        return Avenue::GS_ONSALE === $product->getStatus()->getId();
    }

    /**
     * Is product status equal to Avenue::GS_BSO_ONBOARD?
     * 
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport  $product
     * @return boolean         
     */
    protected function isProductBsoOnBoard(GoodsPassport $product)
    {
        return Avenue::GS_BSO_ONBOARD === $product->getStatus()->getId();
    }

    /**
     * Is product belong store the same as user belongs?
     * 
     * @param  \Woojin\UserBundle\Entity\User           $user    
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport $product 
     * @return boolean                
     */
    protected function isProductBelongStoreUser(User $user, GoodsPassport $product)
    {
        return $user->getStore()->getSn() === substr($product->getSn(), 0, 1);
    }

    private function _getResponse($data, $_format)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($data, $_format);
        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getResponse($jsonResponse, $_format);
    }
}
