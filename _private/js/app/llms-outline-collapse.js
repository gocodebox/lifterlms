/* global LLMS, $ */
/* jshint strict: false */

/**
 * Handle the Collpasible Syllabus Widget / Shortcode
 */
LLMS.OutlineCollapse = {

	/**
	 * jQuery object of all outlines present on the current screen
	 * @type obj
	 */
	$outlines: null,

	/**
	 * Initilize
	 * @return void
	 */
	init: function() {

		this.$outlines = $( '.llms-widget-syllabus--collapsible' );

		if ( this.$outlines.length ) {

			this.bind();

		}

	},

	/**
	 * Bind DOM events
	 * @return void
	 */
	bind: function() {

		this.$outlines.each( function() {

			var $outline = $( this ),
				state, add, remove;

			$outline.find( '.llms-section .section-header' ).on( 'click', function() {

				var $toggle = $( this ),
					$section = $toggle.closest( '.llms-section' );

				state = $section.hasClass( 'llms-section--opened' ) ? 'opened' : 'closed';
				add = ( 'opened' === state ) ? 'closed' : 'opened';
				remove = ( 'opened' === state ) ? 'opened' : 'closed';

				$section.removeClass( 'llms-section--' + remove ).addClass( 'llms-section--' + add );

			} );

		} );

	},

};
