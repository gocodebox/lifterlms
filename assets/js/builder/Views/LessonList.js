/**
 * Single Section View
 *
 * @since    3.13.0
 * @version  3.16.0
 */
define( [ 'Views/Lesson', 'Views/_Receivable' ], function( LessonView, Receivable ) {

	return Backbone.CollectionView.extend( _.defaults( {

		className: 'llms-lessons',

		/**
		 * Section model
		 *
		 * @type  {[type]}
		 */
		modelView: LessonView,

		/**
		 * Are sections selectable?
		 *
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 *
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			connectWith: '.llms-lessons',
			cursor: 'move',
			handle: '.drag-lesson',
			items: '.llms-lesson',
			placeholder: 'llms-lesson llms-sortable-placeholder',
		},

		sortable_start: function( collection ) {
			$( '.llms-lessons' ).addClass( 'dragging' );
		},

		sortable_stop: function( collection ) {
			$( '.llms-lessons' ).removeClass( 'dragging' );
		},

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't send stop events
		 * if moving from one sortable to another... :-(
		 *
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_sortStop : function( event, ui ) {

			var modelBeingSorted     = this.collection.get( ui.item.attr( 'data-model-cid' ) ),
				modelViewContainerEl = this._getContainerEl(),
				newIndex             = modelViewContainerEl.children().index( ui.item );

			if ( newIndex == -1 && modelBeingSorted ) {
				this.collection.remove( modelBeingSorted );
			}

			this._reorderCollectionBasedOnHTML();
			this.updateDependentControls();

			if ( this._isBackboneCourierAvailable() ) {
				this.spawn( 'sortStop', { modelBeingSorted : modelBeingSorted, newIndex : newIndex } );
			} else {
				this.trigger( 'sortStop', modelBeingSorted, newIndex );
			}

		},

	}, Receivable ) );

} );
