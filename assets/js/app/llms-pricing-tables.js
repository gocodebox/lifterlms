/**
 * Pricing Table UI
 *
 * @package LifterLMS/Scripts
 *
 * @since  Unknown.
 * @version  Unknown.
 */

LLMS.Pricing_Tables = {

	/**
	 * Init
	 */
	init: function() {

		var self = this;

		if ( $( 'body' ).hasClass( 'wp-admin' ) ) {
			return;
		}

		if ( $( '.llms-access-plans' ).length ) {

			LLMS.wait_for_matchHeight( function() {
				self.bind();
			} );

			this.$locked = $( 'a[href="#llms-plan-locked"]' );

			if ( this.$locked.length ) {

				LLMS.wait_for_popover( function() {
					self.bind_locked();
				} );

			}

		}

	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 *
	 * @return {[type]} [description]
	 */
	bind: function() {

		$( '.llms-access-plan-content' ).matchHeight();
		$( '.llms-access-plan-pricing.trial' ).matchHeight();

	},

	/**
	 * Setup a popover for members-only restricted plans
	 *
	 * @return void
	 * @since    3.0.0
	 * @version  3.9.1
	 */
	bind_locked: function() {

		this.$locked.each( function() {

			$( this ).webuiPopover( {
				animation: 'pop',
				closeable: true,
				content: function( e ) {
					var $content = $( '<div class="llms-members-only-restrictions" />' );
					$content.append( e.$element.closest( '.llms-access-plan' ).find( '.llms-access-plan-restrictions ul' ).clone() );
					return $content;
				},
				placement: 'top',
				style: 'inverse',
				title: LLMS.l10n.translate( 'Members Only Pricing' ),
				width: '280px',
			} );

		} );

	},

};
