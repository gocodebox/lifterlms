/**
 * Single Course View
 * @since    3.13.0
 * @version  3.16.0
 */
define( [ 'Views/SectionList', 'Views/_Editable' ], function( SectionListView, Editable ) {

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
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-builder-main',

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-course-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			var self = this;

			// this.listenTo( this.model, 'sync', this.render );
			this.render();

			this.sectionListView = new SectionListView( {
				collection: this.model.get( 'sections' ),
			} );
			this.sectionListView.render();
			// drag and drop start
			this.sectionListView.on( 'sortStart', this.sectionListView.sortable_start );
			// drag and drop stop
			this.sectionListView.on( 'sortStop', this.sectionListView.sortable_stop );
			// selection changes
			this.sectionListView.on( 'selectionChanged', this.active_section_change );
			// "select" a section when it's added to the course
			this.listenTo( this.model.get( 'sections'), 'add', this.on_section_add );

			Backbone.pubSub.on( 'section-toggle', this.on_section_toggle, this );

			Backbone.pubSub.on( 'expand-section', this.expand_section, this );

			Backbone.pubSub.on( 'lesson-selected', this.active_lesson_change, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			this.$el.html( this.template( this.model ) );
			return this;
		},

		active_lesson_change: function( model ) {

			// set parent section to be active
			var section = this.model.get( 'sections' ).get( model.get( 'parent_section' ) );
			this.sectionListView.setSelectedModel( section );

		},

		/**
		 * When a section "selection" changes in the list
		 * Update each section model so we can figure out which one is selected from other views
		 * @param    array   current   array of selected models
		 * @param    array   previous  array of previously selected models
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		active_section_change: function( current, previous ) {

			_.each( current, function( model ) {
				model.set( '_selected', true );
			} );

			_.each( previous, function( model ) {
				model.set( '_selected', false );
			} );

		},

		/**
		 * "Selects" the new section when it's added to the course
		 * @param    obj   model  Section model that's just been added
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_section_add: function( model ) {

			this.sectionListView.setSelectedModel( model );

		},

		/**
		 * When expanding/collapsing sections
		 * if collapsing, unselect, if expanding, select
		 * @param    obj   model  toggled section
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_section_toggle: function( model ) {

			var selected = model.get( '_expanded' ) ? [ model ] : [];
			this.sectionListView.setSelectedModels( selected );

		}

	}, Editable ) );

} );
