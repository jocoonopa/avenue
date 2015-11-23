<?php

namespace Woojin\OrderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WoojinOrderBundle extends Bundle
{
	public function setInvoiceMeta($invoiceRepo, $invoice)
  {   
    $invoiceRepo->id = $invoice->getId();
    $invoiceRepo->sn = $invoice->getSn();
    $invoiceRepo->store = $invoice->getStore()->getStoreName();
    $invoiceRepo->creatAt = $invoice->getCreateAt();
    $invoiceRepo->updateAt = $invoice->getUpdateAt();
    $invoiceRepo->hasPrint = $invoice->getHasPrint();

    return $this;
  }

  public function setInvoiceCustom($customRepo, $custom)
  {
    $customRepo->name = $custom->getName();
    $customRepo->phone = $custom->getMobil();

    return $this;
  }

  public function setInvoiceOrdersGoods($goods, $goodsPassport)
  {
    $goods->brand = $goodsPassport->getBrand()->getName();
            
    $goods->pattern = $goodsPassport->getPattern()->getName();
    
    $goods->model = $goodsPassport->getModel();

    $goods->name = $goodsPassport->getName();
    $goods->sn = $goodsPassport->getSn();
    $goods->orgSn = $goodsPassport->getOrgSn();

    if (is_object($goodsPassport->getGoodsMt())) {
        $goods->material = $goodsPassport->getGoodsMt()->getName();
    }
    
    $goods->level = $goodsPassport->getGoodsLevel()->getName();

    return $this;
  }

  public function setInvoiceOrders( $orderRepo, $order)
  {
    $orderRepo->id = $order->getId();
    $orderRepo->paid = $order->getPaid();
    $orderRepo->required = $order->getRequired();
    $orderRepo->status = $order->getOrdersStatus()->getName();
    $orderRepo->memo = $order->getMemo();

    return $this;
  }
}
