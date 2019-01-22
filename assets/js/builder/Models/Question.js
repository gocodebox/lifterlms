/**
 * Quiz Question
 * @since    3.16.0
 * @version  3.27.0
 */
define( [
		'Models/Image',
		'Collections/Questions',
		'Collections/QuestionChoices',
		'Models/QuestionType',
		'Models/_Relationships',
		'Models/_Utilities'
	], function(
		Image,
		Questions,
		QuestionChoices,
		QuestionType,
		Relationships,
		Utilities
	) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 * @type  {Object}
		 */
		relationships: {
			parent: {
				model: 'llms_quiz',
				type: 'model',
			},
			children: {
				choices: {
					class: 'QuestionChoices',
					model: 'choice',
					type: 'collection',
				},
				image: {
					class: 'Image',
					model: 'image',
					type: 'model',
				},
				questions: {
					class: 'Questions',
					conditional: function( model ) {
						var type = model.get( 'question_type' ),
							type_id = _.isString( type ) ? type : type.get( 'id' );
						return ( 'group' === type_id );
					},
					model: 'llms_question',
					type: 'collection',
				},
				question_type: {
					class: 'QuestionType',
					lookup: function( val ) {
						if ( _.isString( val ) ) {
							return window.llms_builder.questions.get( val );
						}
						return val;
					},
					model: 'question_type',
					type: 'model',
				},
			}
		},

		/**
		 * Model defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				choices: [],
				content: '',
				description_enabled: 'no',
				image: {},
				multi_choices: 'no',
				menu_order: 1,
				points: 1,
				question_type: 'generic',
				questions: [], // for question groups
				parent_id: '',
				title: '',
				type: 'llms_question',
				video_enabled: 'no',
				video_src: '',

				_expanded: false,
			}
		},

		/**
		 * Initializer
		 * @param    obj   data     object of data for the model
		 * @param    obj   options  additional options
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data, options ) {

			var self = this;

			this.startTracking();
			this.init_relationships( options );

			if ( false !== this.get( 'question_type' ).choices ) {

				this._ensure_min_choices();

				// when a choice is removed, maybe add back some defaults so we always have the minimum
				this.listenTo( this.get( 'choices' ), 'remove', function() {
					// new itmes are added at index 0 when there's only 1 item in the collection, not sure why exactly...
					setTimeout( function() {
						self._ensure_min_choices();
					}, 0 );
				} );

			}

			// ensure question types that don't support points don't record default 1 point in database
			if ( ! this.get( 'question_type' ).get( 'points' ) ) {
				this.set( 'points', 0 );
			}

			_.delay( function( self ) {
				self.on( 'change:points', self.get_parent().update_points, self.get_parent() );
			}, 1, this );

		},

		/**
		 * Add a new question choice
		 * @param    obj   data     object of choice data
		 * @param    obj   options  additional options
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_choice: function( data, options ) {

			var max = this.get( 'question_type' ).get_max_choices();
			if ( this.get( 'choices' ).size() >= max ) {
				return;
			}

			data = data || {};
			options = options || {};

			data.choice_type = this.get( 'question_type' ).get_choice_type();
			data.question_id = this.get( 'id' );
			options.parent = this;

			var choice = this.get( 'choices' ).add( data, options );

			Backbone.pubSub.trigger( 'question-add-choice', choice, this );

		},

		/**
		 * Collapse question_type attribute during full syncs to save to database
		 * Not needed because question types cannot be adjusted after question creation
		 * Called from sync controller
		 * @param    obj      atts       flat object of attributes to be saved to db
		 * @param    string   sync_type  full or partial
		 *                                 full indicates a force resync or that the model isn't persisted yet
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		before_save: function( atts, sync_type  ) {
			if ( 'full' === sync_type ) {
				atts.question_type = this.get( 'question_type' ).get( 'id' );
			}
			return atts;
		},

		/**
		 * Retrieve the model's parent (if set)
		 * @return   obj|false
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent: function() {

			var rels = this.get_relationships();

			if ( rels.parent ) {
				if ( this.collection && this.collection.parent ) {
					return this.collection.parent;
				} else if ( rels.parent.reference ) {
					return rels.parent.reference;
				}
			}

			return false;

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.27.0
		 * @version  3.27.0
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'questions' );
			}

			return LLMS.l10n.translate( 'question' );
		},

		/**
		 * Gets the index of the question within it's parent
		 * Question numbers skip content elements
		 * & content elements skip questions
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_type_index: function() {

			// current models type, used to check the predicate in the filter function below
			var curr_type = this.get( 'question_type' ).get( 'id' ),
				questions;

			questions = this.collection.filter( function( question ) {

				var type = question.get( 'question_type' ).get( 'id' );

				// if current model is not content, return all non-content questions
				if ( curr_type !== 'content' ) {
					return ( 'content' !== type );
				}

				// current model is content, return only content questions
				return 'content' === type;

			} );

			return questions.indexOf( this );

		},

		/**
		 * Gets iterator for the given type
		 * Questions use numbers and content uses alphabet
		 * @return   mixed
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_type_iterator: function() {

			var index = this.get_type_index();

			if ( -1 === index ) {
				return '';
			}

			if ( 'content' === this.get( 'question_type' ).get( 'id' ) ) {
				var alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split( '' );
				return alphabet[ index ];
			}

			return index + 1;

		},


		get_qid: function() {

			var parent = this.get_parent_question(),
				prefix = '';

			if ( parent ) {

				prefix = parent.get_qid() + '.';

			}

			// return short_id + this.get_type_iterator();
			return prefix + this.get_type_iterator();

		},

		/**
		 * Retrieve the parent question (if the question is in a question group)
		 * @return   obj|false
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent_question: function() {

			if ( this.is_in_group() ) {

				return this.collection.parent;

			}

			return false;

		},

		/**
		 * Retrieve the parent quiz
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent_quiz: function() {
			return this.get_parent();
		},

		/**
		 * Points getter
		 * ensures that 0 is always returned if the question type doesn't support points
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_points: function() {

			if ( ! this.get( 'question_type' ).get( 'points' ) ) {
				return 0;
			}

			return this.get( 'points' );

		},

		/**
		 * Retrieve the questions percentage value within the quiz
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_points_percentage: function() {

			var total = this.get_parent().get( '_points' ),
				points = this.get( 'points' );

			if ( 0 === total ) {
				return '0%';
			}

			return ( ( points / total ) * 100 ).toFixed( 2 ) + '%';

		},

		/**
		 * Deterine if the question belongs to a question group
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		is_in_group: function() {

			return ( 'question' === this.collection.parent.get( 'type' ) );

		},

		_ensure_min_choices: function() {

			var choices = this.get( 'choices' );
			while ( choices.size() < this.get( 'question_type' ).get_min_choices() ) {
				this.add_choice();
			}

		},

	}, Relationships, Utilities ) );

} );
