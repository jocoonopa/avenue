<?php

namespace Woojin\Service\Sculper;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\UserBundle\Entity\AvenueClue;

use Woojin\Service\Sculper\Prototype\ProductModifyPrototype;
use Woojin\Service\Sculper\Prototype\ConsignToPurchasePrototype;
use Woojin\Service\Sculper\Prototype\SoldOutPrototype;
use Woojin\Service\Sculper\Prototype\TurnOutPrototype;
use Woojin\Service\Sculper\Prototype\PurchaseInPrototype;
use Woojin\Service\Sculper\Prototype\ModifyOpeDatetimePrototype;
use Woojin\Service\Sculper\Prototype\CancelSoldOutPrototype;
use Woojin\Service\Sculper\Prototype\CancelPurchaseInPrototype;
use Woojin\Service\Sculper\Prototype\CancelConsignToPurchasePrototype;
use Woojin\Service\Sculper\Prototype\PatchPrototype;
use Woojin\Service\Sculper\Prototype\CustomGetBackPrototype;

use Symfony\Component\Security\Core\SecurityContext;

class ClueSculper
{
    protected $prototype;

    public function getContent()
    {
        return $this->prototype->getContent();
    }

    public function setAfter($entity)
    {
        $this->prototype->setAfterChanged($entity);

        return $this;
    }

    public function setBefore($entity)
    {
        $this->prototype->setBeforeChanged($entity);

        return $this;
    }

    public function initProductModify()
    {
        $this->prototype = new ProductModifyPrototype();

        return $this;
    }

    public function initConsignToPurchase()
    {
        $this->prototype = new ConsignToPurchasePrototype();

        return $this;
    }

    public function initSoldOut()
    {
        $this->prototype = new SoldOutPrototype();

        return $this;
    }

    public function initTurnOut()
    {
        $this->prototype = new TurnOutPrototype();

        return $this;
    }

    public function initPurchaseIn()
    {
        $this->prototype = new PurchaseInPrototype();

        return $this;
    }

    public function initModifyOpeDatetime()
    {
        $this->prototype = new ModifyOpeDatetimePrototype();

        return $this;
    }

    public function initCancelSoldOut()
    {
        $this->prototype = new CancelSoldOutPrototype();

        return $this;
    }

    public function initCancelPurchaseIn()
    {
        $this->prototype = new CancelPurchaseInPrototype();

        return $this;
    }

    public function initCancelConsignToPurchase()
    {
        $this->prototype = new CancelConsignToPurchasePrototype();

        return $this;
    }

    public function initPatch()
    {
        $this->prototype = new PatchPrototype();

        return $this;
    }

    public function initCustomGetBack()
    {
        $this->prototype = new CustomGetBackPrototype();

        return $this;
    }
}