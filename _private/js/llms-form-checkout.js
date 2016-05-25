( function( $ ) {

	window.llms = window.llms || {};

	window.llms.checkout = function() {


		/**
		 * Init
		 * @return void
		 */
		this.init = function() {

			this.bind();
			this.bind_coupon();
			this.bind_pricing();

			// set price on init
			this.set_price();

			// trigger gateway change on load
			$( '.llms-payment-methods input[type=radio]:checked' ).change();

			// ensure user fields are set correctly on init
			this.init_user_fields();

		};




		this.bind = function() {

			var self = this;

			// initialize chosen on the country fields
			$( '#llms_country_options' ).chosen( {
				width: "100%",
			} );

			$( '.llms-toggle' ).on( 'click', function( e ) {

				e.preventDefault();
				self.toggle_user_fields( $( this ) );

			} );

			/**
			 * This piece of code (or something like it) should be what other gateways use to hide or show their forms
			 *
			 */
			$( document ).on( 'llms-payment-method-change', function( e, data ) {

				// this is being used for infusionsoft only at this very moment
				// @todo at a certain point this should be added to Infusionsoft & removed
				if ( 'creditcard' === data.type ) {

					$('.llms-creditcard-fields').slideDown( 400 );

				} else {

					$('.llms-creditcard-fields').slideUp( 400 );

				}

			} );

		};


		this.bind_coupon= function() {

			var self = this;

			// toggle the display of the coupon area
			$( '.llms-coupon-toggle-button' ).on( 'click', function( e ) {
				e.preventDefault();

				var $el = $( this ),
					$box = $el.closest( '.llms-coupon-entry' )
					$input = $( '#llms-coupon-code' );

				$box.toggleClass( 'active' );

				if ( $input.attr( 'disabled' ) ) {
					$input.removeAttr( 'disabled' );
				} else {
					$input.attr( 'disabled', true );
				}

			} );

			$( '#llms-apply-coupon' ).on( 'click', function( e ) {

				e.preventDefault();
				self.coupon_apply( $( this ) );

			} );

			$( '#llms-remove-coupon' ).on( 'click', function( e ) {

				e.preventDefault();
				self.coupon_remove( $( this ) );

			} );

		};


		this.bind_pricing = function() {

			var self = this;

			// change display price when price radio element changes
			$( '.llms-payment-options input[type=radio]' ).on( 'change', function() {
				self.set_price();
			} );


			// maybe display additional information based on the payment gateway
			$('.llms-payment-methods input[type=radio]').on( 'change', function() {

				var $el = $( this );

				// trigger an event that extensions can hook into to hide or show their forms / necessary data
				$( document ).trigger( 'llms-payment-method-change', {
					gateway: $el.val(),
					type: $el.attr( 'data-payment-type' ),
				} );

			} );

			// trigger change on the checked element after bind
			// this will refresh the html in the "You Pay" area at the bottom of the form
			// which will need a refreshing after coupon application
			$( '.llms-payment-options input[type=radio]:checked' ).trigger( 'change' );

		};


		this.coupon_apply = function ( $btn ) {

			var self = this,
				$code = $( '#llms-coupon-code' ),
				code = $code.val(),
				$messages = $( '.llms-coupon-notice' ),
				$errors = $messages.find( '.llms-error' ),
				$container = $( '.llms-coupon-entry' );

			LLMS.Spinner.start( $container );

			window.LLMS.Ajax.call( {
				data: {
					action: 'validate_coupon_code',
					code: code,
					product_id: $( '#llms-product-id' ).val(),
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

						$( '#llms-coupon-form' ).replaceWith( r.data.coupon_html );
						self.bind_coupon();

						$( '#llms-payment-options' ).replaceWith( r.data.pricing_html );
						self.bind_pricing();

					}

				}

			} );

		};


		this.coupon_remove = function( $btn ) {

			var self = this,
				$container = $( '.llms-coupon-entry' );

			LLMS.Spinner.start( $container );

			window.LLMS.Ajax.call( {
				data: {
					action: 'remove_coupon_code',
					product_id: $( '#llms-product-id' ).val(),
				},
				success: function( r ) {

					LLMS.Spinner.stop( $container );

					if ( r.success ) {

						$( '#llms-coupon-form' ).replaceWith( r.data.coupon_html );
						self.bind_coupon();

						$( '#llms-payment-options' ).replaceWith( r.data.pricing_html );
						self.bind_pricing();

					}

				}

			} );

		};


		this.hide_user_fields = function( $fields ) {

			$fields.removeClass( 'active' ).find( 'input', 'select', 'textarea' ).attr( 'disabled', true );

		};

		this.init_user_fields = function() {

			var self = this;

			$( '.llms-user-fields' ).each( function() {

				var $el = $( this );

				if( $el.hasClass( 'active' ) ) {

					self.show_user_fields( $el );

				} else {

					self.hide_user_fields( $el );

				}

			} );

		};

		this.show_user_fields = function( $fields ) {

			$fields.addClass( 'active' ).find( 'input', 'select', 'textarea' ).removeAttr( 'disabled' );

		};

		this.toggle_user_fields = function( $btn ) {

			var current = $btn.closest( '.llms-user-fields' ).attr('id'),
				$hide = $( '#' + current ),
				$show = ( 'llms-login-fields' === current ) ? $( '#llms-register-fields' ) : $( '#llms-login-fields' );

			this.hide_user_fields( $hide );
			this.show_user_fields( $show );

		};


		/**
		 * Set the price next to "You pay" based on the value of currently checked price option
		 */
		this.set_price = function() {

			var $selected = $( '.llms-payment-options input[type=radio]:checked' ),
				price = $selected.parent().find('label').html();

			$( '.llms-final-price' ).html( price );

		};

		this.init();

		return this;

	};

	var a = new window.llms.checkout();

} )( jQuery );
