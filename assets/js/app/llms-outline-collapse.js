/* global LLMS, $ */

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

		var self = this;

		this.$outlines.each( function() {

			var $outline = $( this ),
				$headers = $outline.find( '.llms-section .section-header' );

			// bind header clicks
			$headers.on( 'click', function( e ) {

				e.preventDefault();

				var $toggle = $( this ),
					$section = $toggle.closest( '.llms-section' ),
					state = self.get_section_state( $section );

				switch( state ) {

					case 'closed':
						self.open_section( $section );
					break;

					case 'opened':
						self.close_section( $section );
					break;

				}

			} );

			// bind optional toggle "buttons"
			$outline.find( '.llms-collapse-toggle' ).on( 'click', function( e ) {

				e.preventDefault();

				var $btn = $( this ),
					action = $btn.attr( 'data-action' ),
					opposite_action = ( 'close' === action ) ? 'opened' : 'closed';

				$headers.each( function() {

					var $section = $( this ).closest( '.llms-section' ),
						state = self.get_section_state( $section );

					if ( opposite_action !== state ) {
						return true;
					}

					switch( state ) {

						case 'closed':
							self.close_section( $section );
						break;

						case 'opened':
							self.open_section( $section );
						break;

					}

					$( this ).trigger( 'click' );

				} );

			} );

		} );

	},

	/**
	 * Close an outline section
	 * @param  obj    $section   jQuery selector of a '.llms-section'
	 * @return void
	 */
	close_section: function( $section ) {

		$section.removeClass( 'llms-section--opened' ).addClass( 'llms-section--closed' );

	},

	/**
	 * Open an outline section
	 * @param  obj    $section   jQuery selector of a '.llms-section'
	 * @return void
	 */
	open_section: function( $section ) {

		$section.removeClass( 'llms-section--closed' ).addClass( 'llms-section--opened' );

	},

	/**
	 * Get the current state (open or closed) of an outline section
	 * @param  obj    $section   jQuery selector of a '.llms-section'
	 * @return string            'opened' or 'closed'
	 */
	get_section_state: function( $section ) {

		return $section.hasClass( 'llms-section--opened' ) ? 'opened' : 'closed';

	}

};
