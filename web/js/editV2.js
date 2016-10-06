var loaded = function () {
    var oCss = {
        'border' : 'none',
        'padding' : '15px',
        'backgroundColor' : '#000',
        '-webkit-border-radius' : '10px',
        '-moz-border-radius' : '10px',
        'opacity' : .5,
        'color' : '#fff'
    };

    return $.blockUI({css: oCss, message: '處理中請稍後...'});
};

var goodsApp = function () {
    this.$goButton = $('.go-to-vs-sn');
    this.$numeric = $('.numeric');
    this.$toggleBottom = $('.toggle-goods-form');
    this.$formGoods = $('form#goods');
    this.$goodsPrice = this.$formGoods.find('input[name="goods_price"]');
    this.$webPrice = this.$formGoods.find('input[name="web_price"]');
    this.$brand = this.$formGoods.find('select[name="brand"]');

    CKEDITOR.replace('description');

    this.init();
};

goodsApp.prototype.init = function () {
    this.webCrossPrice().snHref().toggleGoodsForm().numeric().requireRelateEntity().updateIsBehalf();
};

goodsApp.prototype.webCrossPrice = function () {
    var self = this;

    self.$brand.change(function () {
        self.$webPrice.val(parseInt(self.$goodsPrice.val() * self.$brand.find('option:selected').data('ratio')));
    });

    self.$goodsPrice.keyup(function () {
        self.$webPrice.val(parseInt(self.$goodsPrice.val() * self.$brand.find('option:selected').data('ratio')));
    });

    return this;
};

goodsApp.prototype.snHref = function () {
    this.$goButton.click(function () {
        window.location.href = Routing.generate('goods_edit_v2_from_sn', {'sn': $('input[name="v2_sn"]').val()});
    });

    return this;
};

goodsApp.prototype.toggleGoodsForm = function () {
    var self = this;

    this.$toggleBottom.click(function () {
        self.$formGoods.toggle();
    });

    return this;
};

goodsApp.prototype.numeric = function () {
    this.$numeric.numeric();

    return this;
};

goodsApp.prototype.requireRelateEntity = function () {
    var iOrdersUpdateForm = new ordersUpdateForm();
    var iCancelOrder = new cancelOrder();
    var iChangeCustom = new changeCustom();
    var iImgHtmlReader = new imgHtmlReader($('input[name="img"]'), $('img.select-image'));
    var iImgHtmlReader = new imgHtmlReader($('input[name="desimg"]'), $('img.select-desimage'));
    var iOpeDatetimeUpdate = new opeDatetimeUpdate();
    var iMoveOut = new moveOut();

    return this;
};

goodsApp.prototype.updateIsBehalf = function () {
    $('input[name="isBehalf"]').click(function () {
        var $this = $(this);

        loaded();

        return $.ajax({
            url: Routing.generate('admin_goods_toggle_isbehalf', {id: $this.data('id')}),
            type: 'PUT',
            data: {isBehalf: ($this.prop('checked') ? 1 : 0) },
            success: function (res) {
                console.log(res);

                $.unblockUI();
            }
        });
    });

    return this;
};

var moveOut = function () {
    this.$div = $('div.move-out');
    this.$button = this.$div.find('button');
    this.$input = this.$div.find('input');

    this.init();
};

moveOut.prototype.init = function () {
    var self = this;

    this.$button.click(function () {console.log('fff');
        loaded();
        self.move();
    });
};

moveOut.prototype.move = function () {
    var self = this;

    $.post(Routing.generate('order_backOrder_request'),
        {
            nGoodsPassportId: self.$button.data('id'),
            sStoreSn: self.$input.val()
        })
        .success(function (res) {
            $.unblockUI();

            location.reload();
        })
        .error(function () {
            $.unblockUI();

            alert('ajax error');
        });
};

var opeDatetimeUpdate = function () {
    this.$input = $('tr._ope_').find('input[name="ope_datetime"]');

    this.init();
};

opeDatetimeUpdate.prototype.init = function () {
    var self = this;

    this.$input.datetimepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss',
        onSelect: function () {
            $this = $(this);
            loaded();
            self.update($this);
        }
    });
};

opeDatetimeUpdate.prototype.update = function ($e) {
    $.ajax({
        url: Routing.generate('update_ope_datetime', {id: $e.data('id')}),
        type: 'PUT',
        data: {update_at: $e.val()},
        success: function(res) {
            $.unblockUI();
        },
        error: function () {
            $.unblockUI();
            alert('ajax error');
        }
    });
};

var imgHtmlReader = function ($f, $img) {
    this.$file = $f;
    this.$image = $img;

    this.init();
};

imgHtmlReader.prototype.init = function () {
    var self = this;

    this.$file.change(function () {
        if (!this.files) {
            return false;
        }

        var file = this.files[0];
        var reader = new FileReader();

        reader.onload = function (e) {
            self.$image.attr('src', e.target.result);
        };     

        reader.readAsDataURL(file);
    });
};


var changeCustom = function () {
    this.$li = $('li.custom-alter');
    this.$button = this.$li.find('button.custom-mobil-update');
    this.$input = this.$li.find('input[name="custom_mobil"]');
    this.$span = this.$li.find('span');

    this.init();
};

changeCustom.prototype.init = function () {
    var self = this;

    return this.$button.on('click', function () {
        var $button = $(this).prev();

        if ($button.val().length < 10 && $button.val() != '00000') {
            alert('電話長度不足!');

            return false;
        }

        //self.$button.button('loading');
        self.requestToChange($(this), $button.val());
    });
};

changeCustom.prototype.requestToChange = function ($e, mobil) {
    var self = this;

    loaded();

    return $.post(Routing.generate('orders_v2_custom_alter', {id: $e.data('id'), mobil: mobil}))
        .success(function (res) {
            var data = JSON.parse(res);

            //self.$button.button('complete');

            //self.updateCustomInfo(data.custom);

            $.unblockUI();

            window.location.reload();
        })
        .error(function (e) {
            alert('更改客戶失敗!');

            $.unblockUI();

            //self.$button.button('reset');
        });
};

// changeCustom.prototype.updateCustomInfo = function (custom) {
//     // this.$span.removeClass('label-warning').addClass('label-info').text(custom.name);
//     // this.$input.val(custom.mobil);
//     //this.$button.prop('disabled', true);

//     return this;
// };

var cancelOrder = function () {
    this.$button = $('button.cancel-order');
    this.$inverseButton = $('button.inverse-order');
    this.$priceInput = $('input[name="inverse-price"]');
    this.$reverseButton = $('button.reverse-cancel');

    this.init();
};

cancelOrder.prototype.init = function () {
    var self = this;

    this.$priceInput.numeric();

    this.$button.click(function () {
        if (!confirm('確認取消此訂單嘛?')) {
            return false;
        }

        loaded();
        $.post(Routing.generate('orders_v2_cancel', {id: self.$button.data('id')}))
            .success(function () {
                $.unblockUI();

                location.reload();
            })
            .error(function () {
                $.unblockUI();
                alert('something error!!');
            });
    });

    this.$inverseButton.click(function () {
        if (!confirm('確認將寄賣訂單轉換為一般進貨訂單嘛?')) {
            return false;
        }

        loaded();
        $.post(Routing.generate('order_inverse_to_purchase', {
            id: self.$inverseButton.data('id')
        }), {price: self.$priceInput.val()})
            .success(function () {
                $.unblockUI();

                location.reload();
            })
            .error(function () {
                $.unblockUI();
                alert('something error!!');
            });
    });

    this.$reverseButton.click(function () {
        if (!confirm('確認還原訂單嘛?')) {
            return false;
        }

        loaded();
        $.post(Routing.generate('order_reverse_cancel', {
            id: self.$reverseButton.data('id')
        })).success(function () {
                $.unblockUI();

                location.reload();
            })
            .error(function () {
                $.unblockUI();
                alert('something error!!');
            });
    });
};

var ordersUpdateForm = function () {
    this.$form = $('form.orders-update');
    this.$paid = this.$form.find('input[name="paid"]');
    this.$paidOrg = this.$form.find('input[name="paid_org"]');
    this.$remain = this.$form.find('input[name="remain"]');
    this.$payType = this.$form.find('select[name="pay_type"]');
    this.$button = this.$form.find('button');

    this.buttonInit();
    this.formInit();
    this.changePayType().changePaidOrg();
};

ordersUpdateForm.prototype.buttonInit = function () {
    var self = this;

    return this.$button.click(function () {
        loaded();

        self.$form.submit();

        return false;
    });
};

ordersUpdateForm.prototype.formInit = function () {
    return this.$form.ajaxForm({
        error: function () {
            alert('ajax error');
        },
        success: function () {
            $.unblockUI();

            location.reload();
        }
    });
};

ordersUpdateForm.prototype.changePayType = function () {
    var self = this;

    this.$payType.change(function(event) {
        self.setPaid();
    });

    return this;
};

ordersUpdateForm.prototype.changePaidOrg = function () {
    var self = this;

    this.$paidOrg.change(function () {
        if (parseInt(self.$paidOrg.val()) > parseInt(self.$remain.val())) {
            self.$paidOrg.val(parseInt(self.$remain.val()));
        }

        self.setPaid();
    });

    return this;
};

ordersUpdateForm.prototype.getDiscount = function () {
    return parseFloat(this.$payType.find('option:selected').data('discount'));
};

ordersUpdateForm.prototype.setPaid = function () {
    var discount = this.getDiscount();
    var paidOrg = parseInt(this.$paidOrg.val());

    this.$paid.val(Math.round(discount * paidOrg));

    return this;
};

$(function () {
    var iGoodsApp = new goodsApp();

    $('#auction_sold_at').find('input[name="sold_at"]').datetimepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss'
    });

    $('#isAllowAuction').change(function() {
        $('#bso_custom_percentage').prop('readonly', !$(this).prop('checked'));
    }).change();
});
