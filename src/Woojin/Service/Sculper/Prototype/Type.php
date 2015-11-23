<?php

namespace Woojin\Service\Sculper\Prototype;

abstract class Type
{
    const CANCEL_CONSIGN_TO_PURCHASE    = '取消寄賣轉收購';
    const CANCEL_PURCHASE_IN            = '取消進貨';
    const CANCEL_SOLDOUT                = '取銷售出';
    const CONSIGN_TO_PURCHASE           = '寄賣轉收購';
    const MODIFY_OPE_DATETIME           = '修改售出時間';
    const PATCH                         = '補款';
    const PRODUCT_MODIFY                = '商品修改';
    const PURCHASE_IN                   = '進貨';
    const SOLDOUT                       = '售出';
    const TURNOUT                       = '換購';
    const CUSTOM_GETBACK                = '客寄取回';
}
