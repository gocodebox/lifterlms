/**
 * Front End Achievements
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.14.0
 * @since [version] Open from a title button and keep that button out of the modal.
 * @version [version]
 */

LLMS.Achievements = {

	/**
	 * Init
	 *
	 * @since 3.14.0
	 * @since 4.5.1 Fix conditional loading check.
	 *
	 * @return {void}
	 */
	init: function() {

		if ( $( '.llms-achievement' ).length ) {

			var self = this;

			$( function() {
				self.bind();
				self.maybe_open();
			} );
		}

	},

	/**
	 * Bind DOM events
	 *
	 * @since 3.14.0
	 *
	 * @return {void}
	 */
	bind: function() {

		var self = this;

		$( '.llms-achievement' ).each( function() {

			self.create_modal( $( this ) );

		} );

		$( '.llms-achievement' ).on( 'click', function() {

			var $this  = $( this ),
				id     = 'achievement-' + $this.attr( 'data-id' ),
				$modal = $( '#' + id );

			if ( ! $modal.length ) {
				self.create_modal( $this );
			}

			$modal.iziModal( 'open' );

		} );

	},

	/**
	 * Creates modal a modal for an achievement
	 *
	 * @since 3.14.0
	 *
	 * @param obj $el The jQuery selector for the modal card.
	 * @return {void}
	 */
	create_modal: function( $el ) {

		var id     = 'achievement-' + $el.attr( 'data-id' ),
			$modal = $( '#' + id );

		if ( ! $modal.length ) {
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

				var $clone = $el.clone();
				$clone.find( '.llms-achievement-trigger' ).each( function() {
					$( this ).replaceWith( document.createTextNode( $( this ).text() ) );
				} );

				modal.setTitle( $el.find( '.llms-achievement-title' ).text() );
				modal.setSubtitle( $el.find( '.llms-achievement-date' ).html() );
				modal.setContent( '<div class="llms-achievement">' + $clone.html() + '</div>' );

			},

			onClosing: function() {
				window.history.pushState( '', document.title, window.location.pathname + window.location.search );
				var trigger = $el.find( '.llms-achievement-trigger' ).get( 0 );
				if ( trigger ) {
					trigger.focus();
				}
			},

		} );

	},

	/**
	 * On page load, opens a modal if the URL contains an achievement in the location hash
	 *
	 * @since 3.14.0
	 * @since 6.10.2 Sanitize achievement IDs before using window.location.hash to trigger the modal open.
	 *
	 * @return {void}
	 */
	maybe_open: function() {

		let hash = window.location.hash.split( '-' );
		if ( 2 !== hash.length ) {
			return;
		}

		hash[1] = parseInt( hash[1] );
		if ( '#achievement-' !== hash[0] || ! Number.isInteger( hash[1] ) ) {
			return;
		}

		const card = document.querySelector( `.llms-achievement[data-id="${ hash[1] }"]` );
		if ( card ) {
			card.click();
			return;
		}

		// Older template overrides wrap the card in an anchor.
		const a = document.querySelector( `a[href="${ hash.join( '-' ) }"]` );
		if ( ! a ) {
			return;
		}

		a.click();

	}

};
