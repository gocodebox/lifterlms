/* global LLMS, $ */
/* jshint strict: false */
/**
 * Pricing Table UI
 */
LLMS.Pricing_Tables = {

	/**
	 * init
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

				self.bind_locked();

			}

		}

	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 * @return {[type]} [description]
	 */
	bind: function() {

		$( '.llms-access-plan-content' ).matchHeight();
		$( '.llms-access-plan-pricing.trial' ).matchHeight();

	},

	/**
	 * Bind DOM events for locked plans
	 * @return void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	bind_locked: function() {

		var self = this;

		this.$locked.on( 'click', function() {
			return false;
		} );

		this.$locked.on( 'mouseenter', function() {

			var $tip = $( this ).find( '.llms-tooltip' );
			if ( !$tip.length ) {
				$tip = self.get_tooltip();
				$( this ).append( $tip );
			}
			setTimeout( function() {
				$tip.addClass( 'show' );
			}, 10 );

		} );

		this.$locked.on( 'mouseleave', function() {

			var $tip = $( this ).find( '.llms-tooltip' );
			$tip.removeClass( 'show' );

		} );

	},

	/**
	 * Get a tooltip element
	 * @return   obj
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	get_tooltip: function() {
		var msg = LLMS.l10n.translate( 'This plan is for members only. Click the links above to learn more.' ),
			$el = $( '<div class="llms-tooltip" />' );

		$el.append( '<div class="llms-tooltip-content">' + msg + '</div>' );

		return $el;
	},

};
