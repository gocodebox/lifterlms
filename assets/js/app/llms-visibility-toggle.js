/**
 * Handle Password Visibility Toggle for LifterLMS Forms
 *
 * @package LifterLMS/Scripts
 *
 * @since TBD
 */

LLMS.PasswordVisibility = {

	/**
	 * Initialize references and setup event binding
	 *
	 * @since TBD
	 * @return void
	 */
	init: function() {
		this.$toggleButtons = $( '.llms-visibility-toggle button' );

		if ( this.$toggleButtons.length ) {
			this.$toggleButtons.removeClass( 'hide-if-no-js' );
			this.bind();
		}
	},

	/**
	 * Bind DOM events for toggle buttons
	 *
	 * @since TBD
	 * @return void
	 */
	bind: function() {
		var self = this;

		// Remove any previous click events and bind the new click event
		this.$toggleButtons.off('click').on('click', function(event) {
			self.toggleVisibility( $(this) );
		});
	},

	/**
	 * Toggle visibility of password fields
	 *
	 * @since TBD
	 * @param {Object} $button The jQuery object of the clicked button
	 * @return void
	 */
	toggleVisibility: function( $button ) {
		var isVisible = parseInt( $button.attr('data-toggle'), 10 );
		var $form = $button.closest( '.llms-form-fields' );
		var $passwordFields = $form.find( 'input.llms-field-input' );
		var $icon = $button.find( 'i' );
		var $stateText = $button.find( '.llms-visibility-toggle-state' );

		// Toggle the visibility state
		if ( isVisible === 1 ) {
			// Show password
			$passwordFields.filter('[type="password"]').attr('type', 'text');
			$button.attr('data-toggle', 0);
			$icon.removeClass('fa-eye').addClass('fa-eye-slash');
			$stateText.text(LLMS.l10n.translate('Hide Password'));
		} else {
			// Hide password
			$passwordFields.filter('[type="text"]').attr('type', 'password');
			$button.attr('data-toggle', 1);
			$icon.removeClass('fa-eye-slash').addClass('fa-eye');
			$stateText.text(LLMS.l10n.translate('Show Password'));
		}
	}

};

// Initialize the Password Visibility module on document ready
jQuery(document).ready(function($) {
	LLMS.PasswordVisibility.init();
});
