/**
 * Sidebar Elements View
 *
 * @since    3.16.0
 * @version  3.16.12
 */
define( [ 'Models/Section', 'Views/Section', 'Models/Lesson', 'Views/Lesson', 'Views/Popover', 'Views/PostSearch' ], function( Section, SectionView, Lesson, LessonView, Popover, LessonSearch ) {

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
			this.listenTo( this.SidebarView.CourseView.model.get( 'sections' ), 'remove', this.maybe_disable_buttons );

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
			this.maybe_disable_buttons();

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
		 * @version  3.16.12
		 */
		add_existing_lesson: function( event ) {

			event.preventDefault();

			var pop = new Popover( {
				el: '#llms-existing-lesson',
				args: {
					backdrop: true,
					closeable: true,
					container: '.wrap.lifterlms.llms-builder',
					dismissible: true,
					placement: 'left',
					width: 480,
					title: LLMS.l10n.translate( 'Add Existing Lesson' ),
					content: new LessonSearch( {
						post_type: 'lesson',
						searching_message: LLMS.l10n.translate( 'Search for existing lessons...' ),
					} ).render().$el,
				}
			} );

			pop.show();
			Backbone.pubSub.on( 'lesson-search-select', function() {
				pop.hide()
			} );

		},

		/**
		 * Disables lesson add buttons if no sections are available to add a lesson to
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		maybe_disable_buttons: function() {

			var $els = $( '#llms-new-lesson, #llms-existing-lesson' );

			if ( ! this.SidebarView.CourseView.model.get( 'sections' ).length ) {
				$els.attr( 'disabled', 'disabled' );
			} else {
				$els.removeAttr( 'disabled' );
			}

		},

	} );

} );
