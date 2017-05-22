;( function( $, undefined ) {

	window.llms = window.llms || {};

	window.llms.admin_settings = function() {

		this.file_frame = null;

		this.init = function() {
			this.bind();
		};

		this.bind = function() {

			var self = this;

			if ( $( '.llms-image-field-upload' ).length ) {
				$( '.llms-image-field-upload' ).on( 'click', function( e ) {
					e.preventDefault();
					self.image_upload_click( $( this ), e );
				} );

				$( '.llms-image-field-remove' ).on( 'click', function( e ) {
					e.preventDefault();
					self.update_image( $( this ), '', '' );
				} );
			}

		};

		this.image_upload_click = function( $btn, e ) {

			var self = this,
				frame = null;

			if ( ! frame ) {
				var title = $btn.attr( 'data-frame-title' ) || LLMS.l10n.translate( 'Select an Image' ),
					button_text = $btn.attr( 'data-frame-button' ) || LLMS.l10n.translate( 'Select Image' );
				frame = wp.media.frames.file_frame = wp.media({
					title: title,
					button: {
						text: button_text,
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
			}

			frame.on( 'select', function() {

				// We set multiple to false so only get one image from the uploader
				var attachment = frame.state().get('selection').first().toJSON();

				self.update_image( $btn, attachment.id, attachment.url );

			});

			frame.open();

		};

		this.update_image = function( $btn, id, src ) {

			var $input = $( '#' + $btn.attr( 'data-id' ) ),
				$preview = $btn.prevAll( 'img.llms-image-field-preview' )
				$remove = $btn.hasClass( 'llms-image-field-remove' ) ? $btn : $btn.next( 'input.llms-image-field-remove' );

			$input.val( id );
			$preview.attr( 'src', src );

			if ( '' !== id ) {
				$remove.removeClass( 'hidden' );
			} else {
				$remove.addClass( 'hidden' );
			}


		}

		// go
		this.init();

	};

	var a = new window.llms.admin_settings();

} )( jQuery );
