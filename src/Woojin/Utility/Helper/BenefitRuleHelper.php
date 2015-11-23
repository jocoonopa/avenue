<?php

namespace Woojin\Utility\Helper;

use Woojin\OrderBundle\Entity\BenefitRule;
use Woojin\OrderBundle\Entity\Orders;

class BenefitRuleHelper implements IHelper
{
    protected $rules;

    public function __construct($rules)
    {
        $this->rules = $rules;
    }

    /**
     * 取得估計可折扣購物金金額
     * 
     * @return integer
     */
    public function getPredict(Orders $order)
    {
        foreach ($this->rules as $rule) {
            if (!$this->isAccepted($order, $rule)) {
                continue;
            }

            return $this->calculate($order, $rule);
        }

        return 0;
    }

    /**
     * 判斷訂單應付價格是否在條件的範圍內(上限若為0視為無上限)
     * 
     * @param  Orders      $order
     * @param  BenefitRule $rule 
     * @return boolean           
     */
    protected function isAccepted(Orders $order, BenefitRule $rule)
    {
        return ($order->getRequired() >= $rule->getSill() && (($order->getRequired() < $rule->getCeiling()) || $rule->getCeiling() === 0));
    }

    /**
     * 計算根據規則得出的可折扣購物金金額
     * 
     * @param  Orders      $order 
     * @param  BenefitRule $rule  
     * @return integer             
     */
    protected function calculate(Orders $order, BenefitRule $rule)
    {
        return ($this->getIsStock() ? $this->stockPredict($order, $rule) : $this->nonStockPredict($order, $rule));
    }

    /**
     * 可累計的折扣購物金金額
     * 
     * @return integer
     */
    protected function stockPredict(Orders $order, BenefitRule $rule)
    {
        $turnLength = floor($order->getRequired()/$rule->getCeiling());

        return $turnLength * $rule->getGift();
    }

    /**
     * 不可累計的折扣購物金金額
     * 
     * @return integer
     */
    protected function nonStockPredict(Orders $order, BenefitRule $rule)
    {
        return $rule->getGift();
    }
}