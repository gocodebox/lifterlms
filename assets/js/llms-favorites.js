;/* global LLMS, $ */
/* jshint strict: true */

/**
 * Front End Favorite Class
 *
 * @type     {Object}
 * @since    [version]
 * @version  [version]
 */
( function( $ ) {
    
	var favorite = {

		/**
		 * Bind DOM events
		 *
		 * @return void
		 * @since    [version]
		 * @version  [version]
		 */
		bind: function() {

			var self = this;

			// Favorite clicked
			$( '.llms-favorite-wrapper .llms-favorite-action' ).on( 'click', function( e ) {
				e.preventDefault();
				self.favorite( $( this ) );
			} );

		},

		/**
		 * Favorite / Unfavorite an object
		 *
		 * @param    obj   $btn   jQuery object for the "Favorite / Unfavorite" button
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		favorite: function( $btn ) {

			var self      	= this,
				object_id 	= $btn.attr( 'data-id' ),
				object_type = $btn.attr( 'data-type' )
				user_action	= $btn.attr( 'data-action' );

			console.log( 'before ajax', object_id, object_type, user_action );

			LLMS.Ajax.call( {
				data: {
					action: 'favorite_object',
					object_id: object_id,
					object_type: object_type,
					user_action: user_action
				},
				beforeSend: function() {

					console.log('before send');

				},
				success: function( r ) {

					console.log('result', r );

				}

			} );

		}

	};

	favorite.bind();

	window.llms             = window.llms || {};
	window.llms.favorites   = favorite;

} )( jQuery );
