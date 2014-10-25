jQuery(document).ready(function($) {

	//display coupon redemption form on link click
	get_current_price();
	
	$('#show-coupon').on( 'click', display_coupon_form );

	$('.llms-payment-options input[type=radio]').change(display_current_price);

	//$('.llms-price-option-radio').on('change', display_current_price );
	
});

(function($){  
display_coupon_form = function() {

	// Hide the show coupon link
	$(this).hide();
	$('#llms-checkout-coupon').show().slideDown('slow');
	return false;
}
})(jQuery);

(function($){  
display_current_price = function() {
	var target_id = $(this).attr('id');

	var price = $('#' + target_id).parent().find('label').text();

	$('.llms-final-price').text(price);

	// Hide the show coupon link
	// $(this).hide();
	// $('#llms-checkout-coupon').show();
	// return false;
}
})(jQuery);

(function($){  
get_current_price = function() {

	var price = $('.llms-payment-options input[type=radio]:checked');
	var target_id = $(price).attr('id');

	var price = $('#' + target_id).parent().find('label').text();

	$('.llms-final-price').text(price);

	// Hide the show coupon link
	// $(this).hide();
	// $('#llms-checkout-coupon').show();
	// return false;
}
})(jQuery);