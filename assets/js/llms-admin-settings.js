/**
 * LifterLMS Settings Pages UI / UX
 * @since    3.7.3
 * @version  3.18.0
 */
;( function( $, undefined ) {

	window.llms = window.llms || {};
	window.llms.admin_settings = function() {

		this.file_frame = null;

		/**
		 * Initialize
		 * @return   void
		 * @since    3.7.3
		 * @version  3.18.0
		 */
		this.init = function() {
			this.bind();
			this.bind_conditionals();
		};

		/**
		 * Bind DOM events
		 * @return   void
		 * @since    3.7.3
		 * @version  3.17.5
		 */
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

			if ( $( '.llms-gateway-table' ).length ) {
				$( '.llms-gateway-table tbody' ).sortable( {
					axis: 'y',
					cursor: 'move',
					items: 'tr',
					// containtment: 'parent',
					handle: 'td.sort',
					helper: function( event, ui ) {
						ui.children().each( function() {
							$( this ).width( $( this ).width() );
						} );
						// ui.css( 'left', '0' );
						return ui;
					},
					update: function( event, ui ) {
						$( this ).find( 'td.sort input' ).each( function( i ) {
							$( this ).val( i );
						} );
					}
				} );
			}

		};

		/**
		 * Allow checkboxes to conditionally display other settings
		 * @return   void
		 * @since    3.18.0
		 * @version  3.18.0
		 */
		this.bind_conditionals = function() {

			$( '.llms-conditional-controller' ).each( function() {

				var $controls = $( $( this ).attr( 'data-controls' ) ).closest( 'tr' );

				$( this ).on( 'change', function() {

					var val;

					if ( 'checkbox' === $( this ).attr( 'type' ) ) {
						val = $( this ).is( ':checked' );
					}

					if ( val ) {
						$controls.show();
					} else {
						$controls.hide();
					}

				} ).trigger( 'change' );

			} );


		};

		/**
		 * Click event for image upload fields
		 * @param    obj   $btn  jQuery object for clicked button
		 * @param    obj   e     JS event object
		 * @return   void
		 * @since    3.7.3
		 * @version  3.7.3
		 */
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

		/**
		 * Update the DOM with a selected image
		 * @param    obj      $btn  jQuery object of the clicked button
		 * @param    int      id    WP Attachment ID of the image
		 * @param    string   src   <img> src of the selected image
		 * @return   void
		 * @since    3.7.3
		 * @version  3.7.3
		 */
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
