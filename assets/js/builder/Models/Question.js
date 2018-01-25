/**
 * Quiz Question
 * @since    [version]
 * @version  [version]
 */
define( [
		'Models/Image',
		'Collections/Questions',
		'Collections/QuestionChoices',
		'Models/QuestionType',
		'Models/_Relationships'
	], function(
		Image,
		Questions,
		QuestionChoices,
		QuestionType,
		Relationships
	) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			parent: {
				model: 'quiz',
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
					model: 'question',
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

		defaults: function( defaults ) {
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
				type: 'question',
				video_enabled: 'no',
				video_src: '',

				_expanded: false,
			}
		},

		initialize: function( data, options ) {

			var self = this;

			// backwards compat legacy 'single_choice' is now 'choice'
			if ( 'single_choice' === this.get( 'question_type' ) ) {
				this.set( 'question_type', 'choice' );
			}

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

		add_choice: function( data, options ) {

			var max = this.get( 'question_type' ).get_max_choices();
			if ( this.get( 'choices' ).size() >= max ) {
				return;
			}

			data = data || {};
			options = options || {};

			data.choice_type = this.get( 'question_type' ).get_choice_type();
			data.question_id = this.get( 'id' );
			this.get( 'choices' ).add( data, options );

		},

		/**
		 * Collapse question_type attribute during full syncs to save to database
		 * Not needed because question types cannot be adjusted after question creation
		 * Called from sync controller
		 * @param    obj      atts       flat object of attributes to be saved to db
		 * @param    string   sync_type  full or partial
		 *                                 full indicates a force resync or that the model isn't persisted yet
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
		 */
		get_parent: function() {

			var rels = this.get_relationships();

			if ( rels.parent ) {
				if ( rels.parent.reference ) {
					return rels.parent.reference;
				} else if ( this.collection && this.collection.parent ) {
					return this.collection.parent;
				}
			}

			return false;

		},

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

			// var question_type_id = this.get( 'question_type' ).get( 'id' ),
			// 	short_id = LLMS.l10n.translate( 'Q' );

			// if ( 'group' === question_type_id ) {

			// 	short_id = LLMS.l10n.translate( 'G' );

			// } else if ( 'content' === question_type_id ) {

			// 	short_id = LLMS.l10n.translate( 'C' );

			// }

			// return short_id + this.get_type_iterator();
			return prefix + this.get_type_iterator();

		},

		/**
		 * Retrieve the parent question (if the question is in a question group)
		 * @return   obj|false
		 * @since    [version]
		 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
		 */
		get_parent_quiz: function() {
			return this.get_parent();
		},

		get_points: function() {

			if ( ! this.get( 'question_type' ).get( 'points' ) ) {
				return 0;
			}

			return this.get( 'points' );

		},

		/**
		 * Retrieve the questions percentage value within the quiz
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		get_points_percentage: function() {

			var total = this.get_parent().get( '_points' ),
				points = this.get( 'points' );

			if ( 0 === total ) {
				return '0%';
			}

			return ( ( points / total ) * 100 ).toFixed( 2 ) + '%';

		},

		is_in_group: function() {

			return ( 'question' === this.collection.parent.get( 'type' ) );

		},

		_ensure_min_choices: function() {

			var choices = this.get( 'choices' );
			while ( choices.size() < this.get( 'question_type' ).get_min_choices() ) {
				this.add_choice();
			}

		},

	}, Relationships ) );

} );
