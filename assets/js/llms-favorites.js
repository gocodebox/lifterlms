/* global LLMS, $ */
/* jshint strict: true */

/**
 * Front End Favorite Class.
 *
 * @type {Object}
 *
 * @since 7.5.0
 * @version 7.5.0
 */
( function( $ ) {

	var favorite = {

		/**
		 * Bind DOM events.
		 *
		 * @since 7.5.0
		 *
		 * @return {Void}
		 */
		bind: function() {

			var self = this;

			// Favorite clicked.
			$( '.llms-favorite-wrapper' ).on( 'click', function( e ) {
				e.preventDefault();
				var $btn = $( this ).find( '.llms-heart-btn' );
				$btn && self.favorite( $btn );
			} );

			// Adding class in Favorite's parent.
			$( '.llms-favorite-wrapper' ).parent().addClass( 'llms-has-favorite' );

		},

		/**
		 * Favorite / Unfavorite an object.
		 *
		 * @since 7.5.0
		 *
		 * @param {Object} $btn jQuery object for the "Favorite / Unfavorite" button.
		 * @return {Void}
		 */
		favorite: function( $btn ) {

			var object_id 	= $btn.attr( 'data-id' ),
				object_type = $btn.attr( 'data-type' ),
				user_action	= $btn.attr( 'data-action' );

			LLMS.Ajax.call( {
				data: {
					action: 'favorite_object',
					object_id: object_id,
					object_type: object_type,
					user_action: user_action
				},
				beforeSend: function() {},
				success: function( r ) {
					/**
					 * Get all the favorite buttons on the page related to the same lesson, e.g. when the syllabus
					 * is shown on the sidebar of a lesson or a course, in that case you will have the same favorite
					 * button twice. The code below makes sure both the buttons are updated.
					 */
					var $fav_btns = $( '[data-id='+object_id+'][data-type='+object_type+'][data-action='+user_action+']' );
					if( r.success ) {
						$fav_btns.each(
							function() {
								if( 'favorite' === user_action ) {
									$(this).removeClass( 'fa-heart-o' ).addClass( 'fa-heart' );
									$(this).attr( 'data-action', 'unfavorite' );
								} else if ( 'unfavorite' === user_action ) {
									$(this).removeClass( 'fa-heart' ).addClass( 'fa-heart-o' );
									$(this).attr( 'data-action', 'favorite' );
								}
								// Updating count.
								$(this).closest( '.llms-favorite-wrapper' ).find( '.llms-favorites-count' ).text( r.total_favorites );
							}
						);
					}
				}
			} );
		}
	};

	favorite.bind();

	window.llms             = window.llms || {};
	window.llms.favorites   = favorite;

} )( jQuery );
