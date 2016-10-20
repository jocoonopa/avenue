<?php

namespace Woojin\StoreBundle\Entity;

use Woojin\Utility\Avenue\ShippingCalculator;
use Woojin\StoreBundle\Entity\AuctionPayment;
use Woojin\UserBundle\Entity\User;

trait AuctionTrait
{
    /**
     * 方便前端呼叫api使用，不需要同樣邏輯前後端都搞一次
     * 
     * @param  mixed $auction 
     * @return Auction $auction
     */
    public static function initVirtualProperty($auction)
    {
        if (!($auction instanceof Auction) || true === $auction->hasInitializedVirtualProperty) {
            return $auction;
        }

        /**
         * 扣除稅實拿金額
         * 
         * @var integer
         */
        $paymentAmount = $auction->getPaymentNoTax();

        /**
         * 門市相對毛利比(門市+BSO)
         * 
         * @var float
         */
        $storeRelatePercentage = $auction->getStorePercentage()/($auction->getStorePercentage() + $auction->getBsoPercentage());

        /**
         * BSO相對毛利比(門市+BSO)
         * 
         * @var float
         */
        $bsoRelatePercentage = 1 - $storeRelatePercentage;

        $auction->customProfit = (int) ($auction->getPrice() * $auction->getCustomPercentage());

        /**
         * Avenue 共享毛利
         *
         * @var integer
         */
        $remainProfit = $paymentAmount - $auction->customProfit - $auction->getShippingCost();

        $auction->storeProfit = (int) ($remainProfit * $storeRelatePercentage);
        $auction->bsoProfit = (int) ($remainProfit * $bsoRelatePercentage);

        $auction->hasInitializedVirtualProperty = true;

        return $auction;
    }

    public function getPaymentNoTax()
    {
        $sum = 0;

        foreach ($this->getPayments()->filter($this->isPaymentValid()) as $payment) {
            $sum += (int) ($payment->getAmount() * $payment->getPayType()->getDiscount());
        }

        return $sum;
    }

    protected function isPaymentValid()
    {
        return function ($payment) {
            return false === $payment->getIsCancel();
        };
    }

    /**
     * 1. 擁有銷貨權限
     * 2. 競拍狀態為售出
     * 3. 競拍毛利狀態不為分配完畢
     * 4. 使用者所屬店和競拍所屬店相同
     *
     * @param  User    $user
     * @return boolean
     */
    public function isAllowedEditPayment(User $user)
    {
        return $user->hasAuth('SELL')
            && Auction::STATUS_SOLD === $this->getStatus()
            && Auction::PROFIT_STATUS_ASSIGN_COMPLETE !== $this->getProfitStatus()
            && $user->getStore()->getId() === $this->getBsoStore()->getId()
        ;
    }

    public function isAllowedEditSoldAt(User $user)
    {
        return $user->hasAuth('SELL')
            && Auction::STATUS_SOLD === $this->getStatus()
            && $user->getStore()->getId() === $this->getBsoStore()->getId()
        ;
    }

    public function isAllowedAssignProfit(User $user)
    {
        return Auction::STATUS_SOLD === $this->getStatus() 
            && Auction::PROFIT_STATUS_PAY_COMPLETE === $this->getProfitStatus()
            && $user->getStore()->getId() === $this->getCreateStore()->getId()
        ;
    }

    public function getProductSn()
    {
        return is_null($this->product) ? '' : $this->product->getSn();
    }

    public function getProductName()
    {
        return is_null($this->product) ? '' : $this->product->getName();
    }

    public function getProductOrgSn()
    {
        return is_null($this->product) ? '' : $this->product->getOrgSn();
    }

    public function getProductBrandName()
    {
        if (is_null($this->product)) {
            return '';
        }

        return is_null($this->product->getBrand()) ? '' : $this->product->getBrand()->getName();
    }

    public function getProductColorName()
    {
        if (is_null($this->product)) {
            return '';
        }

        return is_null($this->product->getColor()) ? '' : $this->product->getColor()->getName();
    }

    public function getCreateStoreName()
    {
        return is_null($this->createStore) ? '' : $this->createStore->getName();
    }

    public function getSellerName()
    {
        return is_null($this->seller) ? '' : $this->seller->getName();
    }

    public function getSellerMobil()
    {
        return is_null($this->seller) ? '' : $this->seller->getMobil();
    }

    public function getBuyerName()
    {
        return is_null($this->buyer) ? '' : $this->buyer->getName();
    }

    public function getBuyerMobil()
    {
        return is_null($this->buyer) ? '' : $this->buyer->getMobil();
    }

    public function getCustomProfit()
    {
        if (is_null($this->price)) {
            return '';
        }

        if (false === $this->hasInitializedVirtualProperty) {
            static::initVirtualProperty($this);
        }

        return $this->customProfit;
    }

    public function getStoreProfit()
    {
        if (is_null($this->price)) {
            return '';
        }

        if (false === $this->hasInitializedVirtualProperty) {
            static::initVirtualProperty($this);
        }

        return $this->storeProfit;
    }

    public function getBsoProfit()
    {
        if (is_null($this->price)) {
            return '';
        }

        if (false === $this->hasInitializedVirtualProperty) {
            static::initVirtualProperty($this);
        }

        return $this->bsoProfit;
    }

    public function getShippingCost()
    {
        return is_null($this->shipping) ? 0 : $this->shipping->getOption()->getCost();
    }

    public function getCreaterName()
    {
        return is_null($this->creater) ? '' : $this->creater->getUsername();
    }

    public function getSoldAtString($format = 'Y-m-d H:i:s')
    {
        return is_null($this->soldAt) ? '' : $this->soldAt->format($format);
    }

    public function getBsserName()
    {
        return is_null($this->bsser) ? '' : $this->bsser->getUsername();
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
