$(function() {
    var $goods           = $('#tabs'),
		$ul 	         = $goods.children('ul'),
		$li 	         = $ul.children('li'),
		$a 		         = $li.children('a'),
        $divPurchase     = $('#tabs-search'),
        $operatePanel    = $('.operatePanel'),
        $resRight        = $('.ajaxResMsgRight'),
        $openButton      = $('.panelSwitch'),
        nAminateSpeed    = 700;

    $goods.tabs({ heightStyle: "content" });

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
        // }
        // else
        // {
        //     $this.parent().stop().animate({'left': -($this.parent().innerWidth()-40)}, nAminateSpeed, function(){
        //         $this.children('img').attr('src', '/img/Actions-go-next-icon.png').end().addClass('itsOpen'); 
        //     });
        // }
    }).eq(0).click();

});