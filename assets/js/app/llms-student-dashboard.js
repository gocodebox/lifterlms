/**
 * Student Dashboard related JS
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.7.0
 * @since 3.10.0 Unknown.
 * @since [version] Added logic to make the email address confirm field required only if email is different from the original value.
 * @version [version]
 */

LLMS.StudentDashboard = {

	/**
	 * Slug for the current screen/endpoint
	 *
	 * @type  {String}
	 */
	screen: '',

	/**
	 * Will show the number of meters on the page
	 * Used to conditionally bind meter-related events only when meters
	 * actually exist
	 *
	 * @type  int
	 */
	meter_exists: 0,

	/**
	 * Init.
	 *
	 * @since 3.7.0
	 * @since 3.10.0 Unknown.
	 * @since [version] Treat the case when we're on edit-account screen and we need to make the email address confirm field required.
	 *
	 * @return void
	 */
	init: function() {

		if ( $( '.llms-student-dashboard' ).length ) {

			this.meter_exists = $( '.llms-password-strength-meter' ).length;
			this.bind();

			if ( 'orders' === this.get_screen() ) {

				this.bind_orders();

			} else if ( 'edit-account' === this.get_screen() ) {

				if ( $( '#email_address_confirm' ).length && 'required' !== $( '#email_address_confirm' ).attr( 'required' ) ) {
					$( '#email_address' ).data( 'llms-original-email', $( '#email_address' ).val() );
					this.bind_email_fields();
				}

			}

		}

	},

	/**
	 * Bind DOM events.
	 *
	 * @since 3.7.0
	 * @since 3.7.4 Unknown.
	 *
	 * @return void
	 */
	bind: function() {

		var self    = this,
			$toggle = $( '.llms-student-dashboard a[href="#llms-password-change-toggle"]' );

		// click event for the change password link
		$toggle.on( 'click', function( e ) {

			e.preventDefault();

			var $this       = $( this ),
				curr_text   = $this.text(),
				curr_action = $this.attr( 'data-action' ),
				new_action  = 'hide' === curr_action ? 'show' : 'hide',
				new_text    = $this.attr( 'data-text' );

			self.password_toggle( curr_action );

			// prevent accidental cancels when users tab out of the confirm password field
			// and expect to hit submit with enter key immediately after
			if ( 'show' === curr_action ) {
				$this.attr( 'tabindex', '-777' );
			} else {
				$this.removeAttr( 'tabindex' );
			}

			$this.attr( 'data-action', new_action ).attr( 'data-text', curr_text ).text( new_text );

		} );

		// this will remove the required by default without having to mess with
		// conditionals in PHP and still allows the required * to show in the label
		if ( this.meter_exists ) {

			$( '.llms-person-form.edit-account' ).on( 'llms-password-strength-ready', function() {
				self.password_toggle( 'hide' );
			} );

		} else {

			self.password_toggle( 'hide' );

		}

		$( '.llms-donut' ).each( function() {
			LLMS.Donut( $( this ) );
		} );

	},

	/**
	 * Bind events related to the orders screen on the dashboard.
	 *
	 * @since 3.10.0
	 *
	 * @return void
	 */
	bind_orders: function() {

		$( '#llms-cancel-subscription-form' ).on( 'submit', this.order_cancel_warning );
		$( '#llms_update_payment_method' ).on( 'click', function() {
			$( 'input[name="llms_payment_gateway"]:checked' ).trigger( 'change' );
			$( this ).closest( 'form' ).find( '.llms-switch-payment-source-main' ).slideToggle( '200' );
		} );

	},


	/**
	 * Bind events related to the email fields on the dashboard's edit account screen.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	bind_email_fields: function() {

		var $email_confirm = $( '#email_address_confirm' ),
			$email         = $( '#email_address' );

		function maybe_require_email_address_confirm() {
			if ( $email.data( 'llms-original-email' ) !== $email.val() ) {
				if ( 'required' !== $email_confirm.attr( 'required' ) ) {
					$( '<span class="llms-required">*</span>' ).insertBefore( $email_confirm.attr( 'required', 'required' ) );
				}
			} else {
				$email_confirm.removeAttr( 'required' ).prev( '.llms-required' ).remove();
			}
		}

		$email.on( 'focusout', maybe_require_email_address_confirm )
			  .closest( 'form' ).submit( function() {
				  maybe_require_email_address_confirm();
				  return true;
			  });

	},


	/**
	 * Get the current dashboard endpoint/tab slug.
	 *
	 * @since 3.10.0
	 *
	 * @return void
	 */
	get_screen: function() {
		if ( ! this.screen ) {
			this.screen = $( '.llms-student-dashboard' ).attr( 'data-current' );
		}
		return this.screen;
	},

	/**
	 * Show a confirmation warning when Cancel Subscription form is submitted.
	 *
	 * @since 3.10.0
	 *
	 * @param obj e JS event data.
	 * @return void
	 */
	order_cancel_warning: function( e ) {
		e.preventDefault();
		var msg = LLMS.l10n.translate( 'Are you sure you want to cancel your subscription?' );
		if ( window.confirm( LLMS.l10n.translate( msg ) ) ) {
			$( this ).off( 'submit', this.order_cancel_warning );
			$( this ).submit();
		}
	},

	/**
	 * Toggle password related fields on the account edit page.
	 *
	 * @since 3.7.0
	 * @since 3.7.4 Unknown.
	 *
	 * @param string action[show|hide]
	 * @return void
	 */
	password_toggle: function( action ) {

		if ( ! action ) {
			action = 'show';
		}

		var self  = this,
			$pwds = $( '#password, #password_confirm, #current_password' ),
			$form = $( '#password' ).closest( 'form' );

		// hide or show the fields
		$( '.llms-change-password' )[ action ]();

		if ( 'show' === action ) {
			// make passwords required
			$pwds.attr( 'required', 'required' );

			if ( self.meter_exists ) {
				// add the strength check on form submission
				$form.on( 'submit', LLMS.PasswordStrength, LLMS.PasswordStrength.submit );
			}

		} else {
			// remove requirement so form can be submitted while fields are hidden
			// and clear the password out of the fields if typing started
			$pwds.removeAttr( 'required' ).val( '' );

			if ( self.meter_exists ) {

				// remove the password strength submission check
				$form.off( 'submit', LLMS.PasswordStrength.submit );
				// clears the meter
				LLMS.PasswordStrength.check_strength();

			}

		}

	},

};
