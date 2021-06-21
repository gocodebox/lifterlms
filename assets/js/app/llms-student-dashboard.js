/**
 * Student Dashboard related JS
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.7.0
 * @since 3.10.0 Bind events on the orders screen.
 * @since 5.0.0 Removed redundant password toggle logic for edit account screen.
 * @version 5.0.0
 */
LLMS.StudentDashboard = {

	/**
	 * Slug for the current screen/endpoint
	 *
	 * @type  {String}
	 */
	screen: '',

	/**
	 * Init
	 *
	 * @since 3.7.0
	 * @since 3.10.0 Unknown
	 * @since 5.0.0 Removed password toggle logic.
	 *
	 * @return void
	 */
	init: function() {

		if ( $( '.llms-student-dashboard' ).length ) {
			this.bind();
			if ( 'orders' === this.get_screen() ) {
				this.bind_orders();
			}
		}

	},

	/**
	 * Bind DOM events
	 *
	 * @since 3.7.0
	 * @since 3.7.4 Unknown.
	 * @since 5.0.0 Removed password toggle logic.
	 *
	 * @return   void
	 */
	bind: function() {

		$( '.llms-donut' ).each( function() {
			LLMS.Donut( $( this ) );
		} );

	},

	/**
	 * Bind events related to the orders screen on the dashboard
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
	 * Get the current dashboard endpoint/tab slug
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
	 * Show a confirmation warning when Cancel Subscription form is submitted
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

};
