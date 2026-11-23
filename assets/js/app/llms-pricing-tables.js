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

		this.bind_free_enroll();

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
	 * Submit each free enrollment form only once, so a double click doesn't send a second enrollment request.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	bind_free_enroll: function() {

		$( document ).on( 'submit', '.llms-free-enroll-form', function( e ) {

			var $form = $( this );

			if ( $form.hasClass( 'llms-is-processing' ) ) {
				e.preventDefault();
				return;
			}

			$form.addClass( 'llms-is-processing' ).find( 'button[type="submit"]' ).prop( 'disabled', true );

		} );

		// Re-enable forms when the page is restored from the back/forward cache.
		$( window ).on( 'pageshow', function( e ) {
			if ( e.originalEvent && e.originalEvent.persisted ) {
				$( '.llms-free-enroll-form' ).removeClass( 'llms-is-processing' ).find( 'button[type="submit"]' ).prop( 'disabled', false );
			}
		} );

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
