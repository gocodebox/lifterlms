/**
 * Sidebar Elements View
 *
 * @since    3.16.0
 * @version  [version]
 */
define( [ 'Models/Section', 'Views/Section', 'Models/Lesson', 'Views/Lesson', 'Views/ExistingLessonPopover' ], function( Section, SectionView, Lesson, LessonView, show_existing_lesson_popover ) {

	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 *
		 * @type  {String}
		 */
		el: '#llms-elements',

		events: {
			'click #llms-new-section': 'add_new_section',
			'click #llms-new-lesson': 'add_new_lesson',
			'click #llms-existing-lesson': 'add_existing_lesson',
		},

		/**
		 * Wrapper Tag name
		 *
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 *
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-elements-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data ) {

			// save a reference to the main Course view
			this.SidebarView = data.SidebarView;

			// watch course sections and enable/disable lesson buttons conditionally
			this.listenTo( this.SidebarView.CourseView.model.get( 'sections' ), 'add', this.maybe_disable_buttons );
			this.listenTo( this.SidebarView.CourseView.model.get( 'sections' ), 'remove', this.on_section_remove );

		},

		/**
		 * Compiles the template and renders the view
		 *
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template() );
			this.draggable();
			this.maybe_add_initial_section();

			return this;
		},

		draggable: function() {

			$( '#llms-new-section' ).draggable( {
				appendTo: '#llms-sections',
				cancel: false,
				connectToSortable: '.llms-sections',
				helper: function() {
					return new SectionView( { model: new Section() } ).render().$el;
				},
				start: function() {
					$( '.llms-sections' ).addClass( 'dragging' );
				},
				stop: function() {
					$( '.llms-sections' ).removeClass( 'dragging' );
				},
			} );

			$( '#llms-new-lesson' ).draggable( {
				// appendTo: '#llms-sections .llms-section:first-child .llms-lessons',
				appendTo: '#llms-sections',
				cancel: false,
				connectToSortable: '.llms-lessons',
				helper: function() {
					return new LessonView( { model: new Lesson() } ).render().$el;
				},
				start: function() {

					$( '.llms-lessons' ).addClass( 'dragging' );

				},
				stop: function() {
					$( '.llms-lessons' ).removeClass( 'dragging' );
					$( '.drag-expanded' ).removeClass( '.drag-expanded' );
				},
			} );

		},

		add_new_section: function( event ) {

			event.preventDefault();
			Backbone.pubSub.trigger( 'add-new-section' );
		},

		add_new_lesson: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'add-new-lesson' );
		},

		/**
		 * Show the popover to add an existing lessons
		 *
		 * @param    object   event  JS Event Object
		 * @return   void
		 * @since    3.16.12
		 * @version  [version]
		 */
		add_existing_lesson: function( event ) {

			event.preventDefault();
			show_existing_lesson_popover( '#llms-existing-lesson', 'left' );

		},

		/**
		 * Add a demo section and three lessons when a course outline is empty.
		 *
		 * Later empty outlines get a single section and no lessons. The elements
		 * view is rebuilt when the sidebar re-renders, so the demo seed runs once.
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  [version]
		 */
		maybe_add_initial_section: function() {

			var course = this.SidebarView.CourseView.model;

			if ( course._outline_seeded ) {
				this.maybe_add_blank_section();
				this.maybe_disable_buttons();
				return;
			}

			course._outline_seeded = true;

			if ( ! course.get( 'sections' ).length ) {
				if ( false !== window.llms_builder.seed_starter ) {
					Backbone.pubSub.trigger( 'add-new-section' );
					Backbone.pubSub.trigger( 'add-new-lesson' );
					Backbone.pubSub.trigger( 'add-new-lesson' );
					Backbone.pubSub.trigger( 'add-new-lesson' );
				} else {
					this.maybe_add_blank_section();
				}
			}

			this.maybe_disable_buttons();

		},

		/**
		 * Keep one section on screen when the outline would otherwise be empty.
		 *
		 * @since [version]
		 *
		 * @return {void}
		 */
		maybe_add_blank_section: function() {

			var course = this.SidebarView.CourseView.model;

			if ( course.get( 'sections' ).length || course._adding_blank_section ) {
				return;
			}

			course._adding_blank_section = true;
			Backbone.pubSub.trigger( 'add-new-section' );
			course._adding_blank_section = false;

			// A blank replacement must not bring the three demo lessons back on reload.
			this.dismiss_starter_outline();

		},

		/**
		 * Disable lesson buttons when the course has no section to add a lesson to.
		 *
		 * @since [version]
		 *
		 * @return {void}
		 */
		maybe_disable_buttons: function() {

			var $els = $( '#llms-new-lesson, #llms-existing-lesson' );

			if ( ! this.SidebarView.CourseView.model.get( 'sections' ).length ) {
				$els.attr( 'disabled', 'disabled' );
			} else {
				$els.removeAttr( 'disabled' );
			}

		},

		/**
		 * After a section is removed, keep a section on screen without demo lessons.
		 *
		 * @since [version]
		 *
		 * @return {void}
		 */
		on_section_remove: function() {

			this.maybe_disable_buttons();
			this.maybe_add_blank_section();

		},

		/**
		 * Persist that the demo lessons should not be inserted again.
		 *
		 * The demo outline is unsaved until the course is saved, so deleting it does
		 * not produce a trash payload. Without this flag the next builder load sees
		 * an empty course and inserts the three lessons again.
		 *
		 * @since [version]
		 *
		 * @return {void}
		 */
		dismiss_starter_outline: function() {

			var course = this.SidebarView.CourseView.model;

			if ( course._starter_dismissed || ! course.get( 'id' ) ) {
				return;
			}

			course._starter_dismissed = true;
			window.llms_builder.seed_starter = false;

			if ( ! window.LLMS || ! LLMS.Ajax ) {
				return;
			}

			LLMS.Ajax.call( {
				data: {
					action: 'llms_builder',
					action_type: 'dismiss_starter',
					course_id: course.get( 'id' ),
				},
			} );

		},

	} );

} );
