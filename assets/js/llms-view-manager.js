/**
 * JS events for the view manager
 * @since    3.8.0
 * @version  3.8.0
 */
;( function( $, undefined ) {

	window.llms = window.llms || {};

	var ViewManager = function() {

		var currentView = 'self',
			currentNonce;

		/**
		 * Set the current Nonce
		 * @param    string   nonce    a nonce
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.set_nonce = function( nonce ) {
			currentNonce = nonce;
			return this;
		}

		/**
		 * Set the current view
		 * @param    string   view   a view option
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.set_view = function( view ) {
			currentView = view;
			return this;
		}

		/**
		 * Update various links on the page for easy navigation when using views
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.update_links = function() {

			if ( 'self' === currentView || ! currentNonce ) {
				return;
			}

			var $links = $( '.llms-widget-syllabus .llms-lesson a, .llms-course-progress a, .llms-lesson-preview a.llms-lesson-link, .llms-parent-course-link a.llms-lesson-link' );

			$links.each( function() {

				var $link = $( this ),
					href = $link.attr( 'href' ),
					split = href.split( '?' ),
					qs = {};

				if ( split.length > 1 ) {

					$.each( split[1].split( '&' ), function( i, pair ) {
						pair = pair.split( '=' );
						qs[ pair[0] ] = pair[1];
					} );

				}

				qs['llms-view-as'] = currentView;
				qs.view_nonce = currentNonce;

				$link.attr( 'href', split[0] + '?' + $.param( qs ) );

			} );

		}

	};

	// initalize the object
	window.llms.ViewManager = new ViewManager();

} )( jQuery );
