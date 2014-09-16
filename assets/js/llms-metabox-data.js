jQuery(document).ready(function($) {

	$.toggle_sales_fields();

	$('#cancel-sale').on('click', function () {
		$.clear_fields(["#_sale_price", "#_sale_price_dates_from", "#_sale_price_dates_to"]);
		return false;
	});
	
});

//Toggle sales fields
(function($){  
	$.toggle_sales_fields = function() {

		if ($('#_sale_price').val().length > 0) {
	  		$("#checkme").prop('checked', true);
	  		$("#extra").show("fast");
		}
		else {
			//Hide div w/id extra
	   		$("#extra").css("display","none");
		}

		$("#checkme").click(function(){
			$.toggle_sales_fields();	
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