<?php

namespace Woojin\StoreBundle\Entity;

use Woojin\StoreBundle\Entity\Auction;
use Woojin\StoreBundle\Entity\AuctionPayment;
use Woojin\Utility\Avenue\ShippingCalculator;
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

        /**
         * 客戶分潤
         * 
         * @var integer
         */
        $auction->customProfit = $auction->calCustomProfitAndGet();

        /**
         * Avenue 共享毛利 = 扣除稅實拿金額 - 客戶分潤 - 運費 - 商品成本
         *
         * @var integer
         */
        $remainProfit = $auction->getRemainProfit();

        /**
         * 門市分潤 = 共享毛利 * 門市相對分潤
         * 
         * @var integer
         */
        $auction->storeProfit = (int) ($remainProfit * $storeRelatePercentage);

        /**
         * BSO分潤 = 共享毛利 * BSO相對分潤
         * 
         * @var integer
         */
        $auction->bsoProfit = (int) ($remainProfit * $bsoRelatePercentage);

        $auction->hasInitializedVirtualProperty = true;

        return $auction;
    }

    /**
     * 不含稅總和
     * 
     * @return integer
     */
    public function getPaymentTotalWithNoTax()
    {
        $sum = 0;

        foreach ($this->getPayments()->filter($this->isPaymentValid()) as $payment) {
            $sum += $this->getPaymentAmountWithoutTax($payment);
        }

        return $sum;
    }

    /**
     * Avenue 共享毛利 = 扣除稅實拿金額 - 客戶分潤 - 運費 - [(若為寄賣型態)商品成本]
     *
     * @var integer
     */
    public function getRemainProfit()
    {
        $remainProfit = $this->getPaymentTotalWithNoTax() - $this->customProfit - $this->getShippingCost();
        $remainProfit -= $this->isConsign() ? $this->getProduct()->getCost() : 0;

        return $remainProfit;
    }

    /**
     * 取得發票顯示金額:
     * 
     * 現金(含發票)稅由香榭吸收(1), 刷卡由客戶吸收(1.08)
     * 
     * @return integer
     */
    public function getInvoicePrice()
    {
        $sum = 0;

        foreach ($this->getPayments()->filter($this->isPaymentValid()) as $payment) {
            $sum += $this->getPaymentAmountWithTax($payment);
        }

        return $sum;
    }

    /**
     * 計算客戶分潤:
     *
     * 若判斷是寄賣型態, 則成本(寄賣回扣)為此 auction 之客戶分潤,
     * 若否,則競拍售價 * 客戶分潤比 為客戶分潤
     * 
     * @var $this
     */
    public function calCustomProfitAndGet()
    {
        return (int) ($this->isConsign() ? $this->getProduct()->getCost() : $this->getPrice() * $this->getCustomPercentage());
    }

    /**
     * 判斷此競拍是否為寄賣型態
     * 
     * @return boolean
     */
    public function isConsign()
    {
        return 0 === $this->getCustomPercentage() && !is_null($this->getProduct()->getCustom());
    }

    /**
     * 是否允許新增/修改 payment 實體
     * 
     * 1. 擁有銷貨權限
     * 2. 競拍狀態為售出
     * 3. 競拍毛利狀態不為分配完畢
     * 4. 使用者所屬店和競拍所屬店相同
     *
     * @param  \Woojin\UserBundle\Entity\User    $user [操作者]
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

    /**
     * 是否允許修改售出時間
     *
     * 1. 檢查使用者是否擁有銷貨權限
     * 2. 競拍實體狀態是否為售出
     * 3. 操作者所屬店家是否和競拍BSO店家相同 
     * 
     * @param  \Woojin\UserBundle\Entity\User    $user  [操作者]
     * @return boolean      
     */
    public function isAllowedEditSoldAt(User $user)
    {
        return $user->hasAuth('SELL')
            && Auction::STATUS_SOLD === $this->getStatus()
            && $user->getStore()->getId() === $this->getBsoStore()->getId()
        ;
    }

    /**
     * 是否允許分派紅利
     *
     * 1. 競拍實體狀態是否為售出
     * 2. 競拍毛利狀態是否為已付清
     * 3. 操作者所屬店家是否和競拍刷入店家相同
     * 
     * @param  Woojin\UserBundle\Entity\User    $user [操作者]
     * @return boolean
     */
    public function isAllowedAssignProfit(User $user)
    {
        return Auction::STATUS_SOLD === $this->getStatus()
            && Auction::PROFIT_STATUS_PAY_COMPLETE === $this->getProfitStatus()
            && $user->getStore()->getId() === $this->getCreateStore()->getId()
        ;
    }

    /**
     * 競拍尚未結清金額
     * 
     * @return integer
     */
    public function getOwe()
    {
        $payments = array_filter($this->getPayments()->toArray(), array($this, 'isNotCalcelled'));
        $price = $this->getPrice();

        foreach ($payments as $payment) {
            $price -= $payment->getAmount();
        }

        return $price;
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
        return array_key_exists($this->status, static::$statusMap) ? static::$statusMap[$this->status] : '';
    }

    public function getProfitStatusName()
    {
        return array_key_exists($this->profitStatus, static::$profitStatusMap) ? static::$profitStatusMap[$this->profitStatus] : '';
    }

    protected function isNotCalcelled(AuctionPayment $payment)
    {
        return !$payment->getIsCancel();
    }

    protected function getPaymentAmountWithTax(AuctionPayment $payment)
    {
        return (int) $payment->getPayType()->getAmountWithTax($payment->getAmount());
    }

    protected function getPaymentAmountWithoutTax(AuctionPayment $payment)
    {
        return (int) ($payment->getPayType()->isCreditCard() 
            ? $payment->getAmount() 
            : $payment->getPayType()->getAmountWithoutTax($payment->getAmount()))
        ;
    }

    protected function isPaymentValid()
    {
        return function ($payment) {
            return false === $payment->getIsCancel();
        };
    }
}
