var YahooApiSDK = function (id) {
    this.bindSubmitTrigger().setYahooInfo(id);
};

YahooApiSDK.prototype.bindSubmitTrigger = function () {
    yahooTriggerBind();

    return this;
};

YahooApiSDK.prototype.setYahooInfo = function (id) {
    var self = this;

    $('#yahoo-preload-display').removeClass('hidden');
    $('form[name="yahoo-upload"]').addClass('hidden');

    $.getJSON(Routing.generate('admin_yahoo_api_show', {id: id}), {}, function(json, textStatus) {
        var product = json.Response.Product;
        self.setStatus(product)
            .setPayType(product)
            .setMallCategory(product)
            .setStoreCategory(product)
            .setShipping(product)
        ;

        $('#yahoo-preload-display').addClass('hidden');
        $('form[name="yahoo-upload"]').removeClass('hidden');
    });
};

YahooApiSDK.prototype.setStatus = function (product) {
    var statusName = product.ProductStatus.toLowerCase();
        
    switch (statusName)
    {
        case 'online':
            statusName = '上架中';
            break;

        case 'onlinelowstock':
            statusName = '上架中低庫存';
            break;

        case 'onlinenostock':
            statusName = '上架中無庫存';
            break;

        case 'offline':
            statusName = '已下架';
            break;

        case 'ccblock':
            statusName = '客服下架';
            break;

        case 'delete':
            statusName = '刪除';
            break;

        case 'notonline':
            statusName = '未上架';
            break;

        default:
            break;
    }

    $('#yahoo-status-display').removeClass('hidden').text(statusName);

    $('#yahoo-status-inline').text(statusName);

    return this;
};

YahooApiSDK.prototype.setPayType = function (product) {
    var paytypes = product.PayTypeIdList.PayTypeId;
    var paytypeIds = [];

    for (var i in paytypes) {
        paytypeIds.push(parseInt(paytypes[i].Id));
    }

    $('input[name="yahoo_paymentTypes[]"]').each(function () {
        var val = $(this).val();

        if (paytypeIds.indexOf(parseInt(val)) !== -1) {
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
    });

    return this;
};

YahooApiSDK.prototype.setMallCategory = function (product) {
    var catId = product.MallCategoryId;

    $('#yahoo_categoryId').find('option').each(function () {
        if (catId == $(this).val()) {
            $(this).prop('selected', true);

            return;
        }
    });

    return this;
};

YahooApiSDK.prototype.setStoreCategory = function (product) {
    var storeCategorys = product.StoreCategoryList.StoreCategory;
    var storeCategoryIds = [];

    for (var i in storeCategorys) {
        storeCategoryIds.push(parseInt(storeCategorys[i].Id));
    }

    $('input[name="yahoo_storeCategoryIds[]"]').each(function () {
        var val = $(this).val();
        
        if (storeCategoryIds.indexOf(parseInt(val)) !== -1) {
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
    });

    return this;
};

YahooApiSDK.prototype.setShipping = function (product) {
    var shippings = product.ShippingIdList.ShippingId;
    var shippingIds = [];

    for (var i in shippings) {
        shippingIds.push(parseInt(shippings[i].Id));
    }

    $('input[name="yahoo_shippings[]"]').each(function () {
        var val = $(this).val();
        
        if (shippingIds.indexOf(parseInt(val)) !== -1) {
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
    });

    return this;
};


function yahooTriggerBind() {
    $('#yahoo-upload-submit').click(function () {
    var errorMsg = '';

    if ($('select[name="yahoo_categoryId"]').val() == 0) {
        errorMsg += "請選擇分類方式\n";
    }

    if ($('[name="yahoo_shippings[]"]:checked').length === 0) {
        errorMsg += "請選擇物流方式\n";
    }

    if ($('[name="yahoo_paymentTypes[]"]:checked').length === 0) {
        errorMsg += "請選擇付費方式\n";
    }

    if (errorMsg.length > 1) {
        alert(errorMsg);

        return false;
    } else {
        $(this).prop('disabled', true);
        
        $('form[name="yahoo-upload"]').submit();
    }

    return false;
});
}






