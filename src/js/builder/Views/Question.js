/**
 * Single Question View
 * @since    3.16.0
 * @version  3.27.0
 */
define( [
		'Views/_Detachable',
		'Views/_Editable',
		'Views/QuestionChoiceList'
	], function(
		Detachable,
		Editable,
		ChoiceListView
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Generate CSS classes for the question
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		className: function() {
			return 'llms-question qtype--' + this.model.get( 'question_type' ).get( 'id' );
		},

		events: _.defaults( {
			'click .clone--question': 'clone',
			'click .delete--question': 'delete',
			'click .expand--question': 'expand',
			'click .collapse--question': 'collapse',
			'change input[name="question_points"]': 'update_points',
		}, Detachable.events, Editable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
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
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			var change_events = [
				'change:_expanded',
				'change:menu_order',
			];
			_.each( change_events, function( event ) {
				this.listenTo( this.model, event, this.render );
			}, this );

			this.listenTo( this.model.get( 'image' ), 'change', this.render );

			this.listenTo( this.model.get_parent(), 'change:_points', this.render_points_percentage );

			this.on( 'multi_choices_toggle', this.multi_choices_toggle, this );

			Backbone.pubSub.on( 'del-question-choice', this.del_choice, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			if ( this.model.get( 'question_type').get( 'choices' ) ) {

				this.choiceListView = new ChoiceListView( {
					el: this.$el.find( '.llms-question-choices' ),
					collection: this.model.get( 'choices' ),
				} );
				this.choiceListView.render();
				this.choiceListView.on( 'sortStart', this.choiceListView.sortable_start );
				this.choiceListView.on( 'sortStop', this.choiceListView.sortable_stop );

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

			if ( this.model.get( 'clarifications_enabled' ) ) {
				this.init_editor( 'question-clarifications--' + this.model.get( 'id' ), {
					mediaButtons: false,
					tinymce: {
						toolbar1: 'bold,italic,strikethrough,bullist,numlist,alignleft,aligncenter,alignright',
						toolbar2: '',
						setup: _.bind( this.on_editor_ready, this ),
					}
				} );
			}

			this.init_formatting_els();
			this.init_selects();

			return this;
		},

		/**
		 * rerender points percentage when question points are updated
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render_points_percentage: function() {

			this.$el.find( '.llms-question-points' ).attr( 'data-tip', this.model.get_points_percentage() );

		},

		/**
		 * Click event to duplicate a question within a quiz
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		clone: function( event ) {

			event.stopPropagation();
			event.preventDefault();
			this.model.collection.add( this._get_question_clone( this.model ) );

		},

		/**
		 * Recursive clone function which will correctly clone children of a question
		 * @param    obj   question  question model
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_get_question_clone: function( question ) {

			// create a duplicate
			var clone = _.clone( question.attributes );

			// remove id (we want the duplicate to have a temp id)
			delete clone.id;

			clone.parent_id = question.get( 'id' );

			// set the question type ID
			clone.question_type = question.get( 'question_type' ).get( 'id' );

			// clone the image attributes separately
			clone.image = _.clone( question.get( 'image' ).attributes );

			// if it has choices clone all the choices
			if ( question.get( 'choices' ) ) {

				clone.choices = [];

				question.get( 'choices' ).each( function ( choice ) {

					var choice_clone = _.clone( choice.attributes );
					delete choice_clone.id;
					delete choice_clone.question_id;

					clone.choices.push( choice_clone );

				} );

			}

			if ( 'group' === question.get( 'question_type' ).get( 'id' ) ) {

				clone.questions = [];
				question.get( 'questions' ).each( function( child ) {
					clone.questions.push( this._get_question_clone( child ) );
				}, this );

			}

			return clone;

		},

		/**
		 * Collapse a question and hide it's settings
		 * @param obj event js event obj.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		collapse: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			this.model.set( '_expanded', false );

		},

		/**
		 * Delete the question from a quiz / question group
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		delete: function( event ) {

			event.preventDefault();

			if ( window.confirm( LLMS.l10n.translate( 'Are you sure you want to delete this question?' ) ) ) {

				this.model.collection.remove( this.model );
				Backbone.pubSub.trigger( 'model-trashed', this.model );

			}

		},

		/**
		 * Click event to reveal a question's settings & choices
		 * @param obj event js event obj.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		expand: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			this.model.set( '_expanded', true );

		},

		/**
		 * When toggling multiple correct answers *off* remove all correct choices except the first correct choice in the list
		 * @param    string   val  value of the question's `multi_choice` attr [yes|no]
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		multi_choices_toggle: function( val ) {

			if ( 'yes' === val ) {
				return;
			}

			this.model.get( 'choices' ).update_correct( _.first( this.model.get( 'choices' ).get_correct() ) );

		},

		/**
		 * Update the model's points when the value of the points input is updated
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_points: function() {

			this.model.set( 'points', this.$el.find( 'input[name="question_points"]' ).val() * 1 );

		}

	}, Detachable, Editable ) );

} );
