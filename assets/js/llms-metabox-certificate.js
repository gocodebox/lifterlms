jQuery( document ).ready(function($){

	$( '.certificate_image_button' ).click(function(e) {

		// Create Media Manager On Click to allow multiple on one Page
		var certificate_uploader;

		e.preventDefault();

		// Setup the Variables based on the Button Clicked to enable multiple
		var certificate_img_input_id = '#' + this.id + '.upload_certificate_image';
		var certificate_img_src      = 'img#' + this.id + '.llms_certificate_image';

		// If the uploader object has already been created, reopen the dialog
		if (certificate_uploader) {
			certificate_uploader.open();
			return;
		}

		// Extend the wp.media object
		certificate_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Certificate Image',
			button: {
				text: 'Choose Certificate'
			},
			multiple: false
		});

		// When a file is selected, grab the URL and set it as the text field's value
		certificate_uploader.on('select', function() {
			attachment = certificate_uploader.state().get( 'selection' ).first().toJSON();
			// Set the Field with the Image ID
			$( certificate_img_input_id ).val( attachment.id );
			// Set the Sample Image with the URL
			$( certificate_img_src ).attr( 'src', attachment.url );

		});

		// Open the uploader dialog
		certificate_uploader.open();

	});

});
/*
* Media Manager 3.5
* @version 1.70
*/
jQuery( document ).ready(function($){
	// Remove Image and replace with default and Erase Image ID for Certificate
	$( '.llms_certificate_clear_image_button' ).click(function(e) {
		e.preventDefault();
		var certificate_remove_input_id = 'input#' + this.id + '.upload_certificate_image';
		var certificate_img_src         = 'img#' + this.id + '.llms_certificate_image';
		var certificate_default_img_src = $( 'img#' + this.id + '.llms_certificate_default_image' ).attr( "src" );

		$( certificate_remove_input_id ).val( '' );
		$( certificate_img_src ).attr( 'src', certificate_default_img_src );
	});

});
