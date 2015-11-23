<?php

namespace Woojin\Utility\YahooApi\Factory;

use Woojin\Utility\YahooApi\Yahoo;

class SubmitMainParameterFactory implements IParameterFactory
{
    public function get()
    {
        return $this->parameters;
    }

    public function create($r)
    {
        $this->parameters = $this->getBaseParameters($r);
        
        $this
            ->addProductName($r)
            ->addMallCategoryId($r)
            ->addSalePrice($r)
            ->addCustomizedMainProductId($r)
            ->addShortDescription($r)
            ->addLongDescription($r)
            ->addStoreCategoryParams($r)
            ->addPayTypeParams($r)
            ->addShippingParams($r)
            ->addSpecDimension1Params($r)
            ->addSpecDimension2Params($r)
        ;

        return $this;
    }

    public function set($key, $val)
    {
        $this->parameters[$key] = $val;

        return $this;
    }

    public function clear()
    {
        $this->parameters = array();

        return $this;
    }

    protected function trim($string)
    {
        $string = str_replace(
            array("\r", "\n", "\r\n", ' ',"&nbsp;", "&times;", '+', '&'),
            array(' ', ' ', ' ', ' ', ' ', 'x', '、', '|'), 
            $string
        );

        return trim($string);
    }

    protected function getBaseParameters(\stdClass $r)
    {
        return array(
            'SaleType'                      => Yahoo::SALE_TYPE,
            'MaxBuyNum'                     => Yahoo::MAX_BUY_NUM,
            'SpecTypeDimension'             => Yahoo::SPEC_TYPE_DIMENSION, // 無規格        
            'Stock'                         => Yahoo::STOCK, // 庫存量
            'SaftyStock'                    => Yahoo::SAFTY_STOCK // 庫存警告
        );
    }

    protected function addProductName(\stdClass $r)
    {      
        $this->parameters['ProductName'] = substr($this->trim($r->product->getSeoName()), 0, Yahoo::PRODUCT_NAME_LIMIT);

        return $this;
    }

    protected function addMallCategoryId(\stdClass $r)
    {
        $this->parameters['MallCategoryId'] = $r->mallCategoryId;

        return $this;
    }

    protected function addMarketPrice(\stdClass $r)
    {
        $this->parameters['MarketPrice'] = $r->product->getPrice();

        return $this;
    }

    protected function addSalePrice(\stdClass $r)
    {
        $this->parameters['SalePrice'] = $r->product->getPromotionPrice(true);

        return $this;
    }

    protected function addCustomizedMainProductId(\stdClass $r)
    {
        $this->parameters['CustomizedMainProductId'] = $r->product->getSn();

        return $this;
    }

    protected function addShortDescription(\stdClass $r)
    {
        $brief = $r->product->getBrief();

        $this->parameters['ShortDescription'] = ($brief) 
            ? $this->trim($brief->getBriefInLimit(Yahoo::BRIEF_LIMIT - 5, "\n")) 
            : '暫無介紹';

        return $this;
    }

    protected function addLongDescription(\stdClass $r)
    {
        $description = $r->product->getDescription();
        $desimg = $r->product->getDesimg();

        $this->parameters['LongDescription'] = ($description) ? $this->trim($description->getContent()) : '暫無介紹';

        return $this;
    }

    protected function addStoreCategoryParams(\stdClass $r)
    {
        if (isset($r->storeCategoryIds)) {
            foreach ($r->storeCategoryIds as $key => $storeCategoryId) {
                $this->parameters['StoreCategoryId_!' . $key] = $storeCategoryId;
            } 
        }

        return $this;
    }

    protected function addPayTypeParams(\stdClass $r)
    {
        foreach ($r->payTypeIds as $key => $payTypeId) {
            $this->parameters['PayTypeId_!' . $key] = $payTypeId;
        } 

        return $this;
    }

    protected function addShippingParams(\stdClass $r)
    {
        foreach ($r->shippingIds as $key => $shippingId) {
            $this->parameters['ShippingId_!' . $key] = $shippingId;
        } 

        return $this;
    }

    protected function addSpecDimension1Params(\stdClass $r)
    {
        if ($color = $r->product->getColor()) {
            $this->parameters['SpecDimension1'] = '顏色';
            $this->parameters['SpecDimension1Description'] = $color->getName();
        }

        return $this;
    }

    protected function addSpecDimension2Params(\stdClass $r)
    {
        if ($pattern = $r->product->getPattern()) {
            $this->parameters['SpecDimension2'] = '款式';
            $this->parameters['SpecDimension2Description'] = $pattern->getName();
        }

        return $this;
    }
}