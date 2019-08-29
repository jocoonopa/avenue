$(function() {
    var $orders                 = $('#orders'),
		$ul 	                = $orders.children('ul'),
		$li 	                = $ul.children('li'),
		$a 		                = $li.children('a'),
        $divPurchase            = $('#orders-purchase'),
        $operatePanel           = $('.operatePanel'),
        $resRight               = $('.ajaxResMsgRight'),
        $openButton             = $('.panelSwitch'),
        $ajaxEditGoodsDialog    = $('.ajaxEditGoodsDialog'),
        $ordersEditFormDialog   = $('.ordersEditFormDialog'),
        nAminateSpeed           = 700;

    $orders.tabs(
    	{
    		heightStyle: "fill"
    	}
    );

    $a.on('click', function(){
    	var sTargeId 	     = $(this).attr('href');
			$theOpenButton   = $(sTargeId).find('.panelSwitch');

    	if( $theOpenButton.hasClass('itsOpen') )
            $theOpenButton.click();

        $openButton.not('.itsOpen').not($theOpenButton).click();
    });

    $openButton.on('click', function(){
        var $this = $(this);
        // if ($this.hasClass('itsOpen'))
        // {
            $this.parent().stop().animate({'left': 0}, nAminateSpeed, function(){
                $this.children('img').attr('src', '/img/Actions-go-previous-icon.png').end().removeClass('itsOpen');
            });
        //}
        // else
        // {
        //     $this.parent().stop().animate({'left': -($this.parent().innerWidth()-40)}, nAminateSpeed, function(){
        //         $this.children('img').attr('src', '/img/Actions-go-next-icon.png').end().addClass('itsOpen');
        //     });
        // }
    }).eq(0).click();

    $ajaxEditGoodsDialog.dialog({
        autoOpen    : false,
        modal       : true,
        height      : 800,
        width       : 500,
        buttons:{
            '確定':
            function(){
                ajaxLoaded();
                $(this).find('form').submit();
                $(this).dialog('close');
            },
            '取消':
            function(){
                $(this).dialog('close');
            }
        }
    });

    $ordersEditFormDialog.dialog({
        autoOpen    : false,
        modal       : true,
        height      : 800,
        width       : 500,
        buttons:{
            '確定':
            function(){
                ajaxLoaded();
                $(this).find('form').submit();
                $(this).dialog('close');
            },
            '取消':
            function(){
                $(this).dialog('close');
            }
        }
    });
});


function scrollToPageTop () {
    $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
    $body.animate({
        scrollTop: 0
    }, 600);
}

var brand_type_option_arr           = new Array(),
    brand_sn_option_arr             = new Array();