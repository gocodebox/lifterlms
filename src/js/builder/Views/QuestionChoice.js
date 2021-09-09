/**
 * Single Question Choice View
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Views/_Editable', ], function( Editable ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-question-choice',

		events: _.defaults( {
			'change input[name="correct"]': 'toggle_correct',
			'click .llms-action-icon[href="#llms-add-choice"]': 'add_choice',
			'click .llms-action-icon[href="#llms-del-choice"]': 'del_choice',
		}, Editable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		id: function() {
			return 'llms-question-choice-' + this.model.id;
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
		template: wp.template( 'llms-question-choice-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function() {

			this.render();

			this.listenTo( this.model.collection, 'add', this.maybe_disable_buttons );
			this.listenTo( this.model, 'change', this.render );

			if ( 'image' === this.model.get( 'choice_type' ) ) {
				this.listenTo( this.model.get( 'choice' ), 'change', this.render );
			}

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {
			this.$el.html( this.template( this.model ) );
			return this;
		},

		/**
		 * Add a new choice to the current choice list
		 * Adds *after* the clicked choice
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_choice: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			var index = this.model.collection.indexOf( this.model );
			this.model.collection.parent.add_choice( {}, {
				at: index + 1,
			} );

		},

		/**
		 * Delete the choice from the choice list & ensure there's at least one correct choice
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		del_choice: function( event ) {

			event.preventDefault();
			Backbone.pubSub.trigger( 'model-trashed', this.model );
			this.model.collection.remove( this.model );

		},

		/**
		 * When the correct answer input changes sync status to model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		toggle_correct: function() {

			var correct = this.$el.find( 'input[name="correct"]' ).is( ':checked' );
			this.model.set( 'correct', correct );
			this.model.collection.trigger( 'correct-update', this.model );

		},

	}, Editable ) );

} );
