/**
 * 購物車相關 jQuery 
 *
 * Bug!!!!!! 為何位trigger 兩次啊????
 */
window.AvenueWhishlist = function () {
    var self = this;

    self.initMsg = '未收藏';
    self.handlingMsg = '處理中...';
    self.collectMsg = '已收藏';
    self.errorMsg = '請登入';

    /**
     * 一定要加這行，否則你的 cookie 一旦跑到別的路徑就會消失...
     * 
     * @type {String}
     */
    $.cookie.defaults.path = '/';   

    $.getJSON(Routing.generate('api_custom_whishlist'), function(whishlist) {
        $.cookie('avenueWhishlist', whishlist);

        self.init();
    });

    $(document).on('click', 'a.add-to-whishlist', function () {
        if ($(this).hasClass('handling')) {
            return false;
        }
        
        self._add($(this));

        self.handling($(this));
        
        return false;
    });

    $(document).on('click', 'a.remove-from-whishlist', function () {
        if ($(this).hasClass('handling')) {
            return false;
        }

        self._remove($(this));

        self.handling($(this));
        
        return false;
    });    

    $(document).on('click', 'button.remove-from-whishlist', function () {
        window.myBlockUI();

        self.removeAtProfile($(this));
    });

    $(document).on('click', 'button.whishlist-to-cart', function () {
        window.myBlockUI();

        window.ac.add($(this).data('id'));

        self.removeAtProfile($(this));
    });

    self.displayEmptyMsg();
};

AvenueWhishlist.prototype.displayEmptyMsg = function () {
    if ($('#whishlist-frame').find('table tr').length === 0) {
        $('#whishlist-frame').html($('#whishlist-empty-msg').html());
    }
};

AvenueWhishlist.prototype.removeAtProfile = function ($e) {
    var self = this;

    $.ajax({
        url: Routing.generate('api_custom_whishlist_remove'), 
        type: 'PUT',
        dataType: 'json',
        data: {
            product_id: $e.data('id')
        },
        success: function (res) {
            if (parseInt(res.status) === 1) {// 成功
                $e.closest('tr').remove();

                self.displayEmptyMsg();
            } else {// 失敗...
                alert('移除願望清單失敗');
            }

            return $.unblockUI();
        },
        error: function () {
            alert('移除願望清單失敗，請稍後再試');

            return $.unblockUI();
        }
    });
};

AvenueWhishlist.prototype.handling = function ($e) {
    var self = this;

    $e.addClass('handling');

    $e.html(self.handlingMsg);
};

/**
 * Model 反射 View 顯示   
 */
AvenueWhishlist.prototype.init = function () {
    var listIds = $.cookie('avenueWhishlist') || [];
    var self = this;

    $('.add-to-whishlist').each(function () {
        var $e = $(this);

        if (listIds.indexOf($e.data('id')) !== -1) {
            self.toWillBeCollectedStatus($e);
        }
    });
};

/**
 * 加入願望清單  
 *
 * @param {jQuery element} $e [a tag]
 */
AvenueWhishlist.prototype._add = function ($e) {
    var self = this;

    $.ajax({
        url: Routing.generate('api_custom_whishlist_add'), 
        type: 'PUT',
        dataType: 'json',
        data: {
            product_id: $e.data('id')
        },
        success: function (res) {
            if (parseInt(res.status) === 1) {// 成功
                $.cookie('avenueWhishlist', JSON.stringify(res.data));

                self.toWillBeCollectedStatus($e);
            } else {// 失敗
                self.toWillNotBeCollectedStatus($e, true);
            }
        },
        error: function () {
            self.toWillBeCollectedStatus($e, true);
        }
    });
};

/**
 * 移出願望清單  
 *
 * @param {jQuery element} $e [a tag]
 */
AvenueWhishlist.prototype._remove = function ($e) {
    var self = this;

    $.ajax({
        url: Routing.generate('api_custom_whishlist_remove'), 
        type: 'PUT',
        dataType: 'json',
        data: {
            product_id: $e.data('id')
        },
        success: function (res) {
            if (parseInt(res.status) === 1) {// 成功
                $.cookie('avenueWhishlist',  JSON.stringify(res.data));

                self.toWillNotBeCollectedStatus($e);
            } else {// 失敗
                self.toWillBeCollectedStatus($e, true);
            }
        },
        error: function () {
            self.toWillBeCollectedStatus($e, true);
        }
    });
};

AvenueWhishlist.prototype.toWillBeCollectedStatus = function ($e, isFailed) {
    var isFailed = isFailed || null;

    if (!isFailed) {
        $e.removeClass('add-to-whishlist handling').addClass('remove-from-whishlist');

        if ($e.hasClass('no-msg')) {
            $e.html('<span class="gender"><span class="fa fa-heart"></span></span>');
        } else {
            $e.html('<span class="fa fa-heart"></span>' + this.collectMsg);
        }
    } else {
        $e.removeClass('remove-from-whishlist').addClass('add-to-whishlist');

        $e.html('<span class="gender"><span class="fa fa-heart"></span>' + this.errorMsg + '</span>');

        $('#notifyLogin').modal('show');
    }
};

AvenueWhishlist.prototype.toWillNotBeCollectedStatus = function ($e, isFailed) {
    var isFailed = isFailed || null;

    if (!isFailed) {
        $e.addClass('add-to-whishlist').removeClass('remove-from-whishlist handling');

        if ($e.hasClass('no-msg')) {
            $e.html('<span class="gender"><span class="fa fa-heart-o"></span></span>');
        } else {
            $e.html('<span class="fa fa-heart-o"></span>' + this.initMsg);
        }
    } else {
        $(this).addClass('remove-from-whishlist').removeClass('add-to-whishlist');

        $e.html('<span class="gender"><span class="fa fa-heart-o"></span>' + this.errorMsg + '</span>');

        $('#notifyLogin').modal('show');
    }
};

