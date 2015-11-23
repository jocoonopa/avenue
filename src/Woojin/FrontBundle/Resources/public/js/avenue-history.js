window.AvenueHistory = function () {
    var self = this;

    /**
     * 一定要加這行，否則你的 cookie 一旦跑到別的路徑就會消失...
     * 
     * @type {String}
     */
    $.cookie.defaults.path = '/';

    if (typeof $.cookie('avenueHistory') == 'undefined') {
        $.cookie('avenueHistory', JSON.stringify([]));
    }

    $(document).on('click', 'button.left-fixed-remove', function (e) {
        event.stopPropagation();

        self.remove($(this));

        return false;
    });

    $(document).on('click', 'button.left-fixed-empty', function () {
        self.empty($(this));

        return false;
    });
};

AvenueHistory.prototype.add = function (id) {
    var jsonProductIds = $.cookie('avenueHistory');
    var productIds = JSON.parse(jsonProductIds);

    productIds.unshift(id);

    $.unique(productIds);

    $.cookie('avenueHistory', JSON.stringify(productIds));

    return this;
};

AvenueHistory.prototype.empty = function ($e) {
    $.cookie('avenueHistory', JSON.stringify([]));

    $e.closest('.side-container').remove();

    return this;
};

AvenueHistory.prototype.remove = function ($e) {
    var jsonProductIds = $.cookie('avenueHistory');
    var productIds = JSON.parse(jsonProductIds);
    var index = productIds.indexOf($e.data('id'));

    if (index === -1) {
        return this;
    }

    productIds.splice(index, 1);
    
    $.cookie('avenueHistory', JSON.stringify(productIds));

    if ($e.closest('.gallery-block').siblings('.gallery-block').length === 0) {
        $e.closest('.side-container').remove();
    } else {
        $e.closest('.gallery-block').remove();
    }

    return this;
};