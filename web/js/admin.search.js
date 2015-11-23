var AdminSearchUI = function () {
    this.routeMaps = {
        'brand': 'api_brand_list',
        'pattern': 'api_pattern_list',
        'color': 'api_color_list',
        'level': 'api_level_list',
        'activity': 'api_activity_list', // 待補上
        'mt': 'api_mt_list',
        'productStatus': 'api_goodsStatus_list', // 待補上
        'promotion': 'api_promotion_list', // 待補上
        'orderStatus': 'api_orderStatus_list', // 待補上
        'orderKind': 'api_orderKind_list', // 待補上
        'store': 'api_store_list'
    };

    this.$tabPanes = $('.tab-pane');
    this.prevCondition = '';
    this.oUrl = {
        'searchRes': Routing.generate('admin_search_res'),
        'exportStock': Routing.generate('admin_export_stock'),
        'exportProfit': Routing.generate('admin_export_profit'),
        'exportUitox': Routing.generate('admin_export_uitox'),
        'exportUitoxZip': Routing.generate('admin_export_uitox_zip'),
        'exportNoborderZip': Routing.generate('admin_export_noborder_zip')
    };

    this.bindLoadEvents();
    this.ccAllEvent();
    this.submitButtonEvent();
    this.bindNumericLimit();
    this.datetimepicker();
    this.bindAjaxForm();
};

/**
 * pager init
 */
AdminSearchUI.prototype.initPager = function (count, perpage) {
    $select = $('#page');
    $select.html('');

    var maxPage = Math.ceil(count/perpage);
    if (maxPage === 0) {
        maxPage = 1;
    }

    for (var i = 1; i <= maxPage; i ++) {
        $select.append($("<option></option>").attr('value', i).text(i + '/' + maxPage));
    }
};

/**
 * pager reset
 */
AdminSearchUI.prototype.resetPager = function () {
    var $form = $('form[name="product-search"]').find('input, select').not('[name="page"]');

    if (this.prevCondition !== $form.serialize()) {
        $('#page').val(1);
        $('form[name="product-search"]').find('input[name="page"]').val(1);
    }
};

AdminSearchUI.prototype.loadstart = function (msg) {
    var oCss = { 
        'border': 'none',
        'padding': '5px',
        'backgroundColor': '#000',
        '-webkit-border-radius': '10px',
        '-moz-border-radius': '10px',
        'opacity': .7,
        'color': '#fff',
        'font-size': '14px'
    };

    $.blockUI({css: oCss, message: msg}); 

    return this;
};

AdminSearchUI.prototype.loadcomplete = function () {
    $.unblockUI();

    return this;
};

AdminSearchUI.prototype.bindNumericLimit = function () {
    this.$tabPanes.find('input[name="customMobil"]').numeric();

    return this;
};

AdminSearchUI.prototype.bindLoadEvents = function () {
    var self = this;

    self.bindCheckboxLoadEvents(self.routeMaps);
};

AdminSearchUI.prototype.bindCheckboxLoadEvents = function (maps) {
    var self = this;

    for (var property in maps) {
        if (maps.hasOwnProperty(property) && maps[property]) {
            self.bindEachCheckboxLoadEvent(property, maps[property]);
        }
    }

    return this;
};

AdminSearchUI.prototype.bindEachCheckboxLoadEvent = function (name, routeName) {
    var self = this;

    $('li.' + name).click(function () {
        var $this = $(this);

        if (!$this.hasClass('hasLoad')) {
            self.loadstart('載入選項中請稍後...');

            $.getJSON(Routing.generate(routeName), function(json, textStatus) {
                if (name === 'activity') {
                    self.addMultiSelect($('#'+ name), json);
                } else {
                    self.addCheckboxes($('#'+ name), json);
                }

                self.loadcomplete();
            });

            $this.addClass('hasLoad');
        }
    });

    return this;
};

AdminSearchUI.prototype.addCheckboxes = function ($container, obj) {
    var data = {
        "items": obj,
        "_name": function () {
            return $container.attr('id');
        }
    };

    var template = $('#checkbox-template').html();
    Mustache.parse(template);
    
    var rendered = Mustache.render(template, data);
    $container.html(rendered);

    this.addCheckAllButton($container);
    
    var remain = $container.find('div.col-lg-2').length % 6;

    for (var i = 0; i < (6 - remain); i ++) {
        $container.append($('#com-template').html());
    }

    return this;
};

AdminSearchUI.prototype.datetimepicker = function () {
    this.$tabPanes.find('input.datetime').datetimepicker({
        lang:'zh-TW',
        i18n:{
            de:{
                months:[
                    '1月','2月','3月','4月',
                    '5月','6月','7月','8月',
                    '9月','10月','11月','12月',
                ],
                dayOfWeek:[
                    "週四", "週一", "週二", "週三", 
                    "週四", "週五", "週六",
                ]
            }
        },
        format:'Y-m-d H:i:s'
    });
};

AdminSearchUI.prototype.ccAllEvent = function () {
    var self = this;

    $(document).on('click', 'button.check-all', function () {
        var $this = $(this);
        var $parentTabPane = $this.closest('.tab-pane');

        $parentTabPane.find('input[type="checkbox"]').prop('checked', true);

        return false;
    });

    $(document).on('click', 'button.cancel-all', function () {
        var $this = $(this);
        var $parentTabPane = $this.closest('.tab-pane');

        $parentTabPane.find('input[type="checkbox"]').prop('checked', false);

        return false;
    });

    $('button.cancel-all-global').click(function () {
        self.$tabPanes.find($('input[type="checkbox"]')).prop('checked', false);
        self.$tabPanes.find('select').val('');
        self.$tabPanes.find('input[name="textSeries"]').val('');
        self.$tabPanes.find('input[name="startAt"]').val('');
        self.$tabPanes.find('input[name="endAt"]').val('');
        self.$tabPanes.find('input[name="customMobil"]').val('');
        self.$tabPanes.find('input[name="price_start"]').val('');
        self.$tabPanes.find('input[name="price_end"]').val('');

        return false;
    });
};

AdminSearchUI.prototype.addMultiSelect = function ($container, obj) {
    var data = {
        "items": obj,
        "name": function () {
            return $container.attr('id');
        }
    };

    var template = $('#multi-select-template').html();
    Mustache.parse(template);
    
    var rendered = Mustache.render(template, data);
    $container.html(rendered);
};

AdminSearchUI.prototype.addCheckAllButton = function ($container) {
    var $component = $('#cc-template').html();
    $container.append($component);

    return this;
};

AdminSearchUI.prototype.bindAjaxForm = function () {
    var self = this;
    var $form = $('form[name="product-search"]');

    $form.ajaxForm({
        success: function (res) {
            switch ($form.attr('action'))
            {
                case self.oUrl.searchRes:
                    self.searchResCallback(res);
                    break;
            }

            self.lockPrevNext();

            self.loadcomplete();    
        },
        error: function (e) {
            self.loadcomplete();
            self.addErrorAlert();
        }
    }).on('submit', function(){
        self.loadstart('搜尋中請稍後...');
    });
};

AdminSearchUI.prototype.lockPrevNext = function () {
    // 若是頁數選項是最後一個, 鎖住下一頁鍵
    $('#next-page').prop('disabled', ($('#page').find('option:selected').index() === ($('#page').find('option').length - 1)));

    // 若是頁數選項為第一個，鎖住上一頁鍵
    $('#prev-page').prop('disabled', ($('#page').find('option:selected').index() === 0));
};

/**
 * 顯示搜尋的結果
 *
 * 已json資料套用 Mustache 形成畫面
 * 
 * @param  {json} res
 * @return this   
 */
AdminSearchUI.prototype.searchResCallback = function (res) {
    var self = this;

    if (Math.ceil(parseInt(res.count)/parseInt(res.perpage)) !== $('#page').find('option').length) {
        self.initPager(res.count, res.perpage);
    }

    self.displayResult(res.data);
    
    $("[rel^='prettyPhoto']").prettyPhoto();

    if ($('#export-stock').prop('disabled') === true) {
        $('#export-stock').prop('disabled', false);
    }

    if ($('#export-profit').prop('disabled') === true) {
        $('#export-profit').prop('disabled', false);
    }

    if ($('#export-uitox').prop('disabled') === true) {
        $('#export-uitox').prop('disabled', false);
    }

    if ($('#export-noborder').prop('disabled') === true) {
        $('#export-noborder').prop('disabled', false);
    }
};

/**
 * 下載壓縮圖檔
 * 
 * @param  {array} urls
 * @return this   
 */
AdminSearchUI.prototype.exportUitoxZipCallback = function (urls) {
    for (var i = 0; i < urls.length; i ++) {
        window.open(urls[i]);
    }

    return this;
};

AdminSearchUI.prototype.addErrorAlert = function () {
    var alert = $('#alert-error-template').html();

    $('#main').html(alert);

    return this;
};

AdminSearchUI.prototype.submitButtonEvent = function () {
    var self = this;
    var $form = $('form[name="product-search"]');
    var $page = $form.find('input[name="page"]');
    var $perpage = $form.find('input[name="perpage"]');
    var $order = $form.find('input[name="order"]');
    var $dir = $form.find('input[name="dir"]');

    $('#perpage').change(function () {
        $perpage.val($(this).val());
        
        $('button.submit-trigger').click();
    });

    $('#page').change(function () {
        $page.val($('#page').val());

        $('button.submit-trigger').click();
    });

    $('#next-page').click(function () {
        $('#page').val(parseInt($('#page').val()) + 1);
        $('#page').change();
    });

    $('#prev-page').click(function () {
        $('#page').val(parseInt($('#page').val()) - 1);
        $('#page').change();
    });

    $('button.submit-trigger').click(function () {
        self.resetPager();
        self.prevCondition = $('form[name="product-search"]').find('input, select').not('[name="page"]').serialize();

        $('.tab-pane.active').removeClass('active');
        $('ul.nav>li.active').removeClass('active');

        $order.val($('#order').val());
        // $perpage.val($('#perpage').val());
        $dir.val($('#dir').val());

        $form.attr('action', self.oUrl.searchRes);
        $form.submit();
    });

    $('button#export-stock').click(function () {
        $form.clone().attr('action', self.oUrl.exportStock).ajaxFormUnbind().submit();
    });

    $('button#export-profit').click(function () {
        $form.clone().attr('action', self.oUrl.exportProfit).ajaxFormUnbind().submit();        
    });

    $('button#export-uitox').click(function () {
        var exclude = [];
        $('.item-sn').not('.label-success').each(function () {
            var $this = $(this);

            exclude.push($this.data('id'));
        });

        var $form = $('form[name="product-search"]');

        $cloneForm = $form.clone();
        $cloneForm.find('input[name="exclude"]').val(JSON.stringify(exclude));
        $cloneForm.attr('action', self.oUrl.exportUitox).ajaxFormUnbind().submit();

        $cloneForm.attr('action', Routing.generate('admin_export_uitox_zip')).ajaxForm({
            dataType: 'json',
            success: function (urls) {
                self.exportUitoxZipCallback(urls);
            }
        }).submit();
    });

    $('button#export-noborder').click(function () {

        $(this).prop('disabled', true);

        var exclude = [];
        $('.item-sn').not('.label-success').each(function () {
            var $this = $(this);

            exclude.push($this.data('id'));
        });

        var $form = $('form[name="product-search"]');

        $cloneForm = $form.clone();
        $cloneForm.find('input[name="exclude"]').val(JSON.stringify(exclude));
        $cloneForm.ajaxFormUnbind().attr('action', self.oUrl.exportNoborderZip).ajaxForm({
            dataType: 'json',
            success: function (urls) {
                self.exportUitoxZipCallback(urls);

                $(this).prop('disabled', false);
            }
        }).submit();
    });

    $(document).on('click', '.item-sn', function () {
        var $this = $(this);

        $this.toggleClass('label-success').toggleClass('label-danger');
    });

    return this;
};

AdminSearchUI.prototype.filled = function (items) {
    var data = {
        "items": items,
        "url": function () {
            var self = this;
            var url = Routing.generate('goods_edit_v2', {id: self.id}) + '?iframe=true&width=100%&height=100%';

            return url;
        }
    };

    var template = $('#item-template').html();
    Mustache.parse(template);

    var rendered = Mustache.render(template, data);

    $('#main').html(rendered);
};

AdminSearchUI.prototype.displayResult = function (items) {
    $('#main').empty();

    this.filled(items);

    $('img.lazy').lazyload({
        effect: 'fadeIn',
        effectspeed: 900
    });

    setTimeout(function() {
        var container = document.querySelector('#main');
        var msnry = new Masonry(container, {
            itemSelector: '.item',
            columnWidth: '.item'
        });

        $('.item').removeClass('visible-hidden');
    }, 700);
    
    return false;
};



