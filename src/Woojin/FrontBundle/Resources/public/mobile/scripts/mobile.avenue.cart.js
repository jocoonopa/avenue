var $ = jQuery;
/**
 * 購物車相關 jQuery 
 *
 * 代購: 
 * ---------------------------------------
 * 1. 文字顯示為<代購>，點擊後要求先登入會員，跳出 moDal 告知
 * 代購相關注意事項。
 *
 * 2. <<我的收藏>>那邊也要改。
 *
 * 3. 代購一單一個東西。
 *
 * 4. 確認前都還能取消。
 */
window.MobileAvenueCart = function () {
    var self = this;

    self.$addButton = $('#MBP_add-to-cart');
    self.$payButton = $('#MBP_go-to-pay');
    self.$removeButtons = $('.MBP_remove-from-cart');
    self.id = parseInt(self.$addButton.data('id'));
    self.message = $('.MBP_success-message');
    self.$cart = $('#MBH_shopping-cart');
    self.$sum = $('#MBP_total-amount');
    self.$emptyShow = $('.MBC_empty-show');
    self.$emptyHide = $('.MBC_empty-hide');

    /**
     * 一定要加這行，否則你的 cookie 一旦跑到別的路徑就會消失...
     * 
     * @type {String}
     */
    $.cookie.defaults.path = '/';

    if (typeof $.cookie('avenueCart') == 'undefined') {
        $.cookie('avenueCart', JSON.stringify([]));
    }

    this.cartLengthUpdate();

    if (self.isInbag()) {
        self.viewReflect();
    }

    self.$addButton.click(function () {
        if ($(this).hasClass('has_clicked')) {
            window.location.href = $(this).attr('href');

            return false;
        }

        self.add().viewReflect().cartLengthUpdate();
    });

    self.$removeButtons.click(function () {
        var id = $(this).data('id');

        self._remove(id).removeViewReflect(id).updateCartSum().toggleComponent();
    });

    self.toggleComponent();
};

MobileAvenueCart.prototype.viewReflect = function () {
    //this.$addButton.addClass('has_clicked').html('<i class="fa fa-check"></i>結帳');
    //this.$addButton.attr('href', Routing.generate('mobile_front_payment'));
    this.$payButton.removeClass('hidden');
    this.$addButton.addClass('hidden');
    this.message.removeClass('hidden');

    return this;
};

MobileAvenueCart.prototype.cartLengthUpdate = function () {
    var jsonProductIds = $.cookie('avenueCart');
    var productIds = JSON.parse(jsonProductIds);

    this.$cart.text(productIds.length);

    return this;
};

MobileAvenueCart.prototype.add = function () {
    if (this.isInbag()) {
        return this;
    }

    return this.push();
};

/**
 * 將商品id加入 cookie
 */
MobileAvenueCart.prototype.push = function () {
    var productIds;

    if (typeof $.cookie('avenueCart') == 'undefined') {
        $.cookie('avenueCart', '[]');
    }
    
    if (this.id) {
        productIds = JSON.parse($.cookie('avenueCart'));
        productIds.unshift(this.id);

        $.cookie('avenueCart', JSON.stringify(productIds));
    }
    
    return this;
};

/**
 * 將商品id加入 cookie
 */
MobileAvenueCart.prototype._remove = function (id) {
    var jsonProductIds = $.cookie('avenueCart');
    var productIds = JSON.parse(jsonProductIds);
    var index = productIds.indexOf(id);

    if (index === -1) {
        return this;
    }

    productIds.splice(index, 1);
    
    $.cookie('avenueCart', JSON.stringify(productIds));

    return this;
};

MobileAvenueCart.prototype.removeViewReflect = function (id) {
    var self = this;

    $('.MBC_product-in-cart-' + id).remove();

    self.$removeButtons = $('.MBP_remove-from-cart');

    return this;
};

MobileAvenueCart.prototype.updateCartSum = function () {
    var self = this;
    var sum = 0;

    self.$removeButtons.each(function () {
        sum += parseInt($(this).data('price'));
    });

    self.$sum.text(sum);

    return this;
};

MobileAvenueCart.prototype.toggleComponent = function () {
    var self = this;

    if (self.$removeButtons.length === 0) {
        self.$emptyShow.removeClass('hidden');
        self.$emptyHide.addClass('hidden');
    } else {
        self.$emptyShow.addClass('hidden');
        self.$emptyHide.removeClass('hidden');
    }
    
    return this;
};

MobileAvenueCart.prototype.isInbag = function () {
    var jsonProductIds = $.cookie('avenueCart');
    var productIds = JSON.parse(jsonProductIds);

    return (productIds.indexOf(this.id) !== -1);
};
