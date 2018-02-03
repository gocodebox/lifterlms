/**
 * _receive override for Backbone.CollectionView core
 * enables connection with jQuery UI draggable buttons
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return {

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't properly handle
		 * receieves from a jQuery UI draggable object
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_receive : function( event, ui ) {

			// came from sidebar drag
			if ( ui.sender.hasClass( 'ui-draggable' ) ) {
				var index = this._getContainerEl().children().index( ui.helper );
				ui.helper.remove(); // remove the helper
				this.collection.add( {}, { at: index } );
				return;
			}

			var senderListEl = ui.sender;
			var senderCollectionListView = senderListEl.data( 'view' );
			if( ! senderCollectionListView || ! senderCollectionListView.collection ) return;

			var newIndex = this._getContainerEl().children().index( ui.item );
			var modelReceived = senderCollectionListView.collection.get( ui.item.attr( 'data-model-cid' ) );
			senderCollectionListView.collection.remove( modelReceived );
			this.collection.add( modelReceived, { at : newIndex } );
			modelReceived.collection = this.collection; // otherwise will not get properly set, since modelReceived.collection might already have a value.
			this.setSelectedModel( modelReceived );
		},

	}

} );

