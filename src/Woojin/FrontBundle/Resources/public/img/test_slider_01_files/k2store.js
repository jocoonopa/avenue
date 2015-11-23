jQuery.noConflict();
if(typeof(K2Store) == 'undefined') {
	var K2Store = jQuery.noConflict();
}
if(typeof(k2storeURL) == 'undefined') {
	var k2storeURL = '';
}
K2Store(document).ready(function(){
	
	if (doesExist('#miniK2StoreCart')) {
		doMiniCart();
	}
	
	
	if (doesExist('#k2store_shipping_make_same')) {
		if (K2Store('#k2store_shipping_make_same').is(':checked')) {
			K2Store('#k2store_shipping_section').css({'visible' : 'visible', 'display' : 'none'});
			
			K2Store('#k2store_shipping_section').children(".input-label").removeClass("required");
					
			K2Store('#k2store_shipping_section').children(".input-text").removeClass("required");
		}
	}
	
	K2Store('.k2storeCartForm1').each(function(){
	K2Store(this).submit(function(e) {	
		e.preventDefault();
		var form = K2Store(this);
		
		/* Get input values from form */
		var values = form.serializeArray();
		
	
	K2Store.ajax({
		url: k2storeURL+'index.php',
		type: 'post',
		//data: form.find('input[type=\'text\']'), form.find('input[type=\'hidden\']'), form.find('input[type=\'radio\']:checked'), form.find('input[type=\'checkbox\']:checked'), form.find('select'), form.find('textarea'),
		data: values,
		dataType: 'json',
		success: function(json) {
			form.find('.k2success, .k2warning, .k2attention, .k2information, .k2error').remove();
			K2Store('.k2store-notification').hide();
			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {
						form.find('#option-' + i).after('<span class="k2error">' + json['error']['option'][i] + '</span>');
					}
				}
				if (json['error']['stock']) {
					form.find('.k2stock').after('<span class="k2error">' + json['error']['stock'] + '</span>');
				}
				
				if (json['error']['product']) {
					form.find('.k2product').after('<span class="k2error">' + json['error']['product'] + '</span>');
				}
				
			}
			
			if (json['redirect']) {
				window.location.href = json['redirect'];
			}
			
			if (json['success']) {
				form.find('.k2store-notification .message').html('<div class="k2success">' + json['successmsg'] + '</div>');
				if (!json['redirect']) {
					form.find('.k2store-notification').fadeIn('slow');
				}
			
			//if module is present, let us update it.
				if(doesExist('#miniK2StoreCart')) {
					K2Store('#miniK2StoreCart').html(json['total']);
				}
			 
			}	
		}
	});

	});
	});
	
});

function doMiniCart() {
	var container = '#miniK2StoreCart';
	var murl = k2storeURL
			+ 'index.php?option=com_k2store&view=mycart&task=ajaxmini';
	
	K2Store.ajax({
		url : murl,
		type: 'post',
		success: function(response){
			if (doesExist(container)) {
				K2Store(container).html(response);
			}
		}

	});
}

function k2storeGetPaymentForm(element, container) {
	var url = k2storeURL
			+ 'index.php?option=com_k2store&view=checkout&task=getPaymentForm&format=raw&payment_element='
			+ element;
	k2storeDoTask(url, container, document.adminForm);
}

function k2storeDoTask(url, container, form, msg) {
	//to make div compatible
	container = '#'+container;
	k2storeNewModal(msg);

	// if url is present, do validation
	if (url && form) {
		var str = K2Store(form).serialize();
		// execute Ajax request to server
		K2Store.ajax({
			url : url,
			type : 'post',
			 cache: false,
             contentType: 'application/json; charset=utf-8',
             dataType: 'json',
			// data:{"elements":Json.toString(str)},
             success: function(json) {
            	if (doesExist(container)) {
            		K2Store(container).html(json.msg);
				}
				(function() {
					K2Store('body').find('#k2storeModal').remove();
				}).delay(500);
				return true;
			}
		});
	} else if (url && !form) {
		// execute Ajax request to server
		K2Store.ajax({
			url : url,
			 type: 'post',
             cache: false,
             contentType: 'application/json; charset=utf-8',
             dataType: 'json',
             success: function(json) {
            	if (doesExist(container)) {
            		K2Store(container).html(json.msg);
				}
				(function() {
					K2Store('body').find('#k2storeModal').remove();
				}).delay(500);
			}
		});
	}
}

/**
 * 
 * @param {String}
 *            msg message for the modal div (optional)
 */
function k2storeNewModal(msg) {
	if (typeof window.innerWidth != 'undefined') {
		var h = window.innerHeight;
		var w = window.innerWidth;
	} else {
		var h = document.documentElement.clientHeight;
		var w = document.documentElement.clientWidth;
	}
	var t = (h / 2) - 15;
	var l = (w / 2) - 15;
	var i = document.createElement('img');
	var src = k2storeURL + 'media/k2store/images/ajax-loader.gif';
	i.src = src;

	// var s = window.location.toString();
	// var src = 'components/com_k2store/images/ajax-loader.gif';
	// i.src = (s.match(/administrator\/index.php/)) ? '../' + src : src;

	i.style.position = 'absolute';
	i.style.top = t + 'px';
	i.style.left = l + 'px';
	i.style.backgroundColor = '#000000';
	i.style.zIndex = '100001';
	var d = document.createElement('div');
	d.id = 'k2storeModal';
	d.style.position = 'fixed';
	d.style.top = '0px';
	d.style.left = '0px';
	d.style.width = w + 'px';
	d.style.height = h + 'px';
	d.style.backgroundColor = '#000000';
	d.style.opacity = 0.5;
	d.style.filter = 'alpha(opacity=50)';
	d.style.zIndex = '100000';
	d.appendChild(i);
	if (msg != '' && msg != null) {
		var m = document.createElement('div');
		m.style.position = 'absolute';
		m.style.width = '200px';
		m.style.top = t + 50 + 'px';
		m.style.left = (w / 2) - 100 + 'px';
		m.style.textAlign = 'center';
		m.style.zIndex = '100002';
		m.style.fontSize = '1.2em';
		m.style.color = '#ffffff';
		m.innerHTML = msg;
		d.appendChild(m);
	}
	document.body.appendChild(d);
}

function k2storeCartRemove(key, product_id, pop_up) {
	var container;
	if (pop_up == 1) {
		container = K2Store('#sbox-content');
	} else {
		container = K2Store('#k2storeCartPopup');
	}
	var myurl = k2storeURL+'index.php?option=com_k2store&view=mycart&task=update&popup='
			+ pop_up;
	K2Store.ajax({
				url : myurl,
				type: 'post',
				data : "remove=1&key=" + key,
				//update : container,
				success: function(response) {
					K2Store(container).html(response);
					if (doesExist('#miniK2StoreCart')) {
						doMiniCart();
					}
				},// onSuccess
				error: function() {
					window.location(k2storeURL+"index.php?option=com_k2store&view=mycart&task=update&remove=1&cid["
									+ key + "]=" + product_id);
				}// onFailure
			});
}


function k2storeGetAjaxZone(field_name, field_id, country_value, default_zid) {

	var url = k2storeURL
			+ 'index.php?option=com_k2store&view=checkout&task=ajaxGetZoneList';
	K2Store.ajax({
		url : url,
		//update : field_name.substring(0, 4) + 'ZoneList',
		type : 'post',
		data : {
			'country_id' : country_value,
			'zone_id' : default_zid,
			'field_name' : field_name,
			'field_id' : field_id
		},
		// onRequest: function() { document.id('listproduct').set('text',
		// 'loading...'); },
		success: function(response) {
			// document.id('zoneList').
			K2Store('#'+field_name.substring(0, 4) + 'ZoneList').html(response);
		}
	});
}

function doesExist(element) {
	 return K2Store(element).length > 0;
}