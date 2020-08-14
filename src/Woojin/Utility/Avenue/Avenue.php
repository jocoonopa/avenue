<?php

namespace Woojin\Utility\Avenue;

abstract class Avenue
{
    const IS_ERROR                  = 0;
    const IS_SUCCESS                = 1;

    //BRAND order                   : hermes chanel lv gucci paris cloe ysl parada
    const BRAND_HERMES              = 12;
    const BRAND_CHANEL              = 3;
    const BRAND_LV                  = 1;
    const BRAND_GUCCI               = 2;
    const BRAND_PARADA              = 4;
    const BRAND_CHLOE               = 6;
    const BRAND_PARIS               = 7;
    const BRAND_YSL                 = 16;
    const BRAND_OTHER               = 10000;

    //Pattern CategoryGroup         : 包 皮夾 配件 飾品
    const PATTERN_GROUP_BAG         = 1001;
    const PATTERN_GROUP_WALLET      = 1002;
    const PATTERN_GROUP_FIXTURE     = 1003;
    const PATTERN_GROUP_ITEM        = 1004;

    const GS_ONSALE                 = 1;
    const GS_SOLDOUT                = 2;
    const GS_MOVING                 = 3;
    const GS_OFFSALE                = 4;
    const GS_OTHERSTORE             = 5;
    const GS_ACTIVITY               = 6;
    const GS_BEHALF                 = 7;
    const GS_GETBACK                = 8;
    const GS_BSO_ONBOARD            = 9;
    const GS_BSO_SOLD               = 10;

    const OS_HANDLING               = 1;
    const OS_COMPLETE               = 2;
    const OS_CANCEL                 = 3;

    const OK_IN                     = 1;
    const OK_EXCHANGE_IN            = 2;
    const OK_TURN_IN                = 3;
    const OK_MOVE_IN                = 4;
    const OK_CONSIGN_IN             = 5;
    const OK_OUT                    = 6;
    const OK_EXCHANGE_OUT           = 7;
    const OK_TURN_OUT               = 8;
    const OK_MOVE_OUT               = 9;
    const OK_FEEDBACK               = 11;
    const OK_WEB_OUT                = 12;
    const OK_SPECIAL_SELL           = 13;
    const OK_SAME_BS                = 14;
    const OK_OFFICIAL               = 15;

    const OKT_OUT                   = 2;
    const OKT_IN                    = 0;

    const PT_CASH                   = 1;
    const PT_STAFF                  = 68;
    const PT_ATM                    = 81;

    const STORE_STOCK_SN            = '$';
    const STORE_WEBSITE             = 8;
    const STORE_BSO                 = 9;

    const SN_RATE                   = 100;

    const ROLE_CHIEF                = 2;
    const ROLE_SPECIAL_USER         = 5;

    const CUS_NONE                  = 1;
    const MAX_RES                   = 12;
    const START_FROM                = 0;
    const PER_PAGE                  = 10;
    const USER_HIDE                 = 1;
    const RAND_MAX                  = 9999999;
    const RNAD_MIN                  = 1000000;

    const CT_WOMEN                  = 1;
    const CT_MEN                    = 2;
    const CT_SECONDHAND             = 3;

    const USER_IS_ACTIVE            = 1;
    const USER_ENGINEER_ID          = 21;

    const BS_NOT_CONFIRM            = 1; // 客戶
    const BS_FIRST_CONFIRM          = 2; // 第一次確認(確認訂單成立)
    const BS_PAID                   = 3; // 客戶付款
    const BS_SECOND_CONFIRM         = 4; // 第二次確認(確認到款)
    const BS_PURIN                  = 5; // 批貨入庫確認
    const BS_PUROUT                 = 6; // 出貨確認
    const BS_CANCEL                 = 7; // 客戶取消
    const BS_AVENUE_CANCEL          = 8; // 香榭取消(無貨)
    const BS_CHARGE_BACK            = 9; // 退款完成

    const IV_NOT_GET                = 0; // 歐付寶尚未取得訂單資訊
    const IV_GET                    = 1; // 歐付寶取得訂單並且建立
    const IV_GIVE_DONE              = 2; // 出貨完成
    const IV_NOTIFY_CANCEL          = 3; // 客戶通知香榭幫他取消付款
    const IV_CANCEL                 = 4; // 香榭員工在歐付寶後台取消付款
    const IV_BACK_DONE              = 5; // 香榭取回貨物

    const WEB_CHIEF                 = 43; // 官網虛擬店長

    const MV_NOT_CONFIRM            = 1; // 尚未同意
    const MV_CONFIRM                = 2; // 同意
    const MV_COMPLETE               = 3; // 完成
    const MV_CANCEL                 = 4; // 取消
    const MV_REJECT                 = 5; // 拒絕

    const GL_NEW                    = 22;
    const GL_DEMO                   = 23;

    const DESIMG_PIECE_NUM          = 5;
    const DESIMG_1_HEIGHT           = 970;
    const DESIMG_2_HEIGHT           = 1130;
    const DESIMG_3_HEIGHT           = 981;
    const DESIMG_4_HEIGHT           = 787;
    const DESIMG_5_HEIGHT           = 1510;
    const DESIMG_CROP_QA            = 100;

    const IMG_WIDTH                 = 360;

    const USER_BOSS                 = 1;
    const USER_ENG                  = 21;
    const USER_STOCK                = 42;
    const USER_MANAGER              = 3;
    const USER_CHIEF_Z              = 6;
    const USER_CHIEF_Y              = 19;
    const USER_CHIEF_X              = 4;
    const USER_CHIEF_P              = 7;
    const USER_CHIEF_L              = 32;
    const USER_CHIEF_T              = 56;

    const HD_NORMAL                 = 0;
    const HD_OFFICIAL               = 1;
    const HD_EVENT                  = 2;
    const HD_SICK                   = 3;
    const HD_YEAR                   = 4;
    const HD_GLORY                  = 5;
    const HD_LOST                   = 6;
    const HD_PREG                   = 7;
    const HD_COMPANY                = 8;

    const LV_NEW                    = 22;
    const LV_EXHIBIT                = 23;

    const ACTIVITY_CHUNGHSIAO_ID    = 62;
}