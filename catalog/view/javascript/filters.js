var form = {};

$(document).on('keydown',function (event) {
    if (event.key == 'F5') {
    	history.replaceState('', "category", location.href);
    }
});

if(history.state){
	var state_form = JSON.parse(history.state);
	//console.log(state_form);

	$.ajax({
		url: 'index.php?route=product/category/filter',
		type: 'get',
		data: state_form,
		dataType: 'html',
		success: function(html){
			$('#product-category .products').html(html);
		}
	});
}

if(getURLVar('sort') != '' && typeof getURLVar('sort') == 'string'){
	form['sort'] = getURLVar('sort');

	if(getURLVar('order') != ''){
		form['order'] = getURLVar('order');
	}
}

$('.filters_group').each(function(){
	$(this).find('input').each(function(){
		//console.log($(this).attr('type'));
		var self = $(this);
		if(self.attr('type') == 'checkbox' || self.attr('type') == 'radio'){
			//trigger_filter();
			//$(this).removeAttr('checked');

			if(getURLVar(self.attr('name')) != '' && parseInt(self.val()) == parseInt(getURLVar(self.attr('name')))){
				self.attr('checked','checked');
				form[self.attr('name')] = getURLVar(self.attr('name'));
			} else if(typeof state_form !== "undefined" && parseInt(self.val()) == parseInt(state_form[self.attr('name')])){
				self.attr('checked','checked');
				form[self.attr('name')] = state_form[self.attr('name')];
			}
		}
	});	
});

$('#product-category[data-category-id="71"] .filters_group_body .inputs input').change(function(){
	if($(this).val() == 41 && $(this).is(':checked')){
		$('.filters_group.fdiameter').addClass('disabled');
	} else {
		$('.filters_group.fdiameter').removeClass('disabled')
	}
});

$('.sort .instock input').change(function(){
	trigger_filter();
});

$('.faq_tooltip').click(function(e){
	e.preventDefault();
	var self = $(this);

	if(!self.hasClass('active')){
		self.addClass('on');

		setTimeout(function(){
			self.addClass('active');
		}, 200);
	}

	return false;
});

$('.slideups').click(function(){
	var self = $(this);
	var toogle_slideups = $(this).next('.toogle_slideups');
	
	if(toogle_slideups.css('display') == 'block'){
		self.removeClass('on');
		toogle_slideups.slideUp();
	} else {
		self.addClass('on');
		toogle_slideups.slideDown();
	}
});

$('.range').each(function(){
	var self = $(this);
	var range_values = self.next('.range_values');
	var first = 0, last = 0;

	var first_input = range_values.find('label:first input');
	var last_input = range_values.find('label:last input');

	if(getURLVar(first_input.attr('name')) != ''){
		first = parseInt(getURLVar(first_input.attr('name')));
		first_input.val(first.toLocaleString('ru-RU') + self.data('suffix'));
		form[first_input.attr('name')] = first;
	} else if(typeof state_form !== "undefined"){
		first = parseInt(state_form[first_input.attr('name')]);
		first_input.val(first.toLocaleString('ru-RU') + self.data('suffix'));
		form[first_input.attr('name')] = first;
	} else {
		first = self.data('min');
	}

	if(getURLVar(last_input.attr('name')) != ''){
		last = parseInt(getURLVar(last_input.attr('name')));
		last_input.val(last.toLocaleString('ru-RU') + self.data('suffix'));
		form[last_input.attr('name')] = last;
	} else if(typeof state_form !== "undefined"){
		last = parseInt(state_form[last_input.attr('name')]);
		last_input.val(last.toLocaleString('ru-RU') + self.data('suffix'));
		form[last_input.attr('name')] = last;
	} else {
		last = self.data('max');
	}

	self.slider({
		range: true,
	    min: self.data('min'),
	    max: self.data('max'),
	    step: self.data('step'),
	    values: [first, last],
	    slide: function( event, ui ) {
	    	range_values.find('label:first input').val(ui.values[0].toLocaleString('ru-RU') + self.data('suffix'));
	    	range_values.find('label:last input').val(ui.values[1].toLocaleString('ru-RU') + self.data('suffix'));
	    },
	    change: function(event, ui) {
	    	/*setTimeout(function() {
	    	  trigger_filter(self);
	    	}, 2000);*/
			trigger_filter();//self.parents('.filters_group_body').data('groupId'), ui.values[0], ui.values[1]
	    }
	});
});

$('.filters_group input[type="checkbox"], .filters_group input[type="radio"]').change(function(){
	//if($(this).is(':checked') && $(this).attr('type') == 'radio') $(this).removeAttr('checked');
	trigger_filter();
});

function trigger_filter(){
	form = {};

	$('.filters_group').each(function(){
		if(!$(this).hasClass('disabled')){
			var input = $(this).find('input');

			input.each(function(){
				var self_input = $(this);

				if(self_input.attr('type') == 'radio' || self_input.attr('type') == 'checkbox'){
					if(self_input.is(':checked')){
						form[self_input.attr('name')] = parseInt(self_input.val());
					}
				} else {
					form[self_input.attr('name')] = parseInt(self_input.val().replace(/\s/g, ''));
				}
			});
		}
	});

	$('.sort input').each(function(){
		var input = $(this);

		if(input.attr('type') == 'checkbox'){
			if(input.is(':checked')){
				form[input.attr('name')] = parseInt(input.val());
			}
		} else {
			if(input.val() != ''){
				form[input.attr('name')] = input.val();
			}
		}
	});

	form['path'] = $('#filters').attr('data-category');
	history.pushState(JSON.stringify(form), "category", location.href);

	console.log(form);

	$.ajax({
		url: 'index.php?route=product/category/filter',
		type: 'get',
		data: form,
		dataType: 'html',
		success: function(html){
			$('#product-category .products').html(html);
		}
	});

	return true;
}

var total_now = parseInt($('.ptotals .total_now').text());
function more_product(){
	var total = parseInt($('.ptotals .total').text());
	if(!form['page']){
		form['page'] = 2;
	} else {
		form['page'] = parseInt(form['page']) + 1;
	}

	console.log(form);
	total_new = total_now * form['page'];
	
	if(!form['path']) form['path'] = $('#filters').attr('data-category');

	$.ajax({
		url: 'index.php?route=product/category/more',
		type: 'get',
		data: form,
		dataType: 'html',
		success: function(html){
			$('#product-category .product_list').append(html);

			if(total_new >= total){
				total_new = total;
				$('.pagination_more').detach();
				//$('.pagination_block').detach();
			}

			$.ajax({
				url: 'index.php?route=product/category/pagination_more',
				type: 'get',
				data: form,
				dataType: 'html',
				success: function(html){
					$('.pagination_block').html(html);
				}
			});

			$('.ptotals .total_now').text(total_new);
		}
	});
}

var winWidth = $(window).outerWidth();

if(winWidth <= 640){
	$('.sort > .price').appendTo($('.sort_mobile_block'));
	$('.sort > .populars').appendTo($('.sort_mobile_block'));
	$('.sort > .instock').appendTo($('.sort_mobile_block'));
	$('.sort > input').appendTo($('.sort_mobile_block'));
}

$('.sort_mobile > h3').click(function(){
	var self = $(this);
	if(!self.hasClass('on')){
		self.addClass('on');
		self.next().slideDown();
	} else {
		self.removeClass('on');
		self.next().slideUp();
	}
});