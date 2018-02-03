/**
 * Single Section View
 * @since    3.13.0
 * @version  3.16.0
 */
define( [ 'Views/Section', 'Views/_Receivable' ], function( SectionView, Receivable ) {

	return Backbone.CollectionView.extend( _.defaults( {

		/**
		 * Parent element
		 * @type  {String}
		 */
		el: '#llms-sections',

		events : {
			'mousedown > li.llms-section > .llms-builder-header .llms-headline' : '_listItem_onMousedown',
			// 'dblclick > li, tbody > tr > td' : '_listItem_onDoubleClick',
			'click' : '_listBackground_onClick',
			'click ul.collection-view' : '_listBackground_onClick',
			'keydown' : '_onKeydown'
		},

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: SectionView,

		/**
		 * Enable keyboard events
		 * @type  {Bool}
		 */
		processKeyEvents: false,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: true,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			cursor: 'move',
			handle: '.drag-section',
			items: '.llms-section',
			placeholder: 'llms-section llms-sortable-placeholder',
		},

		sortable_start: function( collection ) {
			this.$el.addClass( 'dragging' );
		},

		sortable_stop: function( collection ) {
			this.$el.removeClass( 'dragging' );
		},

	}, Receivable ) );

} );
