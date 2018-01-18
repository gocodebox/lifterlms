/**
 * Single Question View
 * @since    [version]
 * @version  [version]
 */
define( [
		'Views/_Editable',
		'Views/QuestionChoiceList'
	], function(
		Editable,
		ChoiceListView
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Generate CSS classes for the question
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		className: function() {
			return 'llms-question qtype--' + this.model.get( 'question_type' ).get( 'id' );
		},

		events: _.defaults( {
			'click .clone--question': 'clone',
			'click .delete--question': 'delete',
			'change input[name="question_points"]': 'update_points',
		}, Editable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		id: function() {
			return 'llms-question-' + this.model.id;
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
		template: wp.template( 'llms-question-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			var change_events = [
				'change:order',
				'change:description_enabled',
				'change:video_enabled',
				'change:video_src',
			];
			_.each( change_events, function( event ) {
				this.listenTo( this.model, event, this.render );
			}, this );

			this.listenTo( this.model.get( 'image' ), 'change', this.render );

			this.listenTo( this.model.get_parent(), 'change:points', this.render_points_percentage );

			this.on( 'multi_choices_toggle', this.multi_choices_toggle, this );

			Backbone.pubSub.on( 'del-question-choice', this.del_choice, this );

			// called from QuestionChoice view when a choice is toggled as correct/incorrect
			Backbone.pubSub.on( 'question-choice-toggle-correct', this.toggle_correct, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			if ( this.model.get( 'question_type').get( 'choices' ) ) {

				this.choiceListView = new ChoiceListView( {
					el: this.$el.find( '.llms-question-choices' ),
					collection: this.model.get( 'choices' ),
				} );

				this.choiceListView.render();

			}

			if ( 'group' === this.model.get( 'question_type' ).get( 'id' ) ) {

				var self = this;
				setTimeout( function() {
					self.questionListView = self.collectionListView.quiz.get_question_list( {
						el: self.$el.find( '.llms-quiz-questions' ),
						collection: self.model.get( 'questions' ),
					} );
					self.questionListView.render();
					self.questionListView.on( 'sortStart', self.questionListView.sortable_start );
					self.questionListView.on( 'sortStop', self.questionListView.sortable_stop );
				}, 1 );

			}

			if ( this.model.get( 'description_enabled' ) ) {

				this.init_editor( 'question-desc--' + this.model.get( 'id' ) );

			}

			return this;
		},

		/**
		 * rerender points percentage when question points are updated
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		render_points_percentage: function() {

			this.$el.find( '.llms-question-points' ).attr( 'data-tip', this.model.get_points_percentage() );

		},


		clone: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			// create a duplicate
			var clone = _.clone( this.model.attributes );

			// remove id (we want the duplicate to have a temp id)
			delete clone.id;

			clone.parent_id = this.model.get( 'id' );

			// set the question type ID
			clone.question_type = this.model.get( 'question_type' ).get( 'id' );

			// clone the image attributes seperately
			clone.image = _.clone( this.model.get( 'image' ).attributes );

			// if it has choices clone all the choices
			if ( this.model.get( 'choices' ) ) {

				clone.choices = [];

				this.model.get( 'choices' ).each( function ( choice ) {

					var choice_clone = _.clone( choice.attributes );
					delete choice_clone.id;
					delete choice_clone.question_id;

					clone.choices.push( choice_clone );

				} );

			}

			this.model.collection.add( clone );

		},

		/**
		 * Delete the question from a quiz / question group
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		delete: function( event ) {

			event.preventDefault();

			if ( window.confirm( LLMS.l10n.translate( 'Are you sure you want to delete this question?' ) ) ) {

				this.model.collection.remove( this.model );
				Backbone.pubSub.trigger( 'model-trashed', this.model );

			}

		},

		/**
		 * When toggling multiple correct answers *off* remove all correct choices except the first correct choice in the list
		 * @param    string   val  value of the question's `multi_choice` attr [yes|no]
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		multi_choices_toggle: function( val ) {

			if ( 'yes' === val ) {
				return;
			}

			this.model.get( 'choices' ).ensure_correct();

		},

		/**
		 * Bubble information to the QuestionChoices collection for this question
		 * Ensures that at least one correct answer is selected
		 * @param    obj   choice  model of the updated choice
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		toggle_correct: function( choice ) {

			if ( choice.get( 'question_id') != this.model.get( 'id') ) {
				return;
			}

			this.model.get( 'choices' ).trigger( 'question-choices-update-correct', choice, this.model.get( 'multi_choices' ), this.model.get( 'points' ) );

		},

		/**
		 * Update the model's points when the value of the points input is updated
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update_points: function() {

			this.model.set( 'points', this.$el.find( 'input[name="question_points"]' ).val() * 1 );

		}

	}, Editable ) );

} );
