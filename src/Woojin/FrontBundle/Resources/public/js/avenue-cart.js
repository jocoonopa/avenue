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
window.AvenueCart = function () {
    var self = this;

    self.removeIconClass = 'fa fa-hand-o-right';
    self.checkedMsg = '結帳去';

    /**
     * 一定要加這行，否則你的 cookie 一旦跑到別的路徑就會消失...
     * 
     * @type {String}
     */
    $.cookie.defaults.path = '/';

    if (typeof $.cookie('avenueCart') == 'undefined') {
        $.cookie('avenueCart', JSON.stringify([]));
    }

    $(document).on('click', 'a.add-to-cart', function () {
        self.add($(this).data('id'));

        if (!$(this).hasClass('taged')) {
            $(this).html('處理中...');
        }

        return false;
    });

    $(document).on('click', 'span.add-to-cart', function () {
        self.add($(this).data('id'));

        if (!$(this).hasClass('taged')) {
            $(this).removeClass('icon-basket').html('處理中...');
        }
    });

    $(document).on('click', '#scrollbar button.remove-from-cart', function () {
        self.remove($(this).data('id'));

        return false;
    });

    $(document).on('click', 'button.trigger-remove-from-cart', function () {
        window.myBlockUI();

        var id = $(this).data('id');

        self.remove(id);

        $(this).closest('tr').remove();

        return false;
    });

    $(document).on('click', 'button.cart-to-whishlist', function () {
        window.myBlockUI();

        var id = $(this).data('id');

        window.awl._add($(this));

        self.remove(id);

        $(this).closest('tr').remove();

        return false;
    });

    $(document).on('click', 'a.add-to-cart.taged', function () {
        return window.location.href = $(this).attr('href');
    });

    $(document).on('click', 'a.behalf-request', function () {
        var $this = $(this);
        var id = $this.data('id');
        // open modal
        // replace href {id}
        // Send request
        // ----------------------------
        // backend: new a Behalf entity
        // -> message to chief
        
        if ($('a.behalf-login-redirect').length > 0) {
            $('a.behalf-login-redirect').attr('href', Routing.generate('front_custom_login_behalf', {id: id}));
        } else {
            $('#behalfRequest').find('.product-name').text($this.data('name'));
            $('#behalfRequest form[name="behalf"]').find('input[name="product_id"]').val(id);
        }

        $('#behalfRequest').modal('show');
    });

    $('.empty-avenue-cart').click(function () {
        if (confirm('確定要清空購物車嘛?')) {
            $.cookie('avenueCart', JSON.stringify([]));

            self.reflect();
        }
        
        return false;
    });

    if ($('#behalfRequest').find('form').length > 0) {
        this.initBehalfValidate($('#behalfRequest').find('form'));
    }
    
    self.reflect();
};

AvenueCart.prototype.initBehalfValidate = function ($form) {
    $form.validate({
        errorPlacement: function errorPlacement(error, element) {  
            error.insertBefore(element);
        },
        rules: {
            phone: {
                required: true,
                rangelength:[8, 13]
            }
        },
        messages: {
            phone: {
                required: '電話不可空白',
                rangelength: '電話長度不合法'
            }
        }
    });

    $form.find('input[name="phone"]').numeric({ negative: false });
};

/**
 * 取得目前購物車的商品數量
 * 
 * @return {integer}
 */
AvenueCart.prototype.getItemsCount = function () {
    var jProductIds = $.cookie('avenueCart');
    var ids = JSON.parse(jProductIds);

    return ids.length;
};

/**
 * Model 反射 View 顯示   
 */
AvenueCart.prototype.reflect = function () {
    var productIds = $.cookie('avenueCart');

    return (typeof productIds == 'undefined' || productIds.length === 0)
        ? this.render([])
        : this.getProductsFromServer(productIds)
    ;
};

AvenueCart.prototype.render = function (arr) {
    this.emptyView()
        .renderList(arr)
        .renderSubtotal(arr)
        // 標記畫面上的商品
        .tagEachProduct(arr)
        .removeTag()
        // 結帳頁移除購物車商品
        //.removeItemAtCheckout(arr)
        .calculateCheckoutTotal(arr)
        // 結帳頁商品數量 >- or == 0 反射
        .reflectByhasItemsOrNot(arr)
        .reflectByhasItemsOrNotInCartWidget(arr)
    ;

    $.unblockUI();

    return this;
};

/**
 * 顯示結帳購物車總價
 * 
 * @param  {array} products        
 */
AvenueCart.prototype.calculateCheckoutTotal = function (products) {
    var total = formatNumber(this.countSubtotal(products));

    $('.total-result-in').not('.shipping-payment').find('span').html('NT. ' + total);

    return this;
};

/**
 * 結帳頁面根據購物車有無商品反應
 * 
 * @param  {array}  products         
 */
AvenueCart.prototype.reflectByhasItemsOrNot = function (products) {
    if (products.length === 0) {
        $('#product-container').html($('#cart-empty-msg').html());
    }

    return this;
};

/**
 * header 購物車 widget 根據有無商品作不同顯示
 * 
 * @param  {array}  products    
 */
AvenueCart.prototype.reflectByhasItemsOrNotInCartWidget = function (products) {
    if (products.length === 0) {
        $('.empty-cart-msg').removeClass('hidden');
        $('.subtotal').addClass('hidden');
    } else {
        $('.empty-cart-msg').addClass('hidden');
        $('.subtotal').removeClass('hidden');
    }

    return this;
};

/**
 * 清空 Dropdown 購物車畫面
 */
AvenueCart.prototype.emptyView = function () {
    $('#scrollbar').find('li').not('.subtotal, .loading , .empty-cart-msg').remove();

    return this;
};

/**
 * 用 productIds 向 api 取得 products，資料結構如下:
 *
 * [item1, item2, ..., itemEnd]
 * item: {// 請參考GoodsPassportEntity}
 * 
 */
AvenueCart.prototype.getProductsFromServer = function (productIds) {
    var self = this;

    self.loadingStartAnimate();

    return $.getJSON(Routing.generate('api_goodsPassport_multishow', {jIds: productIds}), function (data) {
        self.loadingCompleteAnimate();

        return self.render(data);
    })
    .fail(function (jqxhr, textStatus, error) {
        var err = textStatus + ", " + error;

        self.loadingCompleteAnimate();
        
        console.log( "Request Failed: " + err );
    });
};

AvenueCart.prototype.loadingStartAnimate = function () {
    $('.shop-badge li.item').addClass('hidden');
    $('.shop-badge .loading').removeClass('hidden');
};

AvenueCart.prototype.loadingCompleteAnimate = function () {
    $('.shop-badge .loading').addClass('hidden');
    $('.shop-badge li.item').removeClass('hidden');
};

/**
 * 產生購物車清單
 */
AvenueCart.prototype.renderList = function (products) {
    for (var i = 0; i < products.length; i ++) {
        this.renderEachProduct(products[i]);
    }

    return this;
};

AvenueCart.prototype.renderEachProduct = function (product) {
    var $li = $($('#cart-item').html());

    if (product.img) {
        $li.find('img').attr('src', product.img.path).attr('alt', product.name);
    }
    
    $li.find('a.product-name').text(product.name);
    $li.find('a').attr('href', Routing.generate('front_product_show', {id: product.id}));
    $li.find('.close').attr('data-id', product.id);

    var promotionPrice = window.getPromotionPrice(product);
    
    $li.find('small').text('NT. ' + formatNumber(product.price));
    
    if (promotionPrice) {   
        $li.find('small').css({'text-decoration': 'line-through', 'color': '#d9534f', 'display': 'block'});
        $li.find('.overflow-h').append($('<small>NT. ' + formatNumber(promotionPrice) + ' </small>'));
    }

    $('#scrollbar').prepend($li);
};

/**
 * render bottom view[總計金額]
 */
AvenueCart.prototype.renderSubtotal = function (products) {
    $('#cart-cost').text('NT. ' + formatNumber(this.countSubtotal(products)));
    $('.cart-count').text(products.length);

    return this;
};

AvenueCart.prototype.countSubtotal = function (products) {
    var subtotal = 0;
    var proPrice;

    for (var i = 0; i < products.length; i ++) {
        proPrice = window.getPromotionPrice(products[i]);

        if (proPrice) {
            subtotal = subtotal + parseInt(proPrice);
        } else {
            subtotal = subtotal + parseInt(products[i].price);
        }
    }

    return subtotal;
};

/**
 * 將商品id加入 cookie
 */
AvenueCart.prototype.addItemToCookie = function (id) {
    var productIds;

    if (typeof $.cookie('avenueCart') == 'undefined') {
        $.cookie('avenueCart', '[]');
    }
    
    productIds = JSON.parse($.cookie('avenueCart'));
    productIds.unshift(id);

    $.cookie('avenueCart', JSON.stringify(productIds));

    return this;
};

AvenueCart.prototype.add = function (id) {
    var jsonProductIds = $.cookie('avenueCart');
    var productIds = JSON.parse(jsonProductIds);

    if (productIds.indexOf(id) !== -1) {
        return false;
    }

    return this.addItemToCookie(id).reflect();
};

AvenueCart.prototype.removeItemFromCookie = function (id) {
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

AvenueCart.prototype.remove = function (id) {
    return this.removeItemFromCookie(id).reflect();
};

/**
 * 將加入購物車的商品做標記
 */
AvenueCart.prototype.tagEachProduct = function (products) {
    var self = this;

    for (var i = 0; i < products.length; i ++) {
        $('a.add-to-cart[data-id="' + products[i].id + '"]').not('.taged')
            .addClass('taged')
            .html('<span class="' + self.removeIconClass + '"></span>' + self.checkedMsg)
            .attr('href', Routing.generate('front_custom_login', {routeName: 'front_payment_checkout'}));

        $('span.add-to-cart[data-id="' + products[i].id + '"]').not('.taged').addClass('taged ' + self.removeIconClass).removeClass('icon-basket').empty();
    }

    return this;
};

/**
 * 移除購物車商品同時移除標記
 */
AvenueCart.prototype.removeTag = function () {
    var jsonProductIds = $.cookie('avenueCart');
    var productIds = JSON.parse(jsonProductIds);
    
    $('a.add-to-cart.taged').each(function () {
        if (productIds.indexOf($(this).data('id')) === -1) {
            $(this).removeClass('taged').html('<span class="icon-basket"></span>未選購').attr('href', 'javascript: void(0);');
        }
    });

    $('span.add-to-cart.taged').each(function () {
        if (productIds.indexOf($(this).data('id')) === -1) {
            $(this).removeClass('taged ' + self.removeIconClass).addClass('taged icon-basket');
        }
    });

    return this;
};