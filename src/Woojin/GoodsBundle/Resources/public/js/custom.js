$(function () {
    $('button#top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 1000);
    });

    $('button#bottom').click(function () {
        $('html, body').animate({ scrollTop: $(document).height() }, 1000);
    });

    $('button.mason-trigger').click(function () {
        if (!window.MOBILE_TMP_DATA.data) {
            return false;
        }

        return genMasonView();
    });

    $('button.default-trigger').click(function () {
        if (!window.MOBILE_TMP_DATA.data) {
            return false;
        }

        return genListView();
    });

    // prepare Options Object 
    var options = { 
        success: function(res) { 
            window.MOBILE_TMP_DATA = res;

            if ($('#product-mason').hasClass('hidden')) {
                genListView();
            } else {
                genMasonView();
            }

            if (window.MOBILE_TMP_DATA.page_sum != $('select#page').val()) {
                rebuildOptions(window.MOBILE_TMP_DATA.page_sum);
            }
            
            $('select#page').val(window.MOBILE_TMP_DATA.current_page);

            $.unblockUI();
        },
        error: function () {
            alert('查詢發生錯誤，請再試一次!');

            $.unblockUI();
        },
        beforeSubmit: function(arr, $form, options) { 
            $.blockUI({
                message: '讀取中...',
                css: { 
                'border' : 'none',
                'padding' : '15px',
                'backgroundColor' : '#000',
                '-webkit-border-radius' : '10px',
                '-moz-border-radius' : '10px',
                'opacity' : .5,
                'color' : '#fff',
                'font-size': '12px'
            }});

            $('input[name="page"]').val($('select#page').val());          
        }
    }; 

    $('form#search').ajaxForm(options);

    $('input[type="checkbox"]').change(function () {
        if ($('select#page').val() == 1) {
            return;
        }

        $('select#page').html('<option value="1">1</option>');
        $('select#page').val(1);
        $('input[name="page"]').val($('select#page').val());   
    });

    $('input[name="name"]').change(function () {
        if ($('select#page').val() == 1) {
            return;
        }

        $('select#page').html('<option value="1">1</option>');
        $('select#page').val(1);
        $('input[name="page"]').val($('select#page').val());   
    });

    $('select#page').change(function () {
        $('input[name="page"]').val($(this).val());
        $('form#search').submit();
    });

    $('button.reset').click(function () {
        $('form#search').resetForm();
    });

    $('button#left').click(function () {
        prev();
    });

    $('button#right').click(function () {
        next();
    });

    $(document).on('click', 'button.view-order', function () {
        $('.modal-body').html('<h3>讀取中...</h3>')
        var id = $(this).data('id');

        $.post(Routing.generate('mobile_order'), {id: id})
            .success(function (res) {
                var template = $('#orders-template').html();
                Mustache.parse(template);
                var orders = {orders: res};

                var rendered = Mustache.render(template, orders);
                $('.modal-body').html(rendered);

                $('.nav-tabs a:first').tab('show');

                $('.nav-tabs a').click(function (e) {
                    e.preventDefault()
                    $(this).tab('show')
                })
            });
    });

    $('button.navbar-toggle').click();

    $(document).on('scroll', function() {
        if (!$('.component').hasClass('hidden')) {
            $('.component').addClass('hidden');
        }
    });

    $(document).on('scrollstop',function() {
        if ($('.component').hasClass('hidden') 
            && $('button#top').offset().top >= 150
        ) {
            $('.component').removeClass('hidden');
        }
    });

    $(document).on('click', 'img.item-image', function () {
        $('#masonItem_2').find('.modal-body').html('<p>讀取中...</p>');

        var $table = $(this).parent().find('table');

        $newTable = $table.clone().removeClass('hidden');
        $('#masonItem_2').find('.modal-body').html($newTable.html());
    });
});

window.MOBILE_TMP_DATA = null;

function next()
{
    var page = parseInt($('select#page').val()); 

    return (page === $('select#page option').length) ? null : $('select#page').val(page + 1).change();  
}

function prev()
{
    var page = parseInt($('select#page').val());
    
    return (page === 1) ? null : $('select#page').val(page - 1).change();
}

function genListView()
{
    $('#product-container').empty();
    $('#product-mason').addClass('hidden');
    $('#product-container').removeClass('hidden');

    for (var i in window.MOBILE_TMP_DATA.data) {
        renderProductView(window.MOBILE_TMP_DATA.data[i]);
    }

    $('html, body').animate({scrollTop: $('#product-container').offset().top}, 1000, function () {
        $('img.lazy').lazyload({
            effect: 'fadeIn',
            effectspeed: 900
        });
    });

    return false;
}

function genMasonView()
{
    $('#product-mason').empty();
    $('#product-mason').removeClass('hidden');
    $('#product-container').addClass('hidden');

    for (var i in window.MOBILE_TMP_DATA.data) {
        renderItem(window.MOBILE_TMP_DATA.data[i]);
    }

    setTimeout(function() {
        $('html, body').animate({scrollTop: $('#product-mason').offset().top}, 1000, function () {
            $('img.lazy').lazyload({
                effect: 'fadeIn',
                effectspeed: 900,
                load: function() {
                    var container = document.querySelector('#product-mason');
                    var msnry = new Masonry(container, {
                        itemSelector: '.item',
                        columnWidth: '.item'
                    });
                }
            });
        });
    }, 700);
    
    return false;
}

function renderItem(item)
{
    var template = $('#mason-template').html();
    Mustache.parse(template);

    var rendered = Mustache.render(template, item);

    $('#product-mason').append($(rendered));
}

function rebuildOptions(num)
{
    $('select#page').empty();

    for (var i = 1; i <= num; i ++) {
        $('select#page').append($('<option value="' + i + '">' + i + '/' + num + '</option>'))
    }
}

function renderProductView(product)
{
    var template = $('#unit-template').html();
    Mustache.parse(template);   // optional, speeds up future uses

    var rendered = Mustache.render(template, product);
    $('#product-container').append($(rendered));
}