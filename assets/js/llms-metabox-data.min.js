jQuery(document).ready(function($) {

	if($('#_sale_price').length) {
		toggle_sales_fields();
	}

	if($('#_llms_subscription_price').length) {
		toggle_recurring_fields();
	}

	$('#cancel-sale').on('click', function () {
		clear_fields(["#_sale_price", "#_sale_price_dates_from", "#_sale_price_dates_to"]);
		return false;
	});
	
});

//Toggle sales fields
(function($){  
	toggle_sales_fields = function() {

		if ($('#_sale_price').val().length > 0) {
	  		$("#checkme").prop('checked', true);
	  		$("#extra").show("fast");
		}
		else {
			//Hide div w/id extra
	   		$("#extra").css("display","none");
		}

		$("#checkme").click(function(){
			toggle_sales_fields();	
		});

		if ($("#checkme").is(":checked"))
		{
			//show the hidden div
			$("#extra").show("fast");
		}
		else
		{
			//otherwise, hide it
			$("#extra").hide("fast");
		}
	}
})(jQuery);


//Toggle Recurring Payment Fields
(function($){  
	toggle_recurring_fields = function() {

		if ($('#_llms_subscription_price').val().length > 0) {
	  		$("#recurring_options").show("fast");
		}
		else {
			//Hide div w/id extra
	   		$("#recurring_options").css("display","none");
		}

		$("#_llms_recurring_enabled").click(function(){
			toggle_recurring_fields();	
		});

		if ($("#_llms_recurring_enabled").is(":checked"))
		{
			//show the hidden div
			$("#recurring_options").show("fast");
		}
		else
		{
			//otherwise, hide it
			$("#recurring_options").hide("fast");
		}
	}
})(jQuery);