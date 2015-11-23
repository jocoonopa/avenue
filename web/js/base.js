$(function(){
  var 
  	$win = $(window),
    winHeight = $win.outerHeight(true),
    $body = $('#body'),
    $AjaxMsgConsignDiv = $('#AjaxMsgConsign'),
    $AjaxMsgBackOrderDiv = $('#AjaxMsgBackOrder'),
  	nWidthDiv = $AjaxMsgConsignDiv.width(),
  	$consignButton = $('.openConsign'),
  	$backOrderButton = $('.openBackOrder'),
  	$storeChangeSelect = $('footer').find('.goods_search_store');
    //sInformConsignUrl = Routing.generate('order_ajax_inform_consign_done'),
    //sInformBackOrderUrl = Routing.generate('order_ajax_inform_backOrder'),
    //sFeedBackCheckOutUrl = Routing.generate('order_ajax_feedback_ok');
    //sBackOrderConfirmUrl = Routing.generate('order_backOrder_confirm');

	$body.height(parseInt( winHeight ) + parseInt( $('header').outerHeight(true) ) * 1.7);
	$(document).tooltip();

	rightPanelOpen = function () {
		// $consignButton.on('click', function () {
		// 	var $this = $(this);
		// 	if ($AjaxMsgConsignDiv.hasClass('openNow')) {
		// 		$consignButton.stop().animate({'right' : 0}, 700);
		// 		$AjaxMsgConsignDiv.stop().animate({'right' : -nWidthDiv}, 700).removeClass('openNow');
		// 	} else {
		// 		$consignButton.stop().animate({'right' : nWidthDiv}, 700);
		// 		$AjaxMsgConsignDiv.stop().animate({'right' : 0}, 700).addClass('openNow');
		// 	}
		// })
		// .find('button');

		// $backOrderButton.on('click', function () {
		// 	var $this = $(this);
		// 	if ($AjaxMsgBackOrderDiv.hasClass('openNow')) {
		// 		$backOrderButton.stop().animate({'right' : 0}, 700);
		// 		$AjaxMsgBackOrderDiv.stop().animate({'right' : -nWidthDiv}, 700).removeClass('openNow');
		// 	}	else {
		// 		$backOrderButton.stop().animate({'right' : nWidthDiv}, 700);
		// 		$AjaxMsgBackOrderDiv.stop().animate({'right' : 0}, 700).addClass('openNow');
		// 	}
		// })
		// .find('button');

		//$backOrderButton.fadeIn(500);
	};

	actConsign = function(){
		// $.post(sInformConsignUrl, {}, function (res) {
		// 	res = res.replace(/^\s*|\s*$/g,"");
		// 	if (res.length > 0) {
		// 		$AjaxMsgConsignDiv.html(res);

		// 		var 
		// 			// $resUL = $('.consignInformRes'),
		// 			nUlCount = $('.consignTable').length,
		// 			$resButton = $('.consignTable').find('button');

		// 		$consignButton.fadeIn(700);				
		// 		$resButton.on('click', function () {
		// 			var 
		// 				$this = $(this),
		// 				$ul = $this.closest('ul'),
		// 				nOrdersId = $ul.data('id');

		// 			if (!nOrdersId) {
		// 				nOrdersId = $this.data('id');
		// 			}

		// 			bootbox.prompt("請輸入寄賣金額", function (_price) {
		// 				if ( (_price) && (parseInt( _price ) >= 100) ) {
		// 					$.post(
		// 						sFeedBackCheckOutUrl, 
		// 						{ 
		// 							'nOrdersId' : nOrdersId, 
		// 							'nPrice' : _price 
		// 						}, 
		// 						function () {
		// 						$ul.fadeOut( 1200, function () {
		// 							$(this).remove();
		// 							if ($('.consignTable').length == 0) {
		// 								$consignButton.stop().animate(
		// 									{
		// 										'right' : 0
		// 									}, 
		// 									700, 
		// 									function () {
		// 										$consignButton.fadeOut(700);	
		// 									}
		// 								);
		// 								$AjaxMsgConsignDiv.stop().animate({'right' : -nWidthDiv}, 700).removeClass('openNow');
		// 							}		
		// 						});
		// 					});
		// 				}
		// 			}); 
		// 		});

		// 		var 
		// 			$othUL = $('.confirmFeedOth'),
		// 			$othButton = $othUL.find('button'),
		// 			sFeedToOthUrl = Routing.generate('orders_feed_to_oth');

		// 		$othButton.on('click', function(){
		// 			var 
		// 				$this = $(this),
		// 				$ul = $this.closest('ul'),
		// 				nOrdersId = $this.data('id');

		// 			bootbox.confirm("確定接收嗎?", function(result) {
		// 				if (result === false)
		// 					return;
		// 				$.post(sFeedToOthUrl, { 'nOrdersId' : nOrdersId }, function(){
		// 					$ul.fadeOut(1200, function(){
		// 						$(this).remove();
		// 						actConsign();
		// 					});
		// 				});
		// 			}); 
		// 		});

		// 	}
		// });
	};

	// actBackOrder = function(){
	// 	$.post(sInformBackOrderUrl, {}, function(res){
	// 		if (res != '')
	// 		{
	// 			$AjaxMsgBackOrderDiv.html(res);
	// 			var $resUL 		= $('.backOrderIn'),
	// 				$resUL_ 	= $('.backOrderOut'),
	// 				$li 		= $resUL.find('li').not('.button'),
	// 				nUlCount 	= parseInt($resUL.length)+parseInt($resUL_.length),
	// 				$resButton 	= $resUL.find('button');

	// 			if (nUlCount == 0)
	// 			{
	// 				$backOrderButton.stop().animate({'right' : 0}, 700);
	// 				$AjaxMsgBackOrderDiv.stop().animate({'right' : -nWidthDiv}, 700).removeClass('openNow');
	// 				$backOrderButton.fadeOut(700);
	// 				return;
	// 			}

	// 			$backOrderButton.fadeIn(700);			
	// 			$resButton;	
	// 			$li.hover(function(){
	// 				$(this).siblings().addBack().not('.button');
	// 			}, function(){
	// 				$(this).siblings().addBack().not('.button');
	// 			});

	// 			$resButton.on('click', function(){
	// 				var $this 		= $(this),
	// 					$ul   		= $this.closest('ul'),
	// 					token 		= $('.woojinForm').find('[name="form[_token]"]').eq(0).val(),
	// 					nOrdersId 	= $ul.data('id');

	// 				bootbox.confirm("確定完成嗎?", function(result) {
	// 					if (result === false)
	// 						return;
	// 					$.post(sBackOrderConfirmUrl , { nOrdersId : nOrdersId, 'form[_token]': token }, function(res){
	// 						bootbox.alert(res, function(){
	// 							$ul.fadeOut(1200, function(){
	// 								$(this).remove();
	// 								nUlCount --;
	// 								if (nUlCount == 0)
	// 								{
	// 									$backOrderButton.click();
	// 									$backOrderButton.fadeOut(700);
	// 								}
	// 							});
	// 						});
	// 					});
	// 				}); 
	// 			});
	// 		}
	// 	});
	// };

	// checkInformConsign = function () {
	// 	setTimeout(function(){
	// 		if (!$AjaxMsgConsignDiv.hasClass('openNow'))
	// 			actConsign();
	// 		checkInformConsign();
	// 	}, 60000*3);
	// };
	
	// checkInformBackOrder = function () {
	// 	setTimeout(function(){
	// 		// if (!$AjaxMsgBackOrderDiv.hasClass('openNow'))
	// 		// 	actBackOrder();
	// 		checkInformBackOrder();
	// 	}, 60000*3);
	// };

	storeChange = function () {
		if ( ($storeChangeSelect.size() == 0) ){
			return false;
		}
		$storeChangeSelect.on( 'change', function () {
			var $this = $(this),
				sChangeOwnStoreUrl = Routing.generate( 'user_own_store_change' );

			ajaxLoaded();
			$.post( sChangeOwnStoreUrl, { 'store_id': $this.val() }, function ( res ) {
				$.unblockUI();
				window.location = location.href;
			});
		});
	}

	//actBackOrder();
	//actConsign();
	//rightPanelOpen();
	//checkInformBackOrder();
	//checkInformConsign();
	storeChange();
});

function ajaxLoaded () {
	var oCss = { 
	    'border' : 'none',
	    'padding' : '15px',
	    'backgroundColor' : '#000',
	    '-webkit-border-radius' : '10px',
	    '-moz-border-radius' : '10px',
	    'opacity' : .5,
	    'color' : '#fff'
  	};
	return $.blockUI({ 
		css: oCss
	}); 
}

function getGUID() {
	function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
               .toString(16)
               .substring(1);
  }

  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
           s4() + '-' + s4() + s4() + s4();
}
