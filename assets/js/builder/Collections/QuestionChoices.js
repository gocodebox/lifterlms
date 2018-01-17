/**
 * Question Choice Collection
 * @since    [version]
 * @version  [version]
 */
define( [ 'Models/QuestionChoice' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		initialize: function() {

			// reorder called by QuestionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a choice is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

			// when a choice is added or remove, ensure min correct answers exist
			this.on( 'add', this.ensure_correct );
			this.on( 'remove', this.ensure_correct );

			// called from Question view to ensure min/max correct options exist
			this.on( 'question-choices-update-correct', this.update_correct );

		},

		/**
		 * Retrieve the number of correct choices in the collection
		 * @return   int
		 * @since    [version]
		 * @version  [version]
		 */
		count_correct: function() {

			return _.size( this.filter( function( choice ) {
				return choice.get( 'correct' );
			} ) );

		},

		/**
		 * Ensure at least one correct answer exists
		 * Called when adding/removing choices and when toggling correct choices off
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		ensure_correct: function() {

			var correct = this.filter( function( choice ) {
				return choice.get( 'correct' );
			} );

			if ( correct.length > 1 ) {

				_.each( correct, function( choice, index ) {

					if ( index > 0 ) {
						choice.set( 'correct', false );
					}

				} );

			} else {

				this.first().set( 'correct', true );

			}

		},

		/**
		 * Ensure min/max correct choices exist in the collection based on the question's settings
		 * @param    obj      choice  model of the choice that was toggled
		 * @param    string   multi   value of the question's multi_choice attribute setting [yes|no]
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update_correct: function( choice, multi, points ) {

			var siblings = this.without( choice ); // exclude the toggled choice from loops

			if ( 'no' === multi ) {

				_.each( siblings, function( model ) {
					model.set( 'correct', false );
				} );

			}

			// if we don't have a single corret answer & the question has points, set one
			// allows users to create quizzes / questions with no points and therefore no correct answers are allowed
			if ( 0 === this.count_correct() && points > 0 ) {
				_.first( siblings ).set( 'correct', true );
			}

		},

		/**
		 * Update the marker attr of each choice in the list to reflect the order of the collection
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update_order: function() {

			var self = this;

			this.each( function( choice ) {
				choice.set( 'marker', window.llms_builder.choice_markers[ self.indexOf( choice ) ] );
			} );

		},

	} );

} );
