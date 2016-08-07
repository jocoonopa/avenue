/*
 * Template Name: Unify - Responsive Bootstrap Template
 * Description: Business, Corporate, Portfolio, E-commerce and Blog Theme.
 * Version: 1.6
 * Author: @htmlstream
 * Website: http://htmlstream.com
*/

var App = function () {

    function handleBootstrap() {
        /*Bootstrap Carousel*/
        jQuery('.carousel').carousel({
            interval: 15000,
            pause: 'hover'
        });

        /*Tooltips*/
        jQuery('.tooltips').tooltip();
        jQuery('.tooltips-show').tooltip('show');      
        jQuery('.tooltips-hide').tooltip('hide');       
        jQuery('.tooltips-toggle').tooltip('toggle');       
        jQuery('.tooltips-destroy').tooltip('destroy');       

        /*Popovers*/
        jQuery('.popovers').popover();
        jQuery('.popovers-show').popover('show');
        jQuery('.popovers-hide').popover('hide');
        jQuery('.popovers-toggle').popover('toggle');
        jQuery('.popovers-destroy').popover('destroy');
    }

    function handleSearchV1() {
        jQuery('.search-button').click(function () {
            jQuery('.search-open').slideToggle();
        });

        // jQuery('.search-close').click(function () {
        //     jQuery('.search-open').slideUp();
        // });

        jQuery(window).scroll(function(){
          if(jQuery(this).scrollTop() > 1) jQuery('.search-open').fadeOut('fast');
        });

        jQuery('#head-search').keypress(function(e) {
            if (e.keyCode == 13) {
                jQuery('.search-send-p').click();
            }
        });

        jQuery('.search-send-p').click(function () {
            var val = jQuery('#head-search').val().replace(/\//g, ' ');
            
            if (val.length === 0) {
                return false;
            }

            window.location.href = Routing.generate('front_text_filter', {val: encodeURI(val)});
        });
    }

    function handleToggle() {
        jQuery('.list-toggle').on('click', function() {
            jQuery(this).toggleClass('active');
        });

        /*
        jQuery('#serviceList').on('shown.bs.collapse'), function() {
            jQuery(".servicedrop").addClass('glyphicon-chevron-up').removeClass('glyphicon-chevron-down');
        }

        jQuery('#serviceList').on('hidden.bs.collapse'), function() {
            jQuery(".servicedrop").addClass('glyphicon-chevron-down').removeClass('glyphicon-chevron-up');
        }
        */
    }

    function handleBoxed() {
        jQuery('.boxed-layout-btn').click(function(){
            jQuery(this).addClass("active-switcher-btn");
            jQuery(".wide-layout-btn").removeClass("active-switcher-btn");
            jQuery("body").addClass("boxed-layout container");
        });
        jQuery('.wide-layout-btn').click(function(){
            jQuery(this).addClass("active-switcher-btn");
            jQuery(".boxed-layout-btn").removeClass("active-switcher-btn");
            jQuery("body").removeClass("boxed-layout container");
        });
    }

    function handleHeader() {
         jQuery(window).scroll(function() {
            if (jQuery(window).scrollTop()>100){
                jQuery(".header-fixed .header-static").addClass("header-fixed-shrink");
            }
            else {
                jQuery(".header-fixed .header-static").removeClass("header-fixed-shrink");
            }
        });
    }

    function handleMegaMenu() {
        $(document).on('click', '.mega-menu .dropdown-menu', function(e) {
            e.stopPropagation()
        });

        if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $('li.mega-menu-fullwidth.default-hover > a').click(function () {
                console.log('123');

                window.location.href = $(this).attr('href'); 
            });
        }

        $('.navbar-nav .mega-menu-fullwidth.default-hover').hover(function () {
            $(this).find('a.default').addClass('hidden');
            $(this).find('a.hover').removeClass('hidden');
        }, function () {
            $(this).find('a.default').removeClass('hidden');
            $(this).find('a.hover').addClass('hidden');
        });
    }

    function handleScrollBar() {
        jQuery(document).ready(function ($) {
            "use strict";
            jQuery('.contentHolder').perfectScrollbar();
        });
    }

    function handleIntro() {
        jQuery(document).ready(function () {
            jQuery("#intro-trigger").click(function(){
                bootstro.start(".bootstro", {
                    onComplete : function(params)
                    {
                        //alert("Reached end of introduction with total " + (params.idx + 1)+ " slides");
                    },
                    onExit : function(params)
                    {
                        //alert("Introduction stopped at slide #" + (params.idx + 1));
                    },
                });    
            });
        });
    }

    // function handleFormErrorMessage()
    // {
    //     jQuery.extend(jQuery.validator.messages, {
    //         required: '此欄位為必填欄位',
    //         remote: '請修正此欄位',
    //         email: '請輸入有效的電子信箱',
    //         url: "請輸入有效的網址",
    //         date: "請輸入有效的日期",
    //         dateISO: "Please enter a valid date (ISO).",
    //         number: "請輸入有效的數字",
    //         digits: "請輸入 1 ~ 9 的數字",
    //         creditcard: "請輸入有效的信用卡號碼",
    //         equalTo: "請再次輸入相同的值",
    //         accept: "Please enter a value with a valid extension.",
    //         maxlength: jQuery.validator.format("不可超過 {0} 個字元"),
    //         minlength: jQuery.validator.format("不可少於 {0} 個字元"),
    //         rangelength: jQuery.validator.format("請輸入 {0} 到 {1} 個字元"),
    //         range: jQuery.validator.format("Please enter a value between {0} and {1}."),
    //         max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
    //         min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
    //     });
    // }

    function handleCart() {
        window.ac = new AvenueCart();
    }

    function handleWhishlist() {
        // 願望清單
        window.awl = new AvenueWhishlist();
    }

    function handleHistory() {
        window.hh = new AvenueHistory();
    }

    function handleLazyload() {
        $('img.lazy').lazyload({
            effect : "fadeIn",
            skip_invisible : false
        });
    }

    return {
        init: function () {
            handleBootstrap();
            handleSearchV1();
            handleToggle();
            handleBoxed();
            handleHeader();
            handleMegaMenu();
            handleScrollBar();
            handleCart();
            handleWhishlist();
            handleHistory();
            handleIntro();
            //handleFormErrorMessage();
        },

        initCounter: function () {
            jQuery('.counter').counterUp({delay: 10,time: 1000});
        },

        initParallaxBg: function () {
            jQuery('.parallaxBg').parallax("50%", 0.2);
            jQuery('.parallaxBg1').parallax("50%", 0.4);
        },

        initMouseWheel: function () {
            jQuery('.slider-snap').noUiSlider({
                start: [0, 1000000],
                snap: true,
                connect: true,
                range: {
                    'min': 0,
                    '5%': 5000,
                    '10%': 10000,
                    '15%': 15000,
                    '20%': 20000,
                    '25%': 25000,
                    '30%': 30000,
                    '35%': 35000,
                    '40%': 40000,
                    '50%': 50000,
                    '60%': 60000,
                    '70%': 70000,
                    '80%': 80000,
                    '90%': 90000,
                    'max': 1000000
                }
            });

            $('.slider-snap').on('set', function(){
                $("button.btn-u-dark").eq(0).click();
            });

            jQuery('.slider-snap').Link('lower').to(jQuery('.slider-snap-value-lower'));
            jQuery('.slider-snap').Link('upper').to(jQuery('.slider-snap-value-upper'));
        },
    };

}();