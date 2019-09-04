/**
 * Global admin functions.
 *
 * @since Unknown
 * @version 3.35.0
 */

( function( $ ){

	// Toggle sales price settings.
	clear_fields = function (fields) {
		var fields = fields;

		$.each( fields, function( i, val ) {
			$( val ).val( '' );
		});
	}

	// Load ajax animation functionality
	load_ajax_animation = function() {
		$( '#loading' ).hide();

		$( document ).ajaxStop(function(){
			$( '#loading' ).hide();
		});

		$( document ).ajaxStart(function(){
			$( '#loading' ).show();
		});
	}

} )( jQuery );
