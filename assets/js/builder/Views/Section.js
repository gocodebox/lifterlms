/**
 * Single Section View
 * @since    3.13.0
 * @version  3.16.12
 */
define( [
		'Views/LessonList',
		'Views/_Editable',
		'Views/_Shiftable',
		'Views/_Trashable'
	], function(
		LessonListView,
		Editable,
		Shiftable,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * Element class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-section',

		/**
		 * Events
		 * @type     {Object}
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		events: _.defaults( {

			'click': 'select',
			'click .expand': 'expand',
			'click .collapse': 'collapse',
			'click .shift-up--section': 'shift_up',
			'click .shift-down--section': 'shift_down',

			'mouseenter .llms-lessons': 'on_mouseenter',

		}, Editable.events, Trashable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		id: function() {
			return 'llms-section-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-section-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.render();
			this.listenTo( this.model, 'change', this.render );
			this.listenTo( this.model, 'change:_expanded', this.toggle_expanded );
			this.lessonListView.collection.on( 'add', this.on_lesson_add, this );

			this.dragTimeout = null;

			Backbone.pubSub.on( 'expand-all', this.expand, this );
			Backbone.pubSub.on( 'collapse-all', this.collapse, this );

		},

		/**
		 * Render the section
		 * Initializes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template( this.model.toJSON() ) );

			this.maybe_hide_shiftable_buttons();

			this.lessonListView = new LessonListView( {
				el: this.$el.find( '.llms-lessons' ),
				collection: this.model.get( 'lessons' ),
			} );
			this.lessonListView.render();
			this.lessonListView.on( 'sortStart', this.lessonListView.sortable_start );
			this.lessonListView.on( 'sortStop', this.lessonListView.sortable_stop );

			// selection changes
			this.lessonListView.on( 'selectionChanged', this.active_lesson_change, this );

			this.maybe_hide_trash_button();

			return this;

		},

		active_lesson_change: function( current, previous ) {

			Backbone.pubSub.trigger( 'active-lesson-change', {
				current: current,
				previous: previous,
			} );

		},

		/**
		 * Collapse lessons within the section
		 * @param    obj   event    js event object
		 * @param    bool  update   if true, updates the model to reflect the new state
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		collapse: function( event, update ) {

			if ( 'undefined' === typeof update ) {
				update = true;
			}

			if ( event ) {
				event.stopPropagation();
				event.preventDefault();
			}

			this.$el.removeClass( 'expanded' ).find( '.drag-expanded' ).removeClass( 'drag-expanded' );
			if ( update ) {
				this.model.set( '_expanded', false );
			}
			Backbone.pubSub.trigger( 'section-toggle', this.model );

		},

		/**
		 * Expand lessons within the section
		 * @param    obj   event    js event object
		 * @param    bool  update   if true, updates the model to reflect the new state
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		expand: function( event, update ) {

			if ( 'undefined' === typeof update ) {
				update = true;
			}

			if ( event ) {
				event.stopPropagation();
				event.preventDefault();
			}

			this.$el.addClass( 'expanded' );
			if ( update ) {
				this.model.set( '_expanded', true );
			}
			Backbone.pubSub.trigger( 'section-toggle', this.model );

		},

		maybe_hide_trash_button: function() {

			var $btn = this.$el.find( '.trash--section' );

			if ( this.model.get( 'lessons' ).isEmpty() ) {

				$btn.show();

			} else {

				$btn.hide()

			}

		},

		/**
		 * When a lesson is added to the section trigger a collection reorder & update the lesson's id
		 * @param    obj   model  Lesson model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_lesson_add: function( model ) {

			this.lessonListView.collection.trigger( 'reorder' );
			model.set( 'parent_section', this.model.get( 'id' ) );
			this.expand();

		},

		on_mouseenter: function( event ) {


			if ( $( event.target ).hasClass( 'dragging' ) ) {

				$( '.drag-expanded' ).removeClass( 'drag-expanded' );
				$( event.target ).addClass( 'drag-expanded' );

			}

		},

		/**
		 * Expand
		 * @param    {[type]}   model  [description]
		 * @param    {[type]}   value  [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		toggle_expanded: function( model, value ) {

			if ( value ) {
				this.expand( null, false );
			} else {
				this.collapse( null, false );
			}

		},

	}, Editable, Shiftable, Trashable ) );

} );
