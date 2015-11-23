<?php

namespace Woojin\Utility\YahooApi;

class Yahoo
{
    const MAX_BUY_NUM                   = 1;
    const STOCK                         = 1;
    const SALE_TYPE                     = 'Normal';
    const SAFTY_STOCK                   = 0;
    const SPEC_TYPE_DIMENSION           = 0;
    const YAHOO_API_URL                 = 'http://tw.ews.mall.yahooapis.com/stauth';
    const ERROR_CODE_SIGNATURE_FAIL     = 104;
    const DEFAULT_RETRY_NUM             = 3;
    const ENCODE_ALG                    = 'sha1';
    const YAHOO_ITEM_URL                = 'https://tw.mall.yahoo.com/item';
    const YAHOO_ADMIN_URL               = 'https://tw.user.mall.yahoo.com/store_admin/view/pdbEditMain?product_id=';
    const BRIEF_LIMIT                   = 50;
    const PRODUCT_NAME_LIMIT            = 130;
}
