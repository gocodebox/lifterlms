/* global LLMS, $ */
/* jshint strict: false */

/**
 * Front End Achievements
 * @type     {Object}
 * @since    [version]
 * @version  [version]
 */
LLMS.Achievements = {

	init: function() {

		var self = this;

		if ( $( '.llms-achievement' ) ) {
			$( document ).on( 'ready', function() {
				self.bind();
				self.maybe_open();
			} );
		}

	},

	bind: function() {

		var self = this;

		$( '.llms-achievement' ).each( function() {

			self.create_modal( $( this ) );

		} );

		$( '.llms-achievement' ).on( 'click', function() {

			var $this = $( this ),
				id = 'llms-achievement-modal-' + $this.attr( 'data-id' ),
				$modal = $( '#' + id );

			if ( !$modal.length ) {
				self.create_modal( $this );
			}

			$modal.iziModal( 'open' );

		} );

	},

	create_modal: function( $el ) {

		var id = 'llms-achievement-modal-' + $el.attr( 'data-id' ),
			$modal = $( '#' + id );

		if ( !$modal.length ) {
			$modal = $( '<div class="llms-achievement-modal" id="' + id + '" />' );
			$( 'body' ).append( $modal );
		}

		$modal.iziModal( {
			headerColor: '#3a3a3a',
			group: 'achievements',
			history: true,
			loop: true,
			overlayColor: 'rgba( 0, 0, 0, 0.6 )',
			transitionIn: 'fadeInDown',
			transitionOut: 'fadeOutDown',
			width: 340,
			onOpening: function( modal ) {

				modal.setTitle( $el.find( '.llms-achievement-title' ).html() );
				modal.setSubtitle( $el.find( '.llms-achievement-date' ).html() );
				modal.setContent( '<div class="llms-achievement">' + $el.html() + '</div>' );

			},

		} );

	},

	maybe_open: function() {

		var hash = window.location.hash;
		if ( hash && -1 !== hash.indexOf( 'llms-achievement-modal' ) ) {
			$( 'a[href="' + hash + '"]').trigger( 'click' );
		}

	}

};
