// global admin functions
(function($){ 
	// Datepicker to do: need to change this to an option that's set from admin.
	$('.datepicker').datepicker({ 
		dateFormat: "mm/dd/yy" 
	});

	//only display prerequisite field if has prerequisite is checked
	if ( $( '#llms_has_prerequisite').attr('checked') ) {
		$( '.llms_select_prerequisite' ).show();
	} else {
		$( '.llms_select_prerequisite' ).hide();
	}
	$( '#llms_has_prerequisite').on( 'change', function() {
		if ( $( '#llms_has_prerequisite').attr('checked') ) {
			$( '.llms_select_prerequisite' ).show();
		} else {
			$( '.llms_select_prerequisite' ).hide();
		}
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
