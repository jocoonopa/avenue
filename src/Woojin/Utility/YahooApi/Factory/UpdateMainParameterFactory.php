<?php

namespace Woojin\Utility\YahooApi\Factory;

class UpdateMainParameterFactory extends SubmitMainParameterFactory
{
    public function create($r)
    {
        $this->parameters = $this->getBaseParameters($r);
        
        $this
            ->addProductId($r)
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

    protected function addProductId(\stdClass $r)
    {
        $this->parameters['ProductId'] = $r->product->getYahooId();

        return $this;
    }
}