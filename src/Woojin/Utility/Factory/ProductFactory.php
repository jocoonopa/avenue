<?php

namespace Woojin\Utility\Factory;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Collections\ArrayCollection;

class ProductFactory
{
    protected $product;

    protected $storage;

    protected $resolver;

    public function __construct()
    {
        $this->product = null;

        $this->storage = array();

        $this->resolver = new OptionsResolver();

        $this->configureOptions($this->resolver);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'seoWord',
            'orgSn',
            'memo',
            'custom',
            'customSn',
            'seoSlogan',
            'seoSlogan2',
            'brand',
            'level',
            'color',
            'colorSn',
            'model',
            'mt',
            'pattern',
            'source',
            'isAllowWeb',
            'isAllowCreditCard',
            'isBehalf',
            'description',
            'brief',
            'categorys',
            'user',
            'orderStatusHandling',
            'orderStatusComplete',
            'orderKindIn',
            'orderKindConsign',
            'orderKindFeedback',
            'paytype',
            'webPrice',
            'isAlanIn',
            'isAllowAuction',
            'bsoCustomPercentage'
        ));

        $resolver->setRequired(array('amount' ,'price', 'cost', 'name', 'status'));

        return $this;
    }

    public function create(array $options = array())
    {
        $options = $this->resolver->resolve($options);

        for ($i = 0; $i < $options['amount']; $i ++) {
            $product = new GoodsPassport;

            $product
                ->setSeoSlogan($options['seoSlogan'])
                ->setSeoSlogan2($options['seoSlogan2'])
                ->setSeoWord($options['seoWord'])
                ->setStatus($options['status'])
                ->setLevel($options['level'])
                ->setSource($options['source'])
                ->setMt($options['mt'])
                ->setName($options['name'])
                ->setCost($options['cost'])
                ->setPrice($options['price'])
                ->setOrgSn($options['orgSn'])
                ->setMemo($options['memo'])
                ->setColorSn($options['colorSn'])
                ->setColor($options['color'])
                ->setPattern($options['pattern'])
                ->setBrand($options['brand'])
                ->setModel($options['model'])
                ->setCustomSn($options['customSn'])
                ->setCustom($options['custom'])
                ->setDescription($options['description'])
                ->setBrief($options['brief'])
                ->setIsAllowWeb($options['isAllowWeb'])
                ->setIsAllowCreditCard($options['isAllowCreditCard'])
                ->setWebPrice($options['webPrice'])
                ->setIsBehalf($options['isBehalf'])
                ->setIsAlanIn($options['isAlanIn'])
                ->setIsAllowAuction($options['isAllowAuction'])
                ->setBsoCustomPercentage($options['bsoCustomPercentage'])
                ->setParent($product)
            ;

            if (is_array($options['categorys'])) {
                foreach ($options['categorys'] as $category) {
                    $product->addCategory($category);
                }
            }

            $this->storage[] = $product;
        }

        return $this;
    }

    public function _clone(GoodsPassport $product)
    {
        $this->product = new GoodsPassport();
        $this->product
            ->setImg($product->getImg())
            ->setModel($product->getModel())
            ->setColor($product->getColor())
            ->setPattern($product->getPattern())
            ->setColorSn($product->getColorSn())
            ->setBrand($product->getBrand())
            ->setMt($product->getMt())
            ->setStatus($product->getStatus())
            ->setLevel($product->getLevel())
            ->setSource($product->getSource())
            ->setName($product->getName())
            ->setParent($product->getParent())
            ->setCost($product->getCost())
            ->setPrice($product->getPrice())
            ->setOrgSn($product->getOrgSn())
            ->setMemo($product->getMemo())
            ->setCustom($product->getCustom())
            ->setPromotion($product->getPromotion())
            ->setIsBehalf($product->getIsBehalf())

            // 官網新增屬性
            ->setWebPrice($product->getWebPrice())
            ->setDesimg($product->getDesimg())
            ->setBrief($product->getBrief())
            ->setDescription($product->getDescription())
            ->setIsAllowWeb($product->getIsAllowWeb())
            ->setSeoSlogan($product->getSeoSlogan())
            ->setSeoSlogan2($product->getSeoSlogan2())
            ->setIsAllowCreditCard($product->getIsAllowCreditCard())
            ->setYahooId($product->getYahooId())
            ->setIsAlanIn($options['isAlanIn'])
            ->setIsAllowAuction($options['isAllowAuction'])
            ->setBsoCustomPercentage($options['bsoCustomPercentage'])
        ;

        foreach ($product->getCategorys() as $category) {
            $this->product->addCategory($category);
        }

        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function addStorage(GoodsPassport $product)
    {
        $this->storage[] = $product;

        return $this;
    }

    public function setProduct(GoodsPassport $product)
    {
        $this->product = $product;

        return $this;
    }

    public function cleanStorage()
    {
        $this->storage = array();

        return $this;
    }
}
