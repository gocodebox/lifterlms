( function( $ ) {

	window.llms = window.llms || {};

	window.llms.checkout = function() {


		/**
		 * Init
		 * @return void
		 */
		this.init = function() {

			this.bind();

			// set price on init
			this.set_price();

			// trigger gateay change on load
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

			// change display price when price radio element changes
			$( '.llms-payment-options input[type=radio]' ).on( 'change', function() {
				self.set_price();
			} );

			// toggle the display of the coupon area
			$( '.llms-coupon-toggle-button' ).on( 'click', function( e ) {
				e.preventDefault();

				var $el = $( this ),
					$box = $el.closest( '.llms-coupon-entry' );

				$box.toggleClass( 'active' );

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
