/**
 * Trashable model
 * @type     {Object}
 * @since    3.16.12
 * @version  3.16.12
 */
define( [], function() {

	return {

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		events: {
			'click a[href="#llms-trash-model"]': 'trash_model',
		},

		/**
		 * Remove a model from it's parent and delete it
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		trash_model: function( event ) {

			if ( event ) {
				event.preventDefault();
				event.stopPropagation();
			}

			var msg = LLMS.l10n.replace( 'Are you sure you want to move this %s to the trash?', {
				'%s': this.model.get_l10n_type(),
			} );

			if ( window.confirm( msg ) ) {

				if ( this.model.collection ) {
					this.model.collection.remove( this.model );
				}

				// publish event
				Backbone.pubSub.trigger( 'model-trashed', this.model );

				// trigger local event so extending views can run other actions where necessary
				this.trigger( 'model-trashed', this.model );

			}

		},

	}

} );
