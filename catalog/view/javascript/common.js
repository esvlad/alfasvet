function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	} else { 			// Изменения для seo_url от Русской сборки OpenCart 3x
		var query = String(document.location.pathname).split('/');
		if (query[query.length - 1] == 'cart') value['route'] = 'checkout/cart';
		if (query[query.length - 1] == 'checkout') value['route'] = 'checkout/checkout';
		
		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

function reward_text_cases(number, uppercase) {
	var result, html;

	if(!Number.isInteger(number)){
		var titles = ['бонус','бонуса','бонусов'];
    	cases = [2, 0, 1, 1, 1, 2];

    	result = titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
	} else {
		result = 'бонусов';
	}

	if(result === undefined){
		result = 'бонусов';
	}

	if(!uppercase){
		html = '<b>'+number.toLocaleString('ru-RU')+'</b> '+result;
	} else {
		html = '<b>'+number.toLocaleString('ru-RU')+'</b> <span>'+result+'</span>';
	}

	return html;
}

$(document).mouseup(function (e) {
	var all_menu_el = $('.all_menu');
	if(all_menu_el.is('div')){
		if (all_menu_el.has(e.target).length === 0 && $('#top-links > ul > li.mmenu').has(e.target).length === 0 && all_menu_el.css('display') == 'block'){
			$('#top-links > ul > li.mmenu').removeClass('open');
			$('.all_menu').css('display', 'none');
		}
	}

	var search = $('.container_menu.not_mobile #search');
	if(search.is('div')){
		if (search.has(e.target).length === 0 && search.hasClass('on')){
			search.removeClass('on');

			if($('.search_result').css('z-index') == 3){
				$('.search_result').animate({'opacity': 0}, 150, function(){
					$(this).css('z-index', -99);
				});
			}
		}
	}

	//var faq_tooltip = $('.faq_tooltip');
	if($('.faq_tooltip').is('span')){
		if ($('.faq_tooltip').has(e.target).length === 0 && $('.faq_tooltip').hasClass('active')){
			$('.faq_tooltip').removeClass('on');

			setTimeout(function(){
				$('.faq_tooltip').removeClass('active');
			}, 200);
		}
	}
});

var winWidth = $(window).outerWidth();

$(document).ready(function() {
	var winWidth = $(window).outerWidth();
	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	$('#reset_password').click(function(){
		$('#auth .modal-dialog._auth').addClass('hidden');
		$('#auth .modal-dialog._reset').removeClass('hidden');
	});

	$('#password_reset').click(function(){
		$('#resetpass .error_text').detach();

		$.ajax({
			url: 'index.php?route=account/reset/reset',
			type: 'post',
			data: 'email=' + $('#resetpass input[name="email"]').val(),
			dataType: 'json',
			success: function(json){
				console.log(json);
				if(json.success){
					$('#resetpass .form_block').addClass('hidden');
					$('#resetpass .form_success').removeClass('hidden');
				}

				if(json.error_text){
					$('#resetpass input[name="email"]').parent().append('<p class="error_text">'+json.error_text+'</p>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError){
	        	console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	    	}
		});
	});

	$('#auth .close').click(function(){
		setTimeout(function(){
			$('#resetpass .form_success, .modal-dialog._reset').addClass('hidden');
			$('#resetpass .form_block, .modal-dialog._auth').removeClass('hidden');
		}, 150);
	});

	$('.container[data-category-id="66"] .product_list_item').each(function(){
		$(this).find('.caption_parameters span:eq(0)').after($(this).find('.caption_parameters span:eq(2)'));
	});

	/*$('.container[data-category-id="66"] .product_list_item .caption_parameters span:eq(3)').after($('.container[data-category-id="66"] .product_list_item .caption_parameters span:eq(0)'));*/

	var uri = 'http://' + location.host + location.pathname;
	if($('#product-product').is('div')){
		var strl = uri.split('/')[5].length;
		var ll = uri.length - strl - 1;
		uri = uri.substring(ll,0);
	}
	$('.nav a[href^="http://"], .all_menu a[href^="http://"], .container_menu a[href^="http://"], .mmenu_slideups_child a[href^="http://"]').each(function(){
		var href = $(this).attr('href');

		/*if($('#product-product').is('div')){
			strl = uri.split('/')[5].length;
			ll = uri.length - strl - 1;
			console.log(uri.substring(ll,0));
			uri = uri.substring(ll,0);
		}*/

		//console.log(href);
		//console.log(uri);
		if(uri == href){//href.indexOf(uri) != -1
			$(this).addClass('active');

			if($(this).parents('.navbar').is('nav')){
				$(this).parents('.dropdown-menu').prev().addClass('active');
			}

			if($(this).parent().parent().is('.mmenu_toogle_slideups_child')){
				$(this).parents('.mmenu_slideups_child').find('h3 > a').addClass('active');
			}
		}
	});

	$('.slide_arrow').click(function(){
		var parent = $(this).parent();
		if(!parent.hasClass('on')){
			parent.addClass('on');
			parent.next().slideDown(150);
		} else {
			parent.removeClass('on');
			parent.next().slideUp(150);
		}
	});

	$('.footer_center > h5').click(function(){
		var self = $(this);
		if(!self.hasClass('on')){
			self.addClass('on');
			self.next().slideDown(150);
		} else {
			self.removeClass('on');
			self.next().slideUp(150);
		}
	});

	if(winWidth <= 1024){
		$('.navbar_menu > li').click(function(){
			if($(this).children('.dropdown-menu').css('display') != 'block'){
				$(this).children('.dropdown-menu').css('display', 'block');
			} else {
				$(this).children('.dropdown-menu').css('display', 'none');
			}
		});
	}

	$('.quantity .minus, .quantity .plus').click(function(){
		var self = $(this).parent();
		var qnum = parseInt(self.find('.qnum').text());

		if($(this).hasClass('minus') && qnum == 1){
			return false;
		}

		if($(this).hasClass('minus')){
			qnum--;
		}

		if($(this).hasClass('plus')){
			qnum++;
		}

		if(self.find('.input_quantity').is('input')){
			if(qnum > parseInt(self.find('.input_quantity').attr('max'))){
				qnum = parseInt(self.find('.input_quantity').attr('max'));
			}

			self.find('.input_quantity').val(qnum);

			var cart_id = self.parents('.product_cart').data('id');
		    var quantity = qnum;

		    $.ajax({
		      url: 'index.php?route=checkout/cart/edit',
		      type: 'post',
		      data: 'cart_id=' + cart_id + '&quantity='+quantity,
		      dataType: 'json',
		      success: function(json) {
		      	$('._cart_header').attr('data-count', json.count_cart);
		        pre_cart(json);
		      },
		      error: function(xhr, ajaxOptions, thrownError){
		        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		      }
		    });
		} else {
			if($('.prices .special').is('li')){
				var special = parseInt($('.prices .special').attr('data-special').replace(/\s/g, ''));
			}

			if($('.prices ._reward').is('span')){
				var reward = parseFloat($('.prices ._reward').attr('data-reward').replace(/\s/g, ''));
			}
			
			var price = parseInt($('.prices .price').attr('data-price').replace(/\s/g, ''));
			
			var _special = 0, _price = 0, _reward = 0;


			if(special && special > 0){
				_special = parseInt(qnum) * parseInt(special);
				$('.prices .special > span > b').text(_special.toLocaleString('ru-RU'));
			}

			if(reward && reward > 0){
				_reward = parseInt(qnum) * parseFloat(reward);
				//$('.prices ._reward > b').text(_reward.toLocaleString('ru-RU'));

				$('.prices ._reward').html(reward_text_cases(_reward, true));
			}

			_price = parseInt(qnum) * parseInt(price);
			$('.prices .price h2 > b').text(_price.toLocaleString('ru-RU'));
		}

		self.find('input[name="quantity"]').val(qnum);
		self.find('.qnum').text(qnum);
	});

	// Currency
	$('#form-currency .currency-select').on('click', function(e) {
		e.preventDefault();

		$('#form-currency input[name=\'code\']').val($(this).attr('name'));

		$('#form-currency').submit();
	});

	// Language
	$('#form-language .language-select').on('click', function(e) {
		e.preventDefault();

		$('#form-language input[name=\'code\']').val($(this).attr('name'));

		$('#form-language').submit();
	});

	/* Search */
	$('#search input[name=\'search\']').parent().find('button').on('click', function() {
		var url = $('base').attr('href') + 'index.php?route=product/search';

		var value = $('header #search input[name=\'search\']').val();

		if (value) {
			url += '&search=' + encodeURIComponent(value);
		}

		location = url;
	});

	$('#search input[name=\'search\']').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$('header #search input[name=\'search\']').parent().find('button').trigger('click');
		}
	});

	// Menu
	$('#menu .dropdown-menu').each(function() {
		var menu = $('#menu').offset();
		var dropdown = $(this).parent().offset();

		var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());

		if (i > 0) {
			$(this).css('margin-left', '-' + (i + 10) + 'px');
		}
	});

	// Product List
	$('#list-view').click(function() {
		$('#content .product-grid > .clearfix').remove();

		$('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
		$('#grid-view').removeClass('active');
		$('#list-view').addClass('active');

		localStorage.setItem('display', 'list');
	});

	// Product Grid
	$('#grid-view').click(function() {
		// What a shame bootstrap does not take into account dynamically loaded columns
		var cols = $('#column-right, #column-left').length;

		if (cols == 2) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
		} else if (cols == 1) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
		} else {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12');
		}

		$('#list-view').removeClass('active');
		$('#grid-view').addClass('active');

		localStorage.setItem('display', 'grid');
	});

	if (localStorage.getItem('display') == 'list') {
		$('#list-view').trigger('click');
		$('#list-view').addClass('active');
	} else {
		$('#grid-view').trigger('click');
		$('#grid-view').addClass('active');
	}

	// Checkout
	$(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function(e) {
		if (e.keyCode == 13) {
			$('#collapse-checkout-option #button-login').trigger('click');
		}
	});

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});

	$('.switcher > div').click(function(){
	    var sw = $(this).data('switch');

	    if($(this).parent().hasClass('disabled')){
	    	return false;
	    }

	    if(sw == 1){
	      $('._usercomp').addClass('opacity30');
	      $('._usercomp input').attr('disabled', 'disabled');
	      $('._usercomp .input_file').addClass('disabled');
	    } else {
	      $('._usercomp').removeClass('opacity30');
	      $('._usercomp input').removeAttr('disabled');
	      $('._usercomp .input_file').removeClass('disabled');
	    }

	    $('.switcher > div').removeClass('active');
	    $(this).addClass('active');
	    $('input[name="user_type"], input[name="custom_field[account][4]"]').val(sw);
	});

	$('#auth_login').click(function(){
		var auth_form = {};

		auth_form['email'] = $('#autorization input[name="email"]').val();
		auth_form['password'] = $('#autorization input[name="password"]').val();

		$('#autorization input, #autorization label').removeClass('_error');
		//$('#autorization .error_text').detach();
		
		$.ajax({
			url: 'index.php?route=account/login/autorization',
			type: 'post',
			data: auth_form,
			dataType: 'json',
			success: function(json){
				console.log(json);

				if(json.redirect){
					$('#autorization').trigger('submit');
					//location = json.redirect;
				} else if(json.errors){
					for (i in json.errors) {
	                    var element = $('#autorization input[name=' + i + ']');
	                    
	                    //element.after('<p class="error_text">'+json.errors[i]+'</p>');
	                    element.addClass('_error');
	                    element.prev().addClass('_error');
	                }
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	$('#auth_registration').click(function(){
		var registration_form = {};

		$('#registration input, #registration label').removeClass('_error');

		$('#registration .form-group.required input').each(function(){
			registration_form[$(this).attr('name')] = $(this).val();
		});
		
		$.ajax({
			url: 'index.php?route=account/register/registration_form',
			type: 'post',
			data: registration_form,
			dataType: 'json',
			success: function(json){
				console.log(json);

				if(json.redirect){
					$('#registration').trigger('submit');
				} else if(json.errors){
					for (i in json.errors) {
	                    var element = $('#registration input[name=' + i + ']');
	                    
	                    element.addClass('_error');
	                    element.prev().addClass('_error');
	                }
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	$('.input_file_reg').on('click', function() {
		var element = this;
		var self = $(this);

		if($(element).parent().hasClass('opacity30')){
			return false;
		}

		$('#form-upload').remove();

		$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

		$('#form-upload input[name="file"]').trigger('click');

		if (typeof timer != 'undefined') {
	    	clearInterval(timer);
		}

		timer = setInterval(function() {
			if ($('#form-upload input[name="file"]').val() != '') {
				clearInterval(timer);

				$.ajax({
					url: 'index.php?route=tool/upload',
					type: 'post',
					dataType: 'json',
					data: new FormData($('#form-upload')[0]),
					cache: false,
					contentType: false,
					processData: false,
					beforeSend: function() {
						$(element).children('.loading').addClass('on');
					},
					success: function(json) {
						if (json['error']) {
							alert(json['error']);
						}

						if (json['success']) {
							//alert(json['success']);

							$(element).children('.loading').removeClass('on');
              				$(element).children('span').text(json['origin_name']);
							self.next().val(json['code']);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		}, 500);
	});

	$('.quantity input[name^="quantity"]').focusout(function(){
		var qnum = $(this).val();
		$(this).parent().find('.qnum_num .qnum').removeClass('focus');

		if(qnum < 1) qnum = 1;
		$(this).parent().find('.qnum_num .qnum').text(qnum);
	});

	$('#top-links > ul > li.mmenu').click(function(){
		console.log('click');
		if($(this).hasClass('open')){
			$(this).removeClass('open');
			if(winWidth > 640){
				$('.all_menu').css('display', 'none');
			} else {
				$('.slide_up_menu').slideUp();
			}			
		} else {
			$(this).addClass('open');
			if(winWidth > 640){
				$('.all_menu').css('display', 'block');
			} else {
				$('.slide_up_menu').slideDown();
			}
		}
	});

	$('input[type="tel"]').bind('input', function(e){
		this.value = this.value.replace(/[^\d]/g,'');
	});

	$('.quantity input[name^="quantity"]').bind('input', function(e){
		var qnum = $(this).val();

		if(qnum > parseInt($(this).attr('max'))) qnum = parseInt($(this).attr('max'));
		if(qnum < parseInt($(this).attr('min'))) qnum = parseInt($(this).attr('min'));

		$(this).parent().find('.qnum_num .qnum').text(qnum);
		$(this).val(qnum);

		if($(this).hasClass('input_quantity')){
			var cart_id = $(this).parents('.product_cart').data('id');
		    var quantity = qnum;

		    $.ajax({
		      url: 'index.php?route=checkout/cart/edit',
		      type: 'post',
		      data: 'cart_id=' + cart_id + '&quantity='+quantity,
		      dataType: 'json',
		      success: function(json) {
		        pre_cart(json);
		      },
		      error: function(xhr, ajaxOptions, thrownError){
		        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		      }
		    });
		} else {
			if($('.prices .special').is('li')){
				var special = parseInt($('.prices .special').attr('data-special').replace(/\s/g, ''));
			}

			if($('.prices ._reward').is('span')){
				var reward = parseFloat($('.prices ._reward').attr('data-reward').replace(/\s/g, ''));
			}
			
			var price = parseInt($('.prices .price').attr('data-price').replace(/\s/g, ''));
			
			var _special = 0, _price = 0, _reward = 0;

			if(special && special > 0){
				_special = parseInt(qnum) * parseInt(special);
				$('.prices .special > span > b').text(_special.toLocaleString('ru-RU'));
			}

			/*if(reward && reward > 0){
				_reward = parseInt(qnum) * parseFloat(reward);
				$('.prices ._reward > b').text(_reward.toLocaleString('ru-RU'));
			}*/

			if(reward && reward > 0){
				_reward = parseInt(qnum) * parseFloat(reward);

				$('.prices ._reward').html(reward_text_cases(_reward, true));
			}

			if(special && special > 0){
				_special = parseInt(qnum) * parseInt(special);
				$('.prices .special > span > b').text(_special.toLocaleString('ru-RU'));
			}

			_price = parseInt(qnum) * parseInt(price);
			$('.prices .price h2 > b').text(_price.toLocaleString('ru-RU'));
		}
	});

	$('.quantity .qnum_num').click(function(){
		$(this).parent().find('input[name^="quantity"]').focus().val('');

		$(this).children('.qnum').addClass('focus').text('\u00A0');
	});

	$('.raccount_text a[data-target="#auth"]').click(function(){
		var location_href, pathname = location.pathname;

		if(pathname == '/cart'){
			location_href = location.href + '?step=2';
		} else {
			location_href = location.href;
		}

		$('#registration .form_block').prepend('<div class="form-group required" style="display: none;"><input type="hidden" name="redirect" value="'+location_href+'"></div>');
	});

	$('.filials_dropdown .dropdown-menu > li > a').click(function(){
		$(this).parents('.dropdown').find('.dropdown-toggle > span').text($(this).children('span').text());
	});

	$('.list-group-politics > a').click(function(e){
		e = e || event;
		e.preventDefault();

		var id = $(this).attr('href');

		$('html, body').animate({scrollTop: ($(id).offset().top - 25)}, 750);
	});

	$('.form-diller .btn_gold').click(function(){
		var self = $(this).parents('.form-horizontal');
		var form_data = {};
		var error_form = false;
		self.find('input, label').removeClass('_error');

		self.find('.form-group.required input').each(function(){
			if($(this).val() == ''){
				$(this).addClass('_error');
				$(this).prev().addClass('_error');
				error_form = true;
			}
		});

		if(!error_form){
			self.find('input').each(function(){
				form_data[$(this).attr('name')] = $(this).val();
			});

			form_data['uri'] = location.href;
      		form_data['page'] = $('input[name="title_page"]').val();

			$.ajax({
		        url: 'index.php?route=common/footer/diller',
		        type: 'post',
		        data: form_data,
		        dataType: 'json',
		        complete: function() {
		            //self.find('.btn_review').text('Отправляется');
		        },
		        success: function(json) {
		          console.log(json);

		          if(json.success){
		            self.find('.form_block').animate({'opacity': 0}, 150);
		            self.find('.form_success').fadeIn(150);

		            setTimeout(function(){
		              self.find('.form-group input').each(function(){
		                $(this).val('');
		              });

		              self.find('.form_success').fadeOut(150);
		              self.find('.form_block').animate({'opacity': 1}, 150);
		            }, 3500);
		          }
		        }
		    });
		}
	});

	$('#specialist').load('index.php?route=information/information/specialist');
	$('.marketing_form').load('index.php?route=information/information/marketing');
	
	$('#filials .filials_container').load('index.php?route=information/information/filials');

	if(winWidth <= 1024 && winWidth > 768){
		$('.partners_block').load('index.php?route=extension/module/carousel/view&banner_id=9&slides=4&space=0');
		$('.sertificate_block').load('index.php?route=extension/module/carousel/view&slides=4&full=true');
	} else if(winWidth <= 768 && winWidth > 640){
		$('.partners_block').load('index.php?route=extension/module/carousel/view&banner_id=9&slides=3&space=0');
		$('.sertificate_block').load('index.php?route=extension/module/carousel/view&slides=3&full=true');
	} else if(winWidth <= 640){
		$('.partners_block').load('index.php?route=extension/module/carousel/view&banner_id=9&slides=1&space=0');
		$('.sertificate_block').load('index.php?route=extension/module/carousel/view&slides=1&full=true');
	} else {
		$('.partners_block').load('index.php?route=extension/module/carousel/view&banner_id=9&space=0');
		$('.sertificate_block').load('index.php?route=extension/module/carousel/view&full=true');
	}

	$('.map_sklad').html('<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ac18caec61c33baa06524ac4fa8a71fdaf572cc75e835bd8e7aaa1330c6133741&amp;width=100%25&amp;height=490&amp;lang=ru_RU&amp;scroll=false"></script>');

	if(winWidth <= 768){
		$('.filters_main_title').removeClass('on');
	}

	$('a[data-target="#auth"]').each(function(){
		if(!$('body').hasClass('logged')){
			$(this).attr('href', '#');
		} else {
			$(this).removeAttr('data-toggle data-target');
		}
	});

	$('.container_menu.not_mobile #search input[name="search"]').focusin(function(){
		$('.container_menu.not_mobile #search').addClass('on');

		if($('.search_result a').length > 0 && $(this).val().length > 2){
			$('.search_result').css('z-index', 3).animate({'opacity': 1}, 150);
		}
	});

	if(winWidth <= 640){
		$('#search input[name="search"]').focusin(function(){
			$(this).parents('.mmenu_group').addClass('focus');
			
			$('.slide_up_menu').animate({ scrollTop: $('.mmenu_group form').offset().top }, 'slow');
		});

		$('#search input[name="search"]').focusout(function(){
			$('.slide_up_menu').animate({ scrollTop: 0 }, 'slow', function(){
				$(this).parents('.mmenu_group').removeClass('focus');
			});

			/*if($('.search_result').css('z-index') == 2){
				$('.search_result').animate({'opacity': 0}, 150, function(){
					$(this).css('z-index', -99);
				});
			}*/
		});
	}

	$('input[name="search"]').bind('input',function(e){
		var word = $(this).val();
		word = word.toLowerCase();

		if(word.length > 0){
			$.ajax({
				url: 'index.php?route=product/search/keywords',
				type: 'get',
				data: {'keyword' : word},
				dataType: 'html',
				success: function(html){
					console.log(html.length);
					if(html){
						$('.search_result_list').html(html);
						$('.search_result').css('z-index', 3).animate({'opacity': 1}, 150);
					} else {
						if($('.search_result').css('z-index') == 3){
							$('.search_result').animate({'opacity': 0}, 150, function(){
								$(this).css('z-index', -99);
							});
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
		        	log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		        }
			});
		} else {
			if($('.search_result').css('z-index') == 3){
				$('.search_result').animate({'opacity': 0}, 150, function(){
					$(this).css('z-index', -99);
				});
			}
		}
	});

	if (typeof functionName == 'magnificPopup') {
		$('.marketing_bottom_images').magnificPopup({
		    type:'image',
		    delegate: 'a',
		    gallery: {
		      enabled: true
		    }
		});
	}
});

function auth_social(social){
	alert(social);
}

function pre_cart(json){
	for(key in json){
		if(key == 'products'){
			var all_quantity = 0, all_reward = 0;
			$.each(json['products'], function(key, value){
				var product = $('.product_cart[data-id="'+value.cart_id+'"]');
				var _reward = 0;
				if(value.reward) _reward = value.reward;

				product.find('._total').text(value.total);
				product.find('.reward > i').html(reward_text_cases(_reward, true));

				all_quantity = all_quantity + parseInt(value.quantity);
				all_reward = all_reward + parseFloat(_reward);
			});

			$('.cart_totals_block .total_tovars').text(all_quantity);
			$('._bonuses ._tbonus').html(reward_text_cases(all_reward));
		}

		if(key == 'total'){
			$('.cart_totals_block .total_tovar, .cart_totals_block .total_price').text(json['total'].toLocaleString('ru-RU'));
			$('#delivery input[name="total"]').val(json['total']);
		}
	}
}

// Cart add remove functions
var cart = {
	'add': function(product_id, quantity) {
		var self = $(this);
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			/*beforeSend: function() {
				$('#cart > button').button('loading');
			},*/
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					/*$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
					}, 100);*/

					//$('html, body').animate({ scrollTop: 0 }, 'slow');

					//$('#cart > ul').load('index.php?route=common/cart/info ul li');
					//$('.product_list_item[data-id="'+product_id+'"] .in_cart').addClass('added');
					if(product_id == 50){
						$('.btn_chemodan').addClass('on').text('В корзине');
					} else if(product_id == 51) {
						$('.btn_svets').addClass('on').text('В корзине');
					} else {
						$('.product_list_item[data-id="'+product_id+'"] .in_cart').addClass('on');
						$('.product_list_item[data-id="'+product_id+'"] .in_cart > span').text('В корзине');
					}

					$('._cart_header').attr('data-count', json['count_cart']);

					/*setTimeout(function () {
						$('.product_list_item[data-id="'+product_id+'"] .in_cart > span').text('В корзину');
					}, 1000);*/
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}

				$('._cart_header').attr('data-count', json['count_cart']);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}

				$('._cart_header').attr('data-count', json['count_cart']);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				/*$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);

				$('html, body').animate({ scrollTop: 0 }, 'slow');*/

				$('.product_caption_price .wishlist, .product_list_item[data-id="'+product_id+'"] .wishlist').addClass('on').attr('onclick', 'wishlist.remove(\''+product_id+'\');').text('Добавлено');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/remove',
			type: 'get',
			data: 'remove=' + product_id,
			dataType: 'json',
			success: function(json) {
				if(json.success){
					$('.product_list_item[data-id="'+product_id+'"] .wishlist').removeClass('on').attr('onclick', 'wishlist.add(\''+product_id+'\');').text('В избранное');

					$('.wishlist .product_list_item[data-product="'+product_id+'"]').fadeOut(150, function(){
						$(this).detach();

						if($('.wishlist .product_list_item').length == 0){
							$('.wishlist').append('<p>Избранных товаров нет, но вы можете их добавить.</p>');
							$('html, body').animate({ scrollTop: $('#account-account').offset().top }, 'slow');
						}
					});

					$('.product_caption_price .wishlist, .product_list_item[data-id="'+product_id+'"] .wishlist').removeClass('on').attr('onclick', 'wishlist.add(\''+product_id+'\');').text('В избранное');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#compare-total').html(json['total']);

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

$('.sort .instock').click(function(){
	console.log('.sort .instock');
	if(!$(this).hasClass('on')){
		$(this).addClass('on');
	} else {
		$(this).removeClass('on');
	}
});

/* Agree to Terms */
$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			html  = '<div id="modal-agree" class="modal">';
			html += '  <div class="modal-dialog">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header">';
			html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');
		}
	});
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);
