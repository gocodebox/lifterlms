/**
 * LifterLMS Settings Pages UI / UX
 *
 * @since 3.7.3
 * @version [version]
 */

( function( $, undefined ) {

	window.llms                = window.llms || {};
	window.llms.admin_settings = function() {

		/**
		 * Initialize
		 *
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
		 *
		 * @return   void
		 * @since    3.7.3
		 * @version  3.17.5
		 */
		this.bind = function() {

			var self = this;

			if ( $( '.llms-gateway-table' ).length ) {
				$( '.llms-gateway-table tbody' ).sortable( {
					axis: 'y',
					cursor: 'move',
					items: 'tr',
					// containment: 'parent',
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
		 *
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
		 *
		 * @since 3.7.3
		 * @deprecated [version]
		 *
		 * @param {Object} $btn The jQuery object for clicked button.
		 * @param {Object} e    JS event object.
		 * @return {Void}
		 */
		this.image_upload_click = function( $btn, e ) {
			console.log( 'llms.admin_settings.image_upload_click() is deprecated since [version]! Use llms.admin_fields.image.open_media_lib() instead.' );
			return window.llms.admin_fields.image.open_media_lib( $btn );
		};

		/**
		 * Update the DOM with a selected image
		 *
		 * @since 3.7.3
		 * @deprecated [version]
		 *
		 * @param {Object}  $btn The jQuery object of the clicked button.
		 * @param {Integer} id   WP Attachment ID of the image.
		 * @param {String}  src  Image element src of the selected image.
		 * @return {Void}
		 */
		this.update_image = function( $btn, id, src ) {
			console.log( 'llms.admin_settings.update_image() is deprecated since [version]! Use llms.admin_fields.image.update_image() instead.' );
			return window.llms.admin_fields.image.update_image( $btn, id, src );
		};

		// Go.
		this.init();

	};

	new window.llms.admin_settings();

} )( jQuery );
