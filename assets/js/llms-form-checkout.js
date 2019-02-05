/**
 * LifterLMS Checkout Screen related events and interactions
 *
 * @since    3.0.0
 * @version  3.24.1
 */
( function( $ ) {

	var llms_checkout = function() {

		/**
		 * Array of validation functions to call on form submission
		 * @type    array
		 * @since   3.0.0
		 * @version 3.0.0
		 */
		var before_submit = []; // array of functions to call before submitting the form

		/**
		 * Array of gateways to be automatically bound when needed
		 * @type    array
		 * @since   3.0.0
		 * @version 3.0.0
		 */
		var gateways = [];

		this.$checkout_form = $( '#llms-product-purchase-form' );
		this.$confirm_form = $( '#llms-product-purchase-confirm-form' );
		this.$form_sections = false;
		this.form_action = false;

		/**
		 * Initalize checkout JS & bind if on the checkout screen
		 * @return   void
		 * @since    3.0.0
		 * @version  3.24.1
		 */
		this.init = function() {

			var self = this;

			if ( this.$checkout_form.length ) {

				this.form_action = 'checkout';
				this.$form_sections = this.$checkout_form.find( '.llms-checkout-section' );

				this.$checkout_form.on( 'submit', this, this.submit );

				// add before submit event for password strength meter if one's found
				if ( $( '.llms-password-strength-meter' ).length ) {
					this.add_before_submit_event( {
						data: LLMS.PasswordStrength,
						handler: LLMS.PasswordStrength.checkout,
					} );
				}

				this.bind_login();

				this.bind_coupon();

				this.bind_gateways();

			} else if ( this.$confirm_form.length ) {

				this.form_action = 'confirm';
				this.$form_sections = this.$confirm_form.find( '.llms-checkout-section' );

				this.$confirm_form.on( 'submit', function() {
					self.processing( 'start' );
				} );

			}

		};

		/**
		 * Public function which allows other classes or extensions to add
		 * before submit events to llms checkout private "before_submit" array
		 * @param    object  obj  object of data to push to the array
		 *                        requires at least a "handler" key which should pass a callable function
		 *                        "data" can be anything, will be passed as the first parameter to the handler function
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.add_before_submit_event = function( obj ) {

			if ( !obj.handler || 'function' !== typeof obj.handler ) {
				return;
			}

			if ( !obj.data ) {
				obj.data = null;
			}

			before_submit.push( obj );

		};

		/**
		 * Public function which allows other classes or extensions to add
		 * gateways classes that should be bound by this class
		 * @param    obj   gateway_class  callable class object
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.add_gateway = function( gateway_class ) {

			gateways.push( gateway_class );

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
		 * Bind gateway section events
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind_gateways = function() {

			this.load_gateways();

			if ( ! $( 'input[name="llms_payment_gateway"]').length ) {
				$( '#llms_create_pending_order' ).removeAttr( 'disabled' );
			}

			// add class and trigger watchable event when gateway selection changes
			$( 'input[name="llms_payment_gateway"]' ).on( 'change', function() {

 				$( 'input[name="llms_payment_gateway"]' ).each( function() {

					var $el = $( this ),
						$parent = $el.closest( '.llms-payment-gateway' ),
						$fields = $parent.find( '.llms-gateway-fields' ).find( 'input, textarea, select' ),
						checked = $el.is( ':checked' ),
						display_func = ( checked ) ? 'addClass' : 'removeClass';

					$parent[ display_func ]( 'is-selected' );

					if ( checked ) {

						// enable fields
						$fields.removeAttr( 'disabled' );

						// emit a watchable event for extensions to hook onto
						$( '.llms-payment-gateways' ).trigger( 'llms-gateway-selected', {
							id: $el.val(),
							$selector: $parent,
						} );

					} else {

						// disable fields
						$fields.attr( 'disabled', 'disabled' );

					}

				} );

			} );

			// enable / disable buttons depending on field validation status
			$( '.llms-payment-gateways' ).on( 'llms-gateway-selected', function( e, data ) {

				var $submit = $( '#llms_create_pending_order' );

				if ( data.$selector && data.$selector.find( '.llms-gateway-fields .invalid' ).length ) {
					$submit.attr( 'disabled', 'disabled' );
				} else {
					$submit.removeAttr( 'disabled' );
				}

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

							$errors = $( '<ul class="llms-notice llms-error" />' );
							$messages.append( $errors );

						} else {

							$errors.empty();

						}

						$message.appendTo( $errors );
						$errors.show();

					} else if ( r.success ) {

						$( '.llms-coupon-wrapper' ).replaceWith( r.data.coupon_html );
						self.bind_coupon();

						$( '.llms-payment-gateways' ).replaceWith( r.data.gateways_html );
						self.bind_gateways();

						$( '.llms-order-summary' ).replaceWith( r.data.summary_html );

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

						$( '.llms-payment-gateways' ).replaceWith( r.data.gateways_html );
						self.bind_gateways();

					}

				}

			} );

		};

		/**
		 * Bind external gateway JS
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.load_gateways = function() {

			for ( var i = 0; i <= gateways.length; i++ ) {
				var g = gateways[i];
				if ( typeof g === 'object' && g !== null ) {
					if ( g.bind !== undefined && 'function' === typeof g.bind  ) {
						g.bind();
					}
				}
			}
		};

		/**
		 * Start or stop proccessing events on the checkout form
		 * @param    string   action  whether to start or stop processing [start|stop]
		 * @return   void
		 * @since    3.0.0
		 * @version  3.24.1
		 */
		this.processing = function( action ) {

			var func, $form;

			switch ( action ) {

				case 'stop':
					func = 'removeClass';
				break;

				case 'start':
				default:
					func = 'addClass';
				break;

			}

			if ( 'checkout' === this.form_action ) {
				$form = this.$checkout_form;
			} else if ( 'confirm' === this.form_action ) {
				$form = this.$confirm_form;
			}

			$form[ func ]( 'llms-is-processing' );
			LLMS.Spinner[ action ]( this.$form_sections );

		};

		/**
		 * Handles form submission
		 * Calls all validation events in `before_submit[]`
		 * waits for call backs and either displays returned errors
		 * or submits the form when all are succesful
		 * @param    obj   e  JS event object
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.submit = function( e ) {

			var self = e.data,
				num = before_submit.length,
				checks = 0,
				max_checks = 60000,
				errors = [],
				finishes = 0,
				successes = 0,
				interval;

			e.preventDefault();

			// add spinners
			self.processing( 'start' );

			// remove errors to prevent duplicates
			$( '#llms-checkout-errors' ).remove();

			// start running all the events
			for ( var i = 0; i < before_submit.length; i++ ) {

				var obj = before_submit[ i ];

				obj.handler( obj.data, function( r ) {

					finishes++;
					if ( true === r ) {
						successes++;
					} else if ( 'string' === typeof r ) {
						errors.push( r );
					}

				} );

			}

			// run an interval to wait for finishes
			interval = setInterval( function() {

				var clear = false,
					stop = false;

				// timeout...
				if ( checks >= max_checks ) {

					clear = true;
					stop = true;

				}
				// everything has finished
				else if ( num === finishes ) {

					// all were succesful, submit the form
					if ( num === successes ) {

						clear = true;

						self.$checkout_form.off( 'submit', self.submit );
						self.$checkout_form.trigger( 'submit' );

					} else if ( errors.length ) {

						clear = true;
						stop = true;

						var $err = $( '<ul class="llms-notice llms-error" id="llms-checkout-errors" />')

						for ( var i = 0; i < errors.length; i++ ) {
							$err.append( '<li>' + errors[i] + '</li>' );
						}

						$( '.llms-checkout-wrapper' ).prepend( $err );
						$( 'html, body' ).animate( {
							scrollTop: $err.offset().top - 50,
						}, 200 );

					}

				}

				if ( clear ) {
					clearInterval( interval );
				}

				if ( stop ) {
					self.processing( 'stop' );
				}


				checks++;

			}, 100 );

		};

		// initalize
		this.init();

		return this;

	};

	window.llms = window.llms || {};
	window.llms.checkout = new llms_checkout();

} )( jQuery );
