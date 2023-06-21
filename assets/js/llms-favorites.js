;/* global LLMS, $ */
/* jshint strict: true */

/**
 * Front End Favorite Class.
 *
 * @type     {Object}
 * @since    [version]
 * @version  [version]
 */
( function( $ ) {
    
	var favorite = {

		/**
		 * Main Favorite Container Element.
		 *
		 * @type  obj
		 */
		$container: null,

		/**
		 * Bind DOM events.
		 *
		 * @return void
		 * @since    [version]
		 * @version  [version]
		 */
		bind: function() {

			var self = this;

			// Favorite clicked
			$( '.llms-favorite-wrapper' ).on( 'click', function( e ) {
				e.preventDefault();
				let $btn = $( this ).find( '.llms-heart-btn' );
				self.favorite( $btn );
			} );

			// Adding class in Favorite's parent
			$( '.llms-favorite-wrapper' ).parent().addClass( 'llms-has-favorite' );

		},

		/**
		 * Favorite / Unfavorite an object.
		 *
		 * @param    obj   $btn   jQuery object for the "Favorite / Unfavorite" button.
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		favorite: function( $btn ) {

			this.$container = $btn.closest( '.llms-favorite-wrapper' );

			var self 		= this,
				object_id 	= $btn.attr( 'data-id' ),
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
					
					if( r.success ) {

						if( 'favorite' === user_action ) {

							$btn.removeClass( 'fa-heart-o' ).addClass( 'fa-heart' );
							$( $btn ).attr( 'data-action', 'unfavorite' );

						} else if ( 'unfavorite' === user_action ) {

							$btn.removeClass( 'fa-heart' ).addClass( 'fa-heart-o' );
							$( $btn ).attr( 'data-action', 'favorite' );

						}

						// Updating count.
						self.$container.find( '.llms-favorites-count' ).text( r.total_favorites );
						

					}

				}

			} );

		}

	};

	favorite.bind();

	window.llms             = window.llms || {};
	window.llms.favorites   = favorite;

} )( jQuery );
