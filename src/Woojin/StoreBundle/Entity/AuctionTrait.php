<?php

namespace Woojin\StoreBundle\Entity;

use Woojin\Utility\Avenue\ShippingCalculator;
use Woojin\StoreBundle\Entity\AuctionPayment;


trait AuctionTrait
{
    public function getProductSn()
    {
        return NULL === $this->product ? '' : $this->product->getSn();
    }

    public function getProductName()
    {
        return NULL === $this->product ? '' : $this->product->getName();
    }

    public function getProductOrgSn()
    {
        return NULL === $this->product ? '' : $this->product->getOrgSn();
    }

    public function getProductBrandName()
    {
        if (NULL === $this->product) {
            return '';
        }

        return NULL === $this->product->getBrand() ? '' : $this->product->getBrand()->getName();
    }

    public function getProductColorName()
    {
        if (NULL === $this->product) {
            return '';
        }

        return NULL === $this->product->getColor() ? '' : $this->product->getColor()->getName();
    }

    public function getCreateStoreName()
    {
        return NULL === $this->createStore ? '' : $this->createStore->getName();
    }

    public function getSellerName()
    {
        return NULL === $this->seller ? '' : $this->seller->getName();
    }

    public function getSellerMobil()
    {
        return NULL === $this->seller ? '' : $this->seller->getMobil();
    }

    public function getBuyerName()
    {
        return NULL === $this->buyer ? '' : $this->buyer->getName();
    }

    public function getBuyerMobil()
    {
        return NULL === $this->buyer ? '' : $this->buyer->getMobil();
    }

    public function getCustomProfit()
    {
        return NULL === $this->price ? '' : floor($this->price * $this->customPercentage);
    }

    public function getStoreProfit()
    {
        if (NULL === $this->price) {
            return '';
        }

        $profit = ($this->price - $this->getCustomProfit() - $this->getShippingCost()) * $this->storePercentage;

        return floor($profit);
    }

    public function getBsoProfit()
    {
        if (NULL === $this->price) {
            return '';
        }

        $profit = ($this->price - $this->getCustomProfit() - $this->getShippingCost()) * $this->bsoPercentage;

        return floor($profit);
    }

    public function getShippingCost()
    {
        return NULL === $this->shipping ? 0 : $this->shipping->getOption()->getCost();
    }

    public function getCreaterName()
    {
        return NULL === $this->creater ? '' : $this->creater->getUsername();
    }

    public function getSoldAtString($format = 'Y-m-d H:i:s')
    {
        return NULL === $this->soldAt ? '' : $this->soldAt->format($format);
    }

    public function getBsserName()
    {
        return NULL === $this->bsser ? '' : $this->bsser->getUsername();
    }

    public function getStatusName()
    {
        $name = '';

        switch ($this->status)
        {
            case Auction::STATUS_ONBOARD:
                $name = '待競拍';
                break;

            case Auction::STATUS_SOLD:
                $name = '拍賣完畢';
                break;

            case Auction::STATUS_BACK_TO_STORE:
                $name = '歸還門市';
                break;

            default:
                break;
        }

        return $name;
    }

    public function getOwe()
    {
        $payments = array_filter($this->getPayments()->toArray(), array($this, 'isNotCalcelled'));
        $price = $this->getPrice();

        foreach ($payments as $payment) {
            $price -= $payment->getAmount();
        }

        return $price;
    }

    protected function isNotCalcelled(AuctionPayment $payment)
    {
        return !$payment->getIsCancel();
    }
}
