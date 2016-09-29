<?php

namespace Woojin\StoreBundle\Entity;

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
        return NULL === $this->price ? '' : floor($this->price * $this->storePercentage);
    }

    public function getBsoProfit()
    {
        return NULL === $this->price ? '' : floor($this->price * $this->bsoPercentage);
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
}
