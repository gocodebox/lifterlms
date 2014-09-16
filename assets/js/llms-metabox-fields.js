// global admin functions
(function($){ 
	// Datepicker to do: need to change this to an option that's set from admin.
	$('.datepicker').datepicker({ 
		dateFormat: "yy/mm/dd" 
	});

	// Toggle sales price settings. 
	clear_fields = function (fields) {
		var fields = fields;

		$.each( fields, function( i, val ) {
  			$( val ).val('');
		});
	}

	// Load ajax animation functionality
	load_ajax_animation = function() {
		$('#loading').hide();

		$(document).ajaxStop(function(){
			$('#loading').hide();
		});

		$(document).ajaxStart(function(){
			$('#loading').show();
		});
	}

})(jQuery);
