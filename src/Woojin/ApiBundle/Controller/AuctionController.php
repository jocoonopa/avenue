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

use Woojin\StoreBundle\Entity\Store;
use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\Handler\ResponseHandler;

class AuctionController extends Controller
{
    use HelperTrait;

    /**
     * @Route("/auction/{id}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_auction_show", options={"expose"=true})
     * @ParamConverter("auction", class="WoojinStoreBundle:Auction")
     * @Method("GET")
     */
    public function showAction($auction = NULL, $_format)
    {
        return $this->_getResponse($this->_genShowAuctionReturn($auction), $_format);
    }

    /**
     * @Route("/auction/show/{sn}/{_format}", requirements={"id"="\d+"}, defaults={"_format"="json"}, name="api_auction_show_bysn", options={"expose"=true})
     * @Method("GET")
     */
    public function findBySnAction($sn, $_format)
    {
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * Find product with sn
         *
         * @var mixed \Woojin\GoodsBundle\Entity\GoodsPassport||NULL
         */
        $product = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $sn));

        /**
         * The auction entity
         *
         * @var \Woojin\StoreBundle\Entity\Auction
         */
        $auction = NULL === $product ? NULL : $em->getRepository('WoojinStoreBundle:Auction')->fetchAuctionByProduct($product);

        return $this->_getResponse($this->_genShowAuctionReturn($auction), $_format);
    }

    /**
     * @Route("/auction/{_format}", defaults={"_format"="json"}, name="api_list_auction", options={"expose"=true})
     * @Method("GET")
     */
    public function listAction($_format)
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
         * Fetch products we would return into response
         */
        $products = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findBsoProductsUserStoreOwn($user->getStore());

        return $this->_getResponse($products, $_format);
    }

    /**
     * @Route("/auction/{_format}", defaults={"_format"="json"}, name="api_new_auction", options={"expose"=true})
     * @Method("POST")
     */
    public function newAction(Request $request, $_format)
    {
        list($user, $em, $product) = $this->initBaseVar($request);

        /**
         * Fetch the bsoStore Entity
         *
         * @var \Woojin\StoreBundle\Entity\Store
         */
        $bsoStore = $em->getRepository('WoojinStoreBundle:Store')->find(Store::STORE_BSO_ID);

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getNewActionValidaters(array($user, $product)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        /**
         * The result of service operation
         *
         * @var mixed[\Woojin\StoreBundle\Entity\Auction|Exception]
         */
        $result = $this->get('auction.service')->create([
            'product' => $product,
            'creater' => $user,
            'createStore' => $user->getStore(),
            'bsoStore' => $bsoStore
        ]);

        return $this->_genResponseWithServiceReturnAuction($result, $_format);
    }

    /**
     * @Route("/auction/back/{_format}", defaults={"_format"="json"}, name="api_back_auction", options={"expose"=true})
     * @Method("PUT")
     */
    public function backAction(Request $request, $_format)
    {
        list($user, $em, $product) = $this->initBaseVar($request);

        /**
         * The auction entity
         *
         * @var \Woojin\StoreBundle\Entity\Auction
         */
        $auction = NULL === $product ? NULL : $em->getRepository('WoojinStoreBundle:Auction')->fetchAuctionByProduct($product);

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getBackActionValidaters(array($product, $auction, $user)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        /**
         * The result of service operation
         *
         * @var mixed[\Woojin\StoreBundle\Entity\Auction|Exception]
         */
        $result = $this->get('auction.service')->setAuction($auction)->back(['backer' => $user]);

        return $this->_genResponseWithServiceReturnAuction($result, $_format);
    }

    /**
     * @Route("/auction/sold/{_format}", defaults={"_format"="json"}, name="api_sold_auction", options={"expose"=true})
     * @Method("PUT")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="BSO auction sold action",
     *  requirements={
     *      {
     *          "name"="price",
     *          "requirement"="\d+",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="The sold price"
     *      },
     *      {
     *          "name"="mobil",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Custom mobil number"
     *      },
     *      {
     *          "name"="_format",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="回傳的格式，支援 json, xml, html"
     *      }
     *  }
     * )
     */
    public function soldAction(Request $request, $_format)
    {
        list($user, $em, $product) = $this->initBaseVar($request);

        /**
         * Price
         *
         * @var mixed
         */
        $price = $request->request->get('price');

        /**
         * The custom entity
         *
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(array('mobil' => $request->request->get('mobil'), 'store' => $user->getStore()));

        /**
         * The auction entity
         *
         * @var \Woojin\StoreBundle\Entity\Auction
         */
        $auction = NULL === $product ? NULL : $em->getRepository('WoojinStoreBundle:Auction')->fetchAuctionByProduct($product);

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getSoldActionValidaters(array($product, $auction, $user, $custom, $price)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        /**
         * The result of service operation
         *
         * @var mixed[\Woojin\StoreBundle\Entity\Auction|Exception]
         */
        $result = $this->get('auction.service')->setAuction($auction)->sold([
            'price' => $price,
            'buyer' => $custom,
            'bsser' => $user,
            'soldAt' => new \DateTime
        ]);

        return $this->_genResponseWithServiceReturnAuction($result, $_format);
    }

    /**
     * @Route("/auction/cancel/{_format}", defaults={"_format"="json"}, name="api_cancel_auction", options={"expose"=true})
     * @Method("PUT")
     */
    public function cancelAction(Request $request, $_format)
    {
        list($user, $em, $product) = $this->initBaseVar($request);

        /**
         * The auction entity
         *
         * @var \Woojin\StoreBundle\Entity\Auction
         */
        $auction = NULL === $product ? NULL : $em->getRepository('WoojinStoreBundle:Auction')->fetchAuctionByProduct($product);

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getCancelActionValidaters(array($product, $auction, $user)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        /**
         * The result of service operation
         *
         * @var mixed[\Woojin\StoreBundle\Entity\Auction|Exception]
         */
        $result = $this->get('auction.service')->setAuction($auction)->cancel([
            'canceller' => $user
        ]);

        return $this->_genResponseWithServiceReturnAuction($result, $_format);
    }

    protected function initBaseVar(Request $request)
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

        return array($user, $em, $product);
    }

    /**
     * Provide the validators to newAction
     *
     * @param  array $compose
     * @return array
     */
    protected function getNewActionValidaters(array $compose)
    {
        list($user, $product) = $compose;

        return array(
            'hasFoundProduct' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductSnIsNotExist'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            )),
            'isProductOnSale' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductStatusIsNotOnBoard'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
            )),
            'isNotYahooProduct' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductIsOnYahooStore'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
            )),
            'isProductBelongStoreUser' => array(
                'params' => array($user, $product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductNotBelongToYou'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
            ))
        );
    }

        /**
     * Provide the validators to backAction
     *
     * @param  array $compose
     * @return array
     */
    protected function getBackActionValidaters(array $compose)
    {
        list($product, $auction, $user) = $compose;

        return array(
            'hasFoundProduct' => array(
                'params' => array($product),
                    'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductSnIsNotExist'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
                )),
            'hasFoundAuction' => array(
                'params' => array($auction),
                    'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('AuctionNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
                )),
            'isProductBelongStoreUser' => array(
                'params' => array($user, $product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductNotBelongToYou'),
                    'http_status_code' => Response::HTTP_FORBIDDEN
                )),
            'isProductBsoOnBoard' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductStatusIsNotBSO'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
                ))
        );
    }

    /**
     * Provide the validators to soldAction
     *
     * @param  array $compose
     * @return array
     */
    protected function getSoldActionValidaters(array $compose)
    {
        list($product, $auction, $user, $custom, $price) = $compose;

        return array(
            'isPriceValidNumber' => array(
                'params' => array($price),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('PriceIsNotValid'),
                    'http_status_code' => Response::HTTP_NOT_ACCEPTABLE
                )),
            'hasFoundProduct' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductSnIsNotExist'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
                )),
            'hasFoundAuction' => array(
                'params' => array($auction),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('AuctionNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
                )),
            'isAuctionBelongGivenUsersStore' => array(
                'params' => array($user, $auction),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('AuctionNotBelongToYou'),
                    'http_status_code' => Response::HTTP_FORBIDDEN
                )),
            'isProductBsoOnBoard' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductStatusIsNotBSO'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
                ))
        );
    }

    /**
     * Provide the validators to cancelAction
     *
     * @param  array $compose
     * @return array
     */
    protected function getCancelActionValidaters(array $compose)
    {
        list($product, $auction, $user) = $compose;

        return array(
            'hasFoundProduct' => array(
                'params' => array($product),
                    'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductSnIsNotExist'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
                )),
            'hasFoundAuction' => array(
                'params' => array($auction),
                    'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('AuctionNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
                )),
            'isAuctionBelongGivenUsersStore' => array(
                'params' => array($user, $auction),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('AuctionNotBelongToYou'),
                    'http_status_code' => Response::HTTP_FORBIDDEN
                )),
            'isProductBsoSold' => array(
                'params' => array($product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductStatusIsNotBsoSold'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
                ))
        );
    }
}
