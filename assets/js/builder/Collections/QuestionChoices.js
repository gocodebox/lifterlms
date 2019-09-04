/**
 * Question Choice Collection
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Models/QuestionChoice' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 *
		 * @type  obj
		 */
		model: model,

		initialize: function() {

			// reorder called by QuestionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a choice is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

			// when a choice is added or remove, ensure min/max correct answers exist
			this.on( 'add', this.update_correct );
			this.on( 'remove', this.update_correct );

			// when a choice is toggled, ensure min/max correct exist
			this.on( 'correct-update', this.update_correct );

		},

		/**
		 * Retrieve the number of correct choices in the collection
		 *
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		count_correct: function() {

			return _.size( this.get_correct() );

		},

		/**
		 * Retrieve the collection reduced to only correct choices
		 *
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_correct: function() {
			return this.filter( function( choice ) {
				return choice.get( 'correct' );
			} );
		},

		/**
		 * Ensure min/max correct choices exist in the collection based on the question's settings
		 *
		 * @param    obj      choice  model of the choice that was toggled
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_correct: function( choice ) {

			if ( ! this.parent.get( 'question_type' ).get_choice_selectable() ) {
				return;
			}

			var siblings = this.without( choice ), // exclude the toggled choice from loops
				question = this.parent;

			// if multiple choices aren't enabled turn all other choices to incorrect
			if ( 'no' === question.get( 'multi_choices' ) ) {
				_.each( siblings, function( model ) {
					model.set( 'correct', false );
				} );
			}

			// if we don't have a single correct answer & the question has points, set one
			// allows users to create quizzes / questions with no points and therefore no correct answers are allowed
			if ( 0 === this.count_correct() && question.get( 'points' ) > 0 ) {
				var models = 1 === this.size() ? this.models : siblings;
				_.first( models ).set( 'correct', true );
			}

		},

		/**
		 * Update the marker attr of each choice in the list to reflect the order of the collection
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_order: function() {

			var self     = this,
				question = this.parent;

			this.each( function( choice ) {
				choice.set( 'marker', question.get( 'question_type' ).get_choice_markers()[ self.indexOf( choice ) ] );
			} );

		},

	} );

} );
