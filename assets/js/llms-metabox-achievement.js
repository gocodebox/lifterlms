jQuery( document ).ready(function($){

	$( '.achievement_image_button' ).click(function(e) {

		// Create Media Manager On Click to allow multiple on one Page
		var achievement_uploader;

		e.preventDefault();

		// Setup the Variables based on the Button Clicked to enable multiple
		var achievement_img_input_id = '#' + this.id + '.upload_achievement_image';
		var achievement_img_src      = 'img#' + this.id + '.llms_achievement_image';

		// If the uploader object has already been created, reopen the dialog
		if (achievement_uploader) {
			achievement_uploader.open();
			return;
		}

		// Extend the wp.media object
		achievement_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Achievement Image',
			button: {
				text: 'Choose Achievement'
			},
			multiple: false
		});

		// When a file is selected, grab the URL and set it as the text field's value
		achievement_uploader.on('select', function() {
			attachment = achievement_uploader.state().get( 'selection' ).first().toJSON();
			// Set the Field with the Image ID
			$( achievement_img_input_id ).val( attachment.id );
			// Set the Sample Image with the URL
			$( achievement_img_src ).attr( 'src', attachment.url );

		});

		// Open the uploader dialog
		achievement_uploader.open();

	});

});
/*
* Media Manager 3.5
* @version 1.70
*/
jQuery( document ).ready(function($){
	// Remove Image and replace with default and Erase Image ID for achievement
	$( '.llms_achievement_clear_image_button' ).click(function(e) {
		e.preventDefault();
		var achievement_remove_input_id = 'input#' + this.id + '.upload_achievement_image';
		var achievement_img_src         = 'img#' + this.id + '.llms_achievement_image';
		var achievement_default_img_src = $( 'img#' + this.id + '.llms_achievement_default_image' ).attr( "src" );

		$( achievement_remove_input_id ).val( '' );
		$( achievement_img_src ).attr( 'src', achievement_default_img_src );
	});

});
