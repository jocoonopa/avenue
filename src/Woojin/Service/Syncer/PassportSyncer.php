<?php

namespace Woojin\Service\Syncer;

use Doctrine\ORM\EntityManager;

use Woojin\Utility\Avenue\Avenue;
use Woojin\GoodsBundle\Entity\GoodsPassport;

/**
 * @service syncer.passport
 *
 * 同步商品護照的成本,客戶資訊
 */
class PassportSyncer
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * 同步商品護照的客戶，成本資訊，並且更新產編
     * 
     * @param  GoodsPassport $product
     * @return boolean                
     */
    public function sync(GoodsPassport $product)
    {
        $inherits = $product->getInherits();

        $custom = $product->getCustom();
        $price = $product->getPrice();
        $cost = $product->getCost();

        if (empty($inherits)) {
            return false;
        }

        foreach ($inherits as $inherit) {
            if ($product->getId() === $inherit->getId()) {
                continue;
            }

            $inherit
                ->setCustom($custom)
                ->setCost($cost)
                ->setSn($inherit->genSn(substr($inherit->getSn(), 0, 1)))
            ;
            $this->em->persist($inherit);

            $orderIn = $inherit->getOrders()->first();

            if ($orderIn->getKind()->getType() === Avenue::OKT_IN) {
                $orderIn->setRequired($cost);
                $orderIn->setPaid($cost);
            }
            
            $this->em->persist($orderIn);
        }

        $this->em->flush();

        return true;
    }
}