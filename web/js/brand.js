var 
	brand_id_delete = '',
	delete_path = '',
	tag_switch_flag = 1,
	add_path = '',
	class_add = '',
	index_add = '',
	brand_id_add = '',
	value_ = '',
	value_before = '',
	$ul_delete,
	$li_reset_index,
	$add_button;

function ajax_delete_func ($e, delete_path_) {
	var 
		brand_name = $e.parent().siblings(':nth-of-type(2)').text(),
    $dialog = $('.dialog'),
    $em = $dialog.find('em');
	brand_id_delete = $e.parent().siblings(':first-of-type').children('input').val();
  delete_path = delete_path_;
  $ul_delete = $e.closest('ul');
  $li_reset_index = $ul_delete.parent();
	$em.text( brand_name );
	$dialog.dialog('open');
} 

function ajax_update_func ($e, update_path) {
	if (tag_switch_flag == 1) {
		tag_switch_flag = 0;
    var 
    	$span = $e,
      $td = $e.parent(),
      value_ = $span.text(),
      class_ = $e.attr('class'),
      value_before = value_;
    $td.parent().children().eq(0).click();
		$span.remove();
		$td.append('<input class="ajax_brand_name" type="text" value="'+value_+'" />');
		$('.ajax_brand_name').focus().on('blur', function () {
			if (tag_switch_flag == 0) {               
	      var 
	      	$input = $(this), 
          $li = $(this).parent(),
          $ul = $li.parent(),
          id = $ul.children( 'li:first-of-type' ).children( 'input' ).val(),
          url = Routing.generate( update_path );         
        value_ = $input.val();
        if (value_before != value_ && value_ != '') {          	
          $.ajax({
						url: url,
						type: 'POST',
						data: {
              'id' : id,
              'value' : value_
						},
						error: function () {
							alert('ajax發生錯誤');
							$.unblockUI();
						},
						success: function (response) {
							if (response == 'exists') {
								alert('此修改會造成資料重複!');
								value_ = value_before;							
							}
							$input.remove();
							$li.append('<span class="'+class_+'" style="width: 100px;">'+value_+'</span>');
							$.unblockUI();
						}
					});
        } else if (value_before != value_) {
        	$input.remove();
					$li.append('<span class="'+class_+'">'+value_before+'</span>');
        } else {
        	bootbox.alert('不可以改為空白!', function () {
      			$input.remove();
						$li.append('<span class="'+class_+'">'+value_before+'</span>');
        	});
        }                 
				tag_switch_flag = 1;
				$('.'+class_).on('click', function () {
					ajax_update_func ($(this), update_path);
				});
			}
		});
	}
}

function reset_index_ul_li ( $li ) {
	var $ul = $li.children('ul');
	$ul.each(function (index) {
    var 
    	index_ = index + 1,
      $span = $(this).children('li:first-of-type').children('span');
			$span.text(index_);
	});
}

$(function () {
	$( document ).on( 'click', '.first', function () {
		var brand_id = $(this).children('input').val();
			$('.first').siblings().andSelf().css('opacity', .5);
			$(this).siblings().andSelf().css('opacity', 1);
			$('.sub_list').fadeOut(100);
			$(this).siblings('.sub_list').fadeIn(750);
	})
	.on( 'click', '.sub_first', function () {
		var brand_type_id = $(this).children('input');
		$('ul[class^="brand_type_"]').children('li').css('opacity', .5);
		$(this).siblings().andSelf().css('opacity', 1);
		$('.sub_list_sub').fadeOut(100);
		$(this).siblings('.sub_list_sub').fadeIn(750);
	})
	.on( 'click','.sub_sub_first', function () {
		$('ul[class^="brand_sn_"]').children('li').css('opacity', .5);
		$(this).siblings().andSelf().css('opacity', 1);
	})
	.on( 'click', '.brand_name', function () {
		ajax_update_func($(this), 'brand_ajax_update');	
	})
	.on( 'click','.brand_type_name', function () {
		ajax_update_func($(this), 'brand_type_ajax_update');
	})
	.on( 'click','.brand_sn_name', function () {
		ajax_update_func($(this), 'brand_sn_name_ajax_update');
	})
	.on( 'click','.brand_sn_color', function () {
		ajax_update_func($(this), 'brand_sn_color_ajax_update');
	})
	.on( 'click','.delete_brand', function () {
		ajax_delete_func($(this), 'brand_ajax_delete');
	})
	.on( 'click','.delete_brand_type', function () {
		ajax_delete_func($(this), 'brand_type_ajax_delete');
	})
	.on( 'click','.delete_brand_sn', function () {
		ajax_delete_func($(this), 'brand_sn_ajax_delete');
	})
	.on('click', 'li[class^="add_"] button', function () {		 
    var 
    	add_what = $(this).parent().attr('class'),
      $strong = $('.dialog_add>p>strong'),
      $ul = $(this).closest('ul');

    class_add = $ul.prev().attr('class');
    index_add = ($('.'+class_add).size() + 1);
    $add_button = $(this);
    $li_reset_index = $ul.parent();
		switch (add_what) {
			case 'add_brand':
				$strong.text('品牌');
        add_path = 'brand_ajax_add';
        class_add = '';
    		index_add	= $('.first').size()+1;
				$('.dialog_add').dialog('open');
				break;
			case 'add_brand_type':
				$strong.text('款式');
        add_path = 'brand_type_ajax_add';
        class_add = 'brand_type_';
        brand_id_add = $ul.parent().siblings('li:first-of-type').children('input').val();
				$('.dialog_add').dialog('open');
				break;
			case 'add_brand_sn':
				$strong.text('型號及顏色');
        add_path = 'brand_sn_ajax_add';
        class_add = 'brand_sn_';
				brand_id_add = $ul.parent().siblings('li:first-of-type').children('input').val();
				$('.dialog_add_sn').dialog('open');
				break;
		}	
	});

	$('.dialog').dialog({
    resizable : false,
    autoOpen : false,
    height : 280,
    modal : true,
    buttons : {
			"刪除" : function () {		
        var	
        	url = Routing.generate(delete_path),
      	$dialog = $(this);
				$.ajax({
          url: url,
          type: 'POST',
					data: {
						id: brand_id_delete
					},
					error: function () {
						alert('已經有上層綁定資料，無法刪除');
						$dialog.dialog('close');
					},
					success: function (response) {
						$ul_delete.remove();
						$dialog.children('input').val('');
						reset_index_ul_li ($li_reset_index);
						$dialog.dialog('close');
					}
				});
			},
			"取消": function () {
				$(this).children('input').val('');
				$(this).dialog( "close" );
			}
		}
	});
	$('.dialog_add, .dialog_add_sn').dialog({
    resizable : false,
    autoOpen : false,
    height : 280,
    modal : true,
    buttons : {
			"新增": function () {					
        var 
        	url = Routing.generate(add_path),
          $dialog = $(this),
          value_ = $dialog.children('input:first-of-type').val(),
          value_2 = $dialog.children('input:last-of-type').val();
        if (value_ == '')
        	return;
        if ( ($(this).find('input').length > 1) && (value_2 == '') ) {
        	return;
        }         
				$.ajax({
          url: url,
          type: 'POST',
          data: {
            value : value_,
            value_ : value_2,
            brand_id : brand_id_add
					},
					error : function () {
						$dialog.dialog('close');
						alert('ajax error');
						$.unblockUI();
					},
					success : function (response) {
						if (response != 'exists') {
							if (add_path == 'brand_sn_ajax_add') {
								var 
									insert_element = '<ul class="'+class_add+'"><li class="sub_sub_first"><span>'+index_add+'</span><input type="hidden" value="'+response+'" /></li>';
									insert_element +='<li><span class="brand_sn_name">'+value_+'</span></li><li><span class="brand_sn_color">'+value_2+'</span>'; 
									insert_element +='</li><li><button class="delete_brand_sn btn btn-warning">刪除</button></li></ul>';
							}	 else if (add_path == 'brand_type_ajax_add') {
								var 
									insert_element = '<ul class="'+class_add+'"><li class="sub_first"><span>'+index_add+'</span><input type="hidden" value="'+response+'" /></li>';
									insert_element +='<li><span class="brand_type_name">'+value_+'</span></li>';		
									insert_element +='<li><button class="delete_brand_type btn btn-warning">刪除</button></li>';
									insert_element +='<li class="sub_list_sub"><ul><li class="add_brand_sn"><button class="btn btn-primary">新增型號</button></li></ul></li></ul>';
								$('.brand_type_').css('background-color', '#bf7f7f');
							} else if (add_path == 'brand_ajax_add') {
								var 
									insert_element = '<ul><li class="first"><span>'+index_add+'</span><input type="hidden" value="'+response+'" /></li>';
									insert_element += '<li><span class="brand_name">'+value_+'</span></li>';
									insert_element +='<li><button class="delete_brand btn btn-warning">刪除</button></li>';
									insert_element +='<li class="sub_list"><ul><li class="add_brand_type"><button class="btn btn-primary">新增款式</button></li></ul></li></ul>';
							}
							$add_button.parent().parent().before(insert_element);
							reset_index_ul_li ($li_reset_index);
							$dialog.children('input').val('');
							$dialog.dialog('close');
							$.unblockUI();
						} else {
							$.unblockUI();
							alert('該品牌序號已經存在!');
							$dialog.children('input').focus();
						}
					}
				});
			},
			"取消": function () {
				$(this).dialog('close');
			}
		}
	});
}); 