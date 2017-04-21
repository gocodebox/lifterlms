/* global LLMS, $ */

/**
 * Student Dashboard related JS
 * @type  {Object}
 * @since    3.7.0
 * @version  3.7.4
 */
LLMS.StudentDashboard = {

	/**
	 * Will show the number of meters on the page
	 * Used to conditionally bind meter-related events only when meters
	 * actually exist
	 * @type  int
	 */
	meter_exists: 0,

	/**
	 * Init
	 * @return   void
	 * @since    3.7.0
	 * @version  3.7.4
	 */
	init: function() {

		if ( $( '.llms-student-dashboard' ).length ) {
			this.meter_exists = $( '.llms-password-strength-meter' ).length;
			this.bind();
		}

	},

	/**
	 * Bind DOM events
	 * @return   void
	 * @since    3.7.0
	 * @version  3.7.4
	 */
	bind: function() {

		var self = this,
			$toggle = $( '.llms-student-dashboard a[href="#llms-password-change-toggle"]' );

		// click event for the change password link
		$toggle.on( 'click', function( e ) {

			e.preventDefault();

			var $this = $( this ),
				curr_text = $this.text(),
				curr_action = $this.attr( 'data-action' ),
				new_action = 'hide' === curr_action ? 'show' : 'hide',
				new_text = $this.attr( 'data-text' );

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

	},

	/**
	 * Toggle password related fields on the account edit page
	 * @param    string   action  [show|hide]
	 * @return   void
	 * @since    3.7.0
	 * @version  3.7.4
	 */
	password_toggle: function( action ) {

		if ( !action ) {
			action = 'show';
		}

		var self = this,
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
