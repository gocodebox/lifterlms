/**
 * Handle Lesson Preview Elements
 *
 * @package LifterLMS/Scripts
 *
 * @since    3.0.0
 * @version  3.16.12
 */

LLMS.LessonPreview = {

	/**
	 * A jQuery object of all outlines present on the current screen
	 *
	 * @type obj
	 */
	$els: null,

	/**
	 * Initialize
	 *
	 * @return void
	 */
	init: function() {

		var self = this;

		this.$locked = $( 'a[href="#llms-lesson-locked"]' );

		if ( this.$locked.length ) {

			self.bind();

		}

		if ( $( '.llms-course-navigation' ).length ) {

			LLMS.wait_for_matchHeight( function() {

				self.match_height();

			} );

		}

	},

	/**
	 * Bind DOM events
	 *
	 * @return void
	 * @since    3.0.0
	 * @version  3.16.12
	 */
	bind: function() {

		var self = this;

		this.$locked.on( 'click', function() {
			return false;
		} );

		this.$locked.on( 'mouseenter', function() {

			var $tip = $( this ).find( '.llms-tooltip' );
			if ( ! $tip.length ) {
				var msg = $( this ).attr( 'data-tooltip-msg' );
				if ( ! msg ) {
					msg = LLMS.l10n.translate( 'You do not have permission to access this content' );
				}
				$tip = self.get_tooltip( msg );
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
	 * Match the height of lesson preview items in course navigation blocks
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	match_height: function() {

		$( '.llms-course-navigation .llms-lesson-link' ).matchHeight();

	},

	/**
	 * Get a tooltip element
	 *
	 * @param    string   msg   message to display inside the tooltip
	 * @return   obj
	 * @since    3.0.0
	 * @version  3.2.4
	 */
	get_tooltip: function( msg ) {
		var $el = $( '<div class="llms-tooltip" />' );
		$el.append( '<div class="llms-tooltip-content">' + msg + '</div>' );
		return $el;
	},

};
