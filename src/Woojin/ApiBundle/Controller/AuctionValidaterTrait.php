<?php

namespace Woojin\ApiBundle\Controller;

use Woojin\Utility\Avenue\Avenue;
use Symfony\Component\HttpFoundation\Response;

trait AuctionValidaterTrait
{
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
            'isProductBelongStoreUser' => array(
                'params' => array($user, $product),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('ProductNotBelongToYou'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
            ))
        );
    }
}
