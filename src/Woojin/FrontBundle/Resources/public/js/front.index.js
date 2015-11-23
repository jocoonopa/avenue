App.initParallaxBg();
// 這應該是控制新品推薦那邊
OwlCarousel.initOwlCarousel();

//最上面那個大塊的 banner slider
RevolutionSlider.initRSfullWidth();    

jQuery('[data-toggle="tooltip"]').tooltip(); 

$('.countdown-tls').each(function () {
    var austDay = new Date($(this).data('date').replace(/-/g, '/'));
    $(this).countdown({until: austDay});
});

$("#owl-demo").owlCarousel({
    navigation: true, // Show next and prev buttons
    slideSpeed: 300,
    paginationSpeed: 400,
    singleItem: true,
    autoPlay: 6000,
    stopOnHover : true

    // "singleItem:true" is a shortcut for:
    // items : 1, 
    // itemsDesktop : false,
    // itemsDesktopSmall : false,
    // itemsTablet: false,
    // itemsMobile : false
});

$('.illustration-img1').css({height: $('.illustration-img1').width()}); 