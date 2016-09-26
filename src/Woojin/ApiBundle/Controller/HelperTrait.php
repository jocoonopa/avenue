<?php

namespace Woojin\ApiBundle\Controller;

use JMS\Serializer\SerializerBuilder;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\OrderBundle\Entity\Custom;
use Woojin\StoreBundle\Entity\Auction;
use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\Handler\ResponseHandler;
use Symfony\Component\HttpFoundation\Response;

trait HelperTrait
{
    /**
     * Gen response with given custom
     *
     * @param  mixed $result  [Woojin\OrderBundle\Entity\Custom|NULL]
     * @param  string $_format
     * @return Symfony\Component\HttpFoundation\Response
     */
    private function _genResponseWithCustom($result, $_format)
    {
        $responseArr = $result instanceof Custom
            ? array(
                'status' => Avenue::IS_SUCCESS,
                'custom' => $result,
                'http_status_code' => Response::HTTP_OK
            ) : array(
                'status' => Avenue::IS_ERROR,
                'msg' => $result->getMessage(),
                'http_status_code' => Response::HTTP_INTERNAL_SERVER_ERROR
            );

        return $this->_getResponse($responseArr, $_format);
    }

    /**
     * Gen response with given auction
     *
     * @param  mixed $result  [Woojin\OrderBundle\Entity\Custom|NULL]
     * @param  string $_format
     * @return Symfony\Component\HttpFoundation\Response
     */
    private function _genResponseWithServiceReturnAuction($result, $_format)
    {
        $responseArr = $result instanceof Auction
            ? array(
                'status' => Avenue::IS_SUCCESS,
                'auction' => $result,
                'http_status_code' => Response::HTTP_OK
            ) : array(
                'status' => Avenue::IS_ERROR,
                'msg' => $result->getMessage(),
                'http_status_code' => Response::HTTP_INTERNAL_SERVER_ERROR
            );

        return $this->_getResponse($responseArr, $_format);
    }

    private function _getResponse($data, $_format)
    {
        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($data, $_format);
        $responseHandler = new ResponseHandler;

        return $responseHandler->getResponse($jsonResponse, $_format);
    }

    /**
     * Gen show auction return array
     *
     * @param  mixed Woojin\StoreBundle\Entity\Auction|NULL $auction
     * @return array
     */
    private function _genShowAuctionReturn($auction)
    {
        return (NULL === $auction)
            ? array(
                'status' => Avenue::IS_ERROR,
                'msg' => $this->get('translator')->trans('ProductSnIsNotExist'),
                'http_status_code' => Response::HTTP_NOT_FOUND
            ) : array('status' => Avenue::IS_SUCCESS, 'auction' => $auction)
        ;
    }

    /**
     * Execute registed validaters，if exists error it return response with error msg,
     * otherwise return null
     *
     * @param  array  $validaters
     * @return array
     */
    protected function execValidaters(array $validaters)
    {
        foreach ($validaters as $methodName => $regists) {
            if (!call_user_func_array(array($this, $methodName), $regists['params'])) {
                return $regists['response'];
            }
        }

        return array();
    }

    private function _isNotNull($val)
    {
        return NULL !== $val;
    }

    /**
     * Check whether given variable is NULL or NOT
     *
     * @param  mixed  $custom
     * @return boolean
     */
    protected function hasFoundCustom($custom)
    {
        return $custom instanceof Custom;
    }

    /**
     * Check whether given variable is NULL or NOT
     *
     * @param  mixed  $custom
     * @return boolean
     */
    protected function hasNotFoundCustom($custom)
    {
        return !$this->hasFoundCustom($custom);
    }

    /**
     * Is the custom belong to the given user?
     *
     * @param  \Woojin\OrderBundle\Entity\Custom
     * @param  \Woojin\UserBundle\Entity\User   $user
     * @return boolean
     */
    protected function isBelongUserStore(Custom $custom, User $user)
    {
        return $custom->isBelongUserStore($user);
    }

    /**
     * Check whether given variable is NULL or NOT
     *
     * @param  mixed  $product
     * @return boolean
     */
    protected function hasFoundProduct($product)
    {
        return $product instanceof GoodsPassport;
    }

    /**
     * Check whether the product status is on board or not
     *
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport $product
     * @return boolean
     */
    protected function isProductOnSale(GoodsPassport $product)
    {
        return $product->isProductOnSale();
    }

    /**
     * Is product status_id equal to Avenue::GS_BSO_ONBOARD?
     *
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport  $product
     * @return boolean
     */
    protected function isProductBsoOnBoard(GoodsPassport $product)
    {
        return $product->isProductBsoOnBoard();
    }

    /**
     * Is product has uploaded to Yahoo
     *
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport  $product
     * @return boolean
     */
    protected function isNotYahooProduct(GoodsPassport $product)
    {
        return !$product->isYahooProduct();
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
        return $product->isOwnProduct($user);
    }

    /**
     * Is product status_id equal to Avenue::GS_BSO_SOLD
     *
     * @param  \Woojin\GoodsBundle\Entity\GoodsPassport  $product
     * @return boolean
     */
    protected function isProductBsoSold(GoodsPassport $product)
    {
        return $product->isProductBsoSold();
    }

    /**
     * Is auction belong to the given user's store
     *
     * @param  \Woojin\UserBundle\Entity\User  User    $user
     * @param  \Woojin\StoreBundle\Entity\Auction  Auction $auction
     * @return boolean
     */
    protected function isAuctionBelongGivenUsersStore(User $user, Auction $auction)
    {
        return $auction->isAuctionBelongGivenUsersStore($user);
    }

    /**
     * Has found auction
     *
     * @param  mixed  $auction
     * @return boolean
     */
    protected function hasFoundAuction($auction)
    {
        return $auction instanceof Auction;
    }

    /**
     * Is price a valid number
     *
     * @param  integer $price
     * @return boolean
     */
    protected function isPriceValidNumber($price)
    {
        return is_numeric($price);
    }

    /**
     * Check if this custom has no orders
     *
     * @return boolean
     */
    protected function hasNoOrders(Custom $custom)
    {
        return $custom->hasNoOrders();
    }
}
