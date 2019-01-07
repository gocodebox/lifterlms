/**
 * Single Assignment View
 * @since    3.17.0
 * @version  3.17.7
 */
define( [
		'Views/Popover',
		'Views/PostSearch',
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Trashable',
		'Views/_Subview',
		'Views/SettingsFields'
	], function(
		Popover,
		PostSearch,
		Detachable,
		Editable,
		Trashable,
		Subview,
		SettingsFields
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default',

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			settings: {
				class: SettingsFields,
				instance: null,
				state: 'default',
			},
		},

		el: '#llms-editor-assignment',

		/**
		 * DOM Events
		 * @return   obj
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		events: function() {
			var addon_events = this.is_addon_available() ? window.llms_builder.assignments.get_view_events() : {};
			return _.defaults( {
				'click #llms-existing-assignment': 'add_existing_assignment_click',
				'click #llms-new-assignment': 'add_new_assignment',
			}, Detachable.events, Editable.events, Trashable.events, addon_events );
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-assignment-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		initialize: function( data ) {

			this.lesson = data.lesson;

			// initialize the model if the assignment is enabled or it's disabled but we still have data for a assignment
			if ( 'yes' === this.lesson.get( 'assignment_enabled' ) || ! _.isEmpty( this.lesson.get( 'assignment' ) ) ) {

				this.model = this.lesson.get( 'assignment' );

				/**
				 * @todo  this is a terrilbe terrible patch
				 *        I've spent nearly 3 days trying to figure out how to not use this line of code
				 *        ISSUE REPRODUCTION:
				 *        Open course builder
				 *        Open a lesson (A) and add a assignment
				 *        Switch to a new lesson (B)
				 *        Add a new assignment
				 *        Return to lesson A and the assignment's parent will be set to LESSON B
				 *        This will happen for *every* assignment in the builder...
				 *        Adding this set_parent on init guarantees that the assignment's correct parent is set
				 *        after adding new assignment's to other lessons
				 *        it's awful and it's gross...
				 *        I'm confused and tired and going to miss release dates again because of it
				 */
				this.model.set_parent( this.lesson );

			}

			this.on( 'model-trashed', this.on_trashed );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.17.0
		 * @version  3.17.7
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			if ( this.model && this.is_addon_available() ) {

				this.stopListening( this.model, 'change:assignment_type', this.render );

				this.render_subview( 'settings', {
					el: '#llms-assignment-settings-fields',
					model: this.model,
				} );

				// this.init_datepickers();
				this.init_selects();

				window.llms_builder.assignments.render_editor( this );

				this.listenTo( this.model, 'change:assignment_type', this.render );

			}

			return this;

		},

		/**
		 * Adds a new assignment to a lesson which currently has no assignment associated wlith it
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		add_new_assignment: function() {

			if ( this.is_addon_available() ) {

				this.model = window.llms_builder.assignments.get_assignment( {
					/* translators: %1$s = associated lesson title */
					title: LLMS.l10n.replace( '%1$s Assignment', {
						'%1$s': this.lesson.get( 'title' ),
					} ),
					lesson_id: this.lesson.get( 'id' ),
				} );

				this.lesson.set( 'assignment_enabled', 'yes' );
				this.lesson.set( 'assignment', this.model );

				this.render();

			} else {

				this.show_ad_popover( '#llms-new-assignment' );

			}


		},

		/**
		 * When an assignment is selected from the post select popover
		 * instantiate it and add it to the current lesson
		 * @param    object   event  data from the select2 select event
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		add_existing_assignment: function( event ) {

			this.post_search_popover.hide();

			var assignment = event.data;

			if ( 'clone' === event.action ) {

				delete assignment.id;

			} else {

				assignment._forceSync = true;

			}

			assignment.lesson_id = this.lesson.get( 'id' )

			assignment = window.llms_builder.construct.get_model( 'Assignment', assignment );

			this.lesson.set( 'assignment_enabled', 'yes' );
			this.lesson.set( 'assignment', assignment );
			this.model = assignment;

			this.render();

		},

		/**
		 * Open add existing assignment popover
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		add_existing_assignment_click: function( event ) {

			event.preventDefault();

			if ( this.is_addon_available() ) {

				this.post_search_popover = new Popover( {
					el: '#llms-existing-assignment',
					args: {
						backdrop: true,
						closeable: true,
						container: '.wrap.lifterlms.llms-builder',
						dismissible: true,
						placement: 'left',
						width: 480,
						title: LLMS.l10n.translate( 'Add Existing Assignment' ),
						content: new PostSearch( {
							post_type: 'llms_assignment',
							searching_message: LLMS.l10n.translate( 'Search for existing assignments...' ),
						} ).render().$el,
						onHide: function() {
							Backbone.pubSub.off( 'assignment-search-select' );
						},
					}
				} );

				this.post_search_popover.show();
				Backbone.pubSub.once( 'assignment-search-select', this.add_existing_assignment, this );

			} else {

				this.show_ad_popover( '#llms-existing-assignment' );

			}


		},

		/**
		 * Determine if Assignments addon is available to use
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_addon_available: function() {

			return ( window.llms_builder.assignments );

		},

		/**
		 * Called when assignment is trashed
		 * @param    obj   assignment  Assignment model
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		on_trashed: function( assignment ) {

			this.lesson.set( 'assignment_enabled', 'no' );
			this.lesson.set( 'assignment', '' );

			delete this.model;

			this.render();

		},

		/**
		 * Shows a dirty dirty ad popoever for advanced assignments
		 * @param    string   el  jQuery selector string
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		show_ad_popover: function( el ) {

			var h3 = LLMS.l10n.translate( 'Get Your Students Taking Action' ),
				p = 'Great learning content is only half of teaching online. When your learners fully engage, they will take your content and move into action. Remove barriers for your learners by telling them what to do to apply what they just learned. Create graded assignments or simply give them a checklist of action items to complete before moving on.',
				btn = LLMS.l10n.translate( 'Get Assignments Now!' ),
				url = 'https://lifterlms.com/product/lifterlms-assignments?utm_source=LifterLMS%20Plugin&utm_medium=Assignment%20Builder%20Button&utm_campaign=Assignment%20Addon%20Upsell&utm_content=3.17.0';

			this.ad_popover = new Popover( {
				el: el,
				args: {
					backdrop: true,
					closeable: true,
					container: '.wrap.lifterlms.llms-builder',
					dismissible: true,
					// placement: 'left',
					width: 380,
					title: LLMS.l10n.translate( 'Unlock LifterLMS Assignments' ),
					content: '<h3>' + h3 + '</h3><p>' + p + '</p><br><p><a class="llms-button-primary" href="' + url + '" target="_blank">' + btn + '</a></p>'
				}
			} );

			this.ad_popover.show();

		},

	}, Detachable, Editable, Trashable, Subview, SettingsFields ) );

} );
