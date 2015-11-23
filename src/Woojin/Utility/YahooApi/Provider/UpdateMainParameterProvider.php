<?php

namespace Woojin\Utility\YahooApi\Provider;

class UpdateMainParameterProvider
{
    protected $client;
    protected $r;

    public function __construct(\Woojin\Utility\YahooApi\Client $client)
    {
        $this->client = $client;
    }

    public function genR(\Woojin\GoodsBundle\Entity\GoodsPassport $product)
    {
        $response = $this->client->getMain($product);

        $yahooProduct = $response->Response->Product;

        $this
            ->setR()
            ->addProduct($product)
            ->addMallCategorId($yahooProduct)
            ->addStoreCategoryIds($yahooProduct)
            ->addPayTypeIds($yahooProduct)
            ->addShippingIds($yahooProduct)
        ;

        return $this;
    }

    public function getR()
    {
        return $this->r;
    }

    public function setR()
    {
        $this->r = new \stdClass();

        return $this;
    }

    protected function addProduct($product)
    {
        $this->r->product = $product;

        return $this;
    }

    protected function addMallCategorId(\stdClass $yahooProduct)
    {
        $this->r->mallCategoryId = $yahooProduct->MallCategoryId;

        return $this;
    }

    protected function addStoreCategoryIds(\stdClass $yahooProduct)
    {
        if (isset($yahooProduct->StoreCategoryList) 
            && isset($yahooProduct->StoreCategoryList->StoreCategory)
        ) {
            $this->r->storeCategoryIds = array();

            foreach ($yahooProduct->StoreCategoryList->StoreCategory as $storeCategory) {
                $this->r->storeCategoryIds[] = $storeCategory->Id;
            }
        }

        return $this;
    }

    protected function addPayTypeIds(\stdClass $yahooProduct)
    {
        if (isset($yahooProduct->PayTypeIdList) && isset($yahooProduct->PayTypeIdList->PayTypeId)) {
            $this->r->payTypeIds = array();

            foreach ($yahooProduct->PayTypeIdList->PayTypeId as $payTypeId) {
                $this->r->payTypeIds[] = $payTypeId->Id;
            }
        }

        return $this;
    }

    protected function addShippingIds(\stdClass $yahooProduct)
    {
        if (isset($yahooProduct->ShippingIdList) && isset($yahooProduct->ShippingIdList->ShippingId)) {
            $this->r->shippingIds = array();

            foreach ($yahooProduct->ShippingIdList->ShippingId as $shippingId) {
                $this->r->shippingIds[] = $shippingId->Id;
            }
        }

        return $this;
    }
}