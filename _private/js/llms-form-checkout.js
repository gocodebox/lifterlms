/**
 * LifterLMS Checkout Screen related events and interactiosn
 * @since    3.0.0
 * @version  3.0.0
 */
( function( $ ) {

	window.llms = window.llms || {};

	window.llms.checkout = function() {

		/**
		 * Initalize checkout JS & bind if on the checkout screen
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.init = function() {

			if ( $( '.llms-checkout-wrapper').length ) {

				this.bind_login();

				this.bind_coupon();

			}

		};

		/**
		 * Bind coupon add & remove button events
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind_coupon = function() {

			var self = this;

			// show & hide the coupon field & button
			$( 'a[href="#llms-coupon-toggle"]' ).on( 'click', function( e ) {

				e.preventDefault();
				$( '.llms-coupon-entry' ).slideToggle( 400 );

			} );

			// apply coupon click
			$( '#llms-apply-coupon' ).on( 'click', function( e ) {

				e.preventDefault();
				self.coupon_apply( $( this ) );

			} );

			// remove coupon click
			$( '#llms-remove-coupon' ).on( 'click', function( e ) {

				e.preventDefault();
				self.coupon_remove( $( this ) );

			} );

		};

		/**
		 * Bind click events for the Show / Hide login area at the top of the checkout screen
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind_login = function() {

			$( 'a[href="#llms-show-login"]' ).on( 'click', function( e ) {

				e.preventDefault();
				$( this ).closest( '.llms-info' ).slideUp( 400 );
				$( 'form.llms-login' ).slideDown( 400 );

			} );
		};

		/**
		 * Triggered by clicking the "Apply Coupon" Button
		 * Validates the coupon via JS and adds error / success messages
		 * On success it will replace partials on the checkout screen with updated
		 * prices and a "remove coupon" button
		 * @param    obj   $btn  jQuery selector of the Apply button
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.coupon_apply = function ( $btn ) {

			var self = this,
				$code = $( '#llms_coupon_code' ),
				code = $code.val(),
				$messages = $( '.llms-coupon-messages' ),
				$errors = $messages.find( '.llms-error' ),
				$container = $( 'form.llms-checkout' );

			LLMS.Spinner.start( $container );

			window.LLMS.Ajax.call( {
				data: {
					action: 'validate_coupon_code',
					code: code,
					plan_id: $( '#llms-plan-id' ).val(),
				},
				beforeSend: function() {

					$errors.hide();

				},
				success: function( r ) {

					LLMS.Spinner.stop( $container );

					if ( 'error' === r.code ) {

						var $message = $( '<li>' + r.message + '</li>');

						if ( ! $errors.length ) {

							$errors = $( '<ul class="llms-error" />' );
							$messages.append( $errors );

						} else {

							$errors.empty();

						}

						$message.appendTo( $errors );
						$errors.show();

					} else if ( r.success ) {

						$( '.llms-coupon-wrapper' ).replaceWith( r.data.coupon_html );
						self.bind_coupon();

						$( '.llms-order-summary' ).replaceWith( r.data.summary_html );
						// self.bind_pricing();

					}

				}

			} );

		};

		/**
		 * Called by clicking the "Remove Coupon" button
		 * Removes the coupon via AJAX and unsets related session data
		 * @param    obj   $btn  jQuery selector of the Remove button
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.coupon_remove = function( $btn ) {

			var self = this,
				$container = $( 'form.llms-checkout' );

			LLMS.Spinner.start( $container );

			window.LLMS.Ajax.call( {
				data: {
					action: 'remove_coupon_code',
					plan_id: $( '#llms-plan-id' ).val(),
				},
				success: function( r ) {

					LLMS.Spinner.stop( $container );

					if ( r.success ) {

						$( '.llms-coupon-wrapper' ).replaceWith( r.data.coupon_html );
						self.bind_coupon();

						$( '.llms-order-summary' ).replaceWith( r.data.summary_html );
						// self.bind_pricing();

					}

				}

			} );

		};

		// initalize
		this.init();

		return this;

	};

	var a = new window.llms.checkout();

} )( jQuery );
