/* Write here your custom javascript codes */
Date.prototype.dateDiff = function (interval, objDate) {
    var dtEnd = new Date(objDate);
    
    if (isNaN(dtEnd)) {
        return undefined;
    }

    switch (interval) {
        case 's':
            return parseInt((dtEnd - this) / 1000);
        case 'n':
            return parseInt((dtEnd - this) / 60000);
        case 'h':
            return parseInt((dtEnd - this) / 3600000);
        case 'd':
            return parseInt((dtEnd - this) / 86400000);
        case 'w':
            return parseInt((dtEnd - this) / (86400000 * 7));
        case 'm':
            return (dtEnd.getMonth()+1)+((dtEnd.getFullYear()-this.getFullYear())*12) - (this.getMonth()+1);
        case 'y':
            return dtEnd.getFullYear() - this.getFullYear();
    }
};

/**
 * 取得官網顯示價格
 * 
 * ======== flow =========
 *
 * 如果不存在活動，返回 null
 * 
 * 如果不允許網路顯示，直接返回 null
 *
 * 如果網路售價合法[大於等於 100，且小於原始售價]
 *     返回網路售價
 *
 * 存在活動且活動折扣 < 1
 *     返回原始售價 * 活動折扣金額
 *
 * 返回 null
 * 
 * ======= End Flow =======
 *     
 * @return integer | boolean
 */
window.getPromotionPrice = function (goods, isInt) {
    if (!!!goods.is_allow_web) {
        return null;
    }

    if (goods.product_tl && goods.product_tl.price >= 100) {
        var now = new Date();
        var stopAt = new Date(goods.product_tl.end_at);
        
        if (parseInt(now.dateDiff('d', stopAt)) >= 0) {
            return goods.product_tl.price;
        }
    }

    if (goods.promotion && isValid(goods.promotion)) {
        var displayPrice = ((goods.web_price && goods.web_price >= 100) ? goods.web_price : goods.price);

        if (goods.promotion.gift > 0) {
            // 售價若大於滿額贈門檻
            if (displayPrice >= goods.promotion.thread) {
                return (goods.promotion.is_stack == 1) // 累計模式 或是單次模式
                    ? displayPrice - (goods.promotion.gift * floor(displayPrice/goods.promotion.thread))
                    : displayPrice - goods.promotion.gift
                ;
            }
        }
        //return parseInt(goods.price * goods.promotion.discount);
    }

    if (goods.web_price && goods.web_price >= 100) {
        return parseInt(goods.web_price);
    }

    return isInt ? parseInt(goods.price) : null;
};

window.getSeoName = function (goods) {
    var seoName = '';

    seoName += '[香榭國際精品]';

    if (goods.brand) {
        seoName += goods.brand.name + ' ';
    }

    if (goods.seo_slogan) {
        seoName += goods.seo_slogan.name + ' ';
    }

    if (goods.seo_word) {
        seoName += goods.seo_word + ' ';
    }

    seoName += goods.model + ' ';

    seoName += goods.name;

    return seoName;
};

window.isValid = function (promotion) {
    if (!promotion) {
        return false;
    }

    var now = new Date();

    var startAt =  new Date(promotion.start_at);
    if (parseInt(now.dateDiff('d', startAt)) > 0) {
        return false;
    }
    
    var stopAt = new Date(promotion.stop_at);
    if (parseInt(now.dateDiff('d', stopAt)) < 0) {
        return false;
    }

    return true;
};

window.formatNumber = function (num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
};

window.myBlockUI = function () {
    var oCss = { 
        'border' : 'none',
        'padding' : '15px',
        'backgroundColor' : '#000',
        '-webkit-border-radius' : '10px',
        '-moz-border-radius' : '10px',
        'opacity' : .5,
        'color' : '#fff'
    };
    
    return $.blockUI({ 
        css: oCss,
        message: '請稍後...'
    }); 
};