/**
 * Quiz question bank view
 * @since    [version]
 * @version  [version]
 */
define( [ 'Views/Question' ], function( QuestionView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-quiz-questions',

		/**
		 * Parent element
		 * @type  {String}
		 */
		// el: '#llms-quiz-questions',

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: QuestionView,

		/**
		 * Enable keyboard events
		 * @type  {Bool}
		 */
		processKeyEvents: false,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			connectWith: '.llms-quiz-questions',
			cursor: 'move',
			handle: '.llms-data-stamp',
			items: '.llms-question',
			placeholder: 'llms-question llms-sortable-placeholder',
		},

		sortable_start: function( collection ) {
			$( '.llms-quiz-questions' ).addClass( 'dragging' );
		},

		sortable_stop: function( collection ) {
			$( '.llms-quiz-questions' ).removeClass( 'dragging' );
		},

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't send stop events
		 * if moving from one sortable to another... :-(
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		_sortStop : function( event, ui ) {

			event.stopPropagation();

			var modelBeingSorted = this.collection.get( ui.item.attr( 'data-model-cid' ) ),
				modelViewContainerEl = this._getContainerEl(),
				newIndex = modelViewContainerEl.children().index( ui.item );

			if ( newIndex == -1 && modelBeingSorted ) {
				this.collection.remove( modelBeingSorted );
			}

			this._reorderCollectionBasedOnHTML();
			this.updateDependentControls();

			if( this._isBackboneCourierAvailable() ) {
				this.spawn( 'sortStop', { modelBeingSorted : modelBeingSorted, newIndex : newIndex } );
			} else {
				this.trigger( 'sortStop', modelBeingSorted, newIndex );
			}

		},

	} );

} );
