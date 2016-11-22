;( function( $, undefined ) {

	window.llms = window.llms || {};

	var GradeBook = function() {

		this.init = function() {

			$( '#llms-gb-student-tabs .llms-nav-link' ).on( 'click', function( e ) {

				e.preventDefault();

				var $parent = $( this ).closest( '.llms-nav-items' ),
					id = $( this ).attr( 'href' );

				$parent.find( '.llms-active' ).removeClass( 'llms-active' );

				$( this ).closest( '.llms-nav-item' ).addClass( 'llms-active' );
				$( '.llms-gb-tab' ).removeClass( 'active' );

				$( id ).addClass( 'active' );

			} );

			$( '#llms-gb-student-tabs .llms-nav-link' ).first().trigger( 'click' );

		};

		this.init();

		return this;

	};

	window.llms.gradebook = new GradeBook();

} )( jQuery );
