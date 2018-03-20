/**
 * Single Assignment View
 * @since    [version]
 * @version  [version]
 */
define( [
		// 'Models/Assignment',
		'Views/Popover',
		'Views/PostSearch',
		'Views/_Detachable',
		'Views/_Editable',
		// 'Views/_Subview',
		'Views/_Trashable'
	], function(
		// AssignmentModel,
		Popover,
		PostSearch,
		Detachable,
		Editable,
		// Subview,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		el: '#llms-editor-assignment',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {
			'click #llms-existing-assignment': 'add_existing_assignment_click',
			'click #llms-new-assignment': 'add_new_assignment',
		}, Detachable.events, Editable.events, Trashable.events ),

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
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function( data ) {

			this.lesson = data.lesson;

			// initialize the model if the quiz is enabled or it's disabled but we still have data for a quiz
			// if ( 'yes' === this.lesson.get( 'quiz_enabled' ) || ! _.isEmpty( this.lesson.get( 'quiz' ) ) ) {
			// 	this.model = this.lesson.get( 'quiz' );

				/**
				 * @todo  this is a terrilbe terrible patch
				 *        I've spent nearly 3 days trying to figure out how to not use this line of code
				 *        ISSUE REPRODUCTION:
				 *        Open course builder
				 *        Open a lesson (A) and add a quiz
				 *        Switch to a new lesson (B)
				 *        Add a new quiz
				 *        Return to lesson A and the quizzes parent will be set to LESSON B
				 *        This will happen for *every* quiz in the builder...
				 *        Adding this set_parent on init guarantees that the quizzes correct parent is set
				 *        after adding new quizzes to other lessons
				 *        it's awful and it's gross...
				 *        I'm confused and tired and going to miss release dates again because of it
				 */
				// this.model.set_parent( this.lesson );

				// this.on( 'model-trashed', this.on_trashed );

			// }

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			return this;

		},

		/**
		 * Adds a new assignment to a lesson which currently has no assignment associated wlith it
		 * @return   void
		 * @since    [version]
		 * @version  [version]
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

		// come back to this and make sure cloning resets all the IDs
		add_existing_assignment: function( event ) {

			// this.post_search_popover.hide();

			// var quiz = event.data;

			// if ( 'clone' === event.action ) {

			// 	delete quiz.id;

			// 	_.each( quiz.questions, function( question ) {

			// 		delete question.parent_id;
			// 		delete question.id;

			// 		if ( question.choices ) {

			// 			_.each( question.choices, function( choice ) {

			// 				delete choice.question_id;
			// 				delete choice.id;

			// 			} );

			// 		}

			// 	} );

			// } else {

			// 	quiz._forceSync = true;

			// }

			// delete quiz.lesson_id;

			// this.lesson.add_quiz( quiz );
			// this.model = this.lesson.get( 'quiz' );
			// this.render();

		},

		/**
		 * Open add existing assignment popover
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
		 */
		is_addon_available: function() {

			return ( window.llms_builder.assignments );

		},

		/**
		 * Called when assignment is trashed
		 * @param    obj   assignment  Assignment model
		 * @return   void
		 * @since    [version]
		 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
		 */
		show_ad_popover: function( el ) {

			var h3 = LLMS.l10n.translate( 'Get Your Students Taking Action' ),
				p = 'Great learning content is only half of teaching online. When your learners fully engage, they will take your content and move into action. Remove barriers for your learners by telling them what to do to apply what they just learned. Create graded assignments or simply give them a checklist of action items to complete before moving on.',
				btn = LLMS.l10n.translate( 'Get Assignments Now!' );

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
					content: '<h3>' + h3 + '</h3><p>' + p + '</p><br><p><a class="llms-button-primary" href="#">' + btn + '</a></p>'
				}
			} );

			this.ad_popover.show();

		},

	}, Detachable, Editable, Trashable ) );

} );
