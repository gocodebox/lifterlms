/**
 * Quiz question bank view
 * @since    3.16.0
 * @version  3.16.0
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

		/**
		 * Highlight drop areas when dragging starts
		 * @param    obj   model  model being sorted
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		sortable_start: function( model ) {
			var selector = 'group' === model.get( 'question_type' ).get( 'id' ) ? '.llms-editor-tab > .llms-quiz-questions' : '.llms-quiz-questions';
			$( selector ).addClass( 'dragging' );
		},

		/**
		 * Remove highlights when dragging stops
		 * @param    obj   model  model being sorted
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		sortable_stop: function() {
			$( '.llms-quiz-questions' ).removeClass( 'dragging' );
		},

		/**
		 * Overrides receive to ensure that question groups can't be moved into queston groups
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI Sortable ui object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_receive : function( event, ui ) {

			event.stopPropagation();

			// prevent moving a question group into a question group
			if ( ui.item.hasClass( 'qtype--group' ) && $( event.target ).closest( '.qtype--group' ).length ) {;
				ui.sender.sortable( 'cancel' );
				return;
			}

			var senderListEl = ui.sender;
			var senderCollectionListView = senderListEl.data( "view" );
			if( ! senderCollectionListView || ! senderCollectionListView.collection ) return;

			var newIndex = this._getContainerEl().children().index( ui.item );
			var modelReceived = senderCollectionListView.collection.get( ui.item.attr( "data-model-cid" ) );
			senderCollectionListView.collection.remove( modelReceived );
			this.collection.add( modelReceived, { at : newIndex } );
			modelReceived.collection = this.collection; // otherwise will not get properly set, since modelReceived.collection might already have a value.
			this.setSelectedModel( modelReceived );
		},

		/**
		 * Override to allow manipulatino of placeholder element
		 * @param    {[type]}   event  [description]
		 * @param    {[type]}   ui     [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_sortStart : function( event, ui ) {

			var modelBeingSorted = this.collection.get( ui.item.attr( "data-model-cid" ) );

			ui.placeholder.addClass( 'qtype--' + modelBeingSorted.get( 'question_type' ).get( 'id' ) );

			if( this._isBackboneCourierAvailable() )
				this.spawn( "sortStart", { modelBeingSorted : modelBeingSorted } );
			else this.trigger( "sortStart", modelBeingSorted );
		},

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't send stop events
		 * if moving from one sortable to another... :-(
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
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
