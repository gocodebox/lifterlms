jQuery(document).ready(function($) {

	//display coupon redemption form on link click
	
	$('#show-coupon').on( 'click', display_coupon_form );
	
});

(function($){  
display_coupon_form = function() {

	// Hide the show coupon link
	$(this).hide();
	$('#llms-checkout-coupon').show();
	return false;
}
})(jQuery);