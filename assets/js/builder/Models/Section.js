/**
 * Section Model
 *
 * @since 3.16.0
 * @version 4.20.0
 */
define( [ 'Collections/Lessons', 'Models/_Relationships' ], function( Lessons, Relationships ) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			parent: {
				model: 'course',
				type: 'model',
			},
			children: {
				lessons: {
					class: 'Lessons',
					model: 'lesson',
					type: 'collection',
				},
			}
		},

		/**
		 * New section defaults
		 *
		 * @since 3.16.0
		 *
		 * @return {Object}
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				lessons: [],
				order: this.collection ? this.collection.length + 1 : 1,
				parent_course: window.llms_builder.course.id,
				title: LLMS.l10n.translate( 'New Section' ),
				type: 'section',

				// Expand the first 100 sections by default to avoid timeout issues.
				_expanded: ! this.collection || this.collection.length <= 100 ? true : false,
				_selected: false,
			};
		},

		/**
		 * Initialize
		 *
		 * @since 3.16.0
		 *
		 * @return {void}
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

		},

		/**
		 * Add a lesson to the section
		 *
		 * @since 3.16.0
		 * @since 3.16.11 Unknown.
		 *
		 * @param {Object} data    Hash of lesson data (creates new lesson)
		 *                         or existing lesson as a Backbone.Model.
		 * @param {Object} options Hash of options.
		 * @return {Object} Backbone.Model of the new/updated lesson.
		 */
		add_lesson: function( data, options ) {

			data    = data || {};
			options = options || {};

			if ( data instanceof Backbone.Model ) {
				data.set( 'status', 'publish' );
				data.set( 'parent_section', this.get( 'id' ) );
				data.set_parent( this );
			} else {
				data.status = 'publish';
				data.parent_section = this.get( 'id' );
			}

			return this.get( 'lessons' ).add( data, options );

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 *
		 * @since 3.16.12
		 *
		 * @param {Boolean} plural If true, returns the plural, otherwise returns singular.
		 * @return {String}
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'sections' );
			}

			return LLMS.l10n.translate( 'section' );
		},

		/**
		 * Get next section in the collection
		 *
		 * @since 3.16.11
		 *
		 * @param {boolean} circular If true handles the collection in a circle.
		 *                           If current is the last section, returns the first section.
		 * @return {Object}|false
		 */
		get_next: function( circular ) {
			return this._get_sibling( 'next', circular );
		},

		/**
		 * Retrieve a reference to the parent course of the section
		 *
		 * @since 4.14.0
		 *
		 * @return {Object}
		 */
		get_course: function() {

			// When working with an unsaved draft course the parent isn't properly set on the creation of a section.
			if ( ! this.get_parent() ) {
				this.set_parent( window.llms_builder.CourseModel );
			}

			return this.get_parent();

		},

		/**
		 * Get prev section in the collection
		 *
		 * @since 3.16.11
		 *
		 * @param {Boolean} circular If true handles the collection in a circle.
		 *                           If current is the first section, returns the last section.
		 * @return {Object}|false
		 */
		get_prev: function( circular ) {
			return this._get_sibling( 'prev', circular );
		},

		/**
		 * Get a sibling section
		 *
		 * @since 3.16.11
		 * @since 4.20.0 Fix case when the last section was returned when looking for the prev of the first section and not `circular`.
		 *
		 * @param {String}  direction Siblings direction [next|prev].
		 * @param {Boolean} circular  If true handles the collection in a circle.
		 *                            If current is the last section, returns the first section.
		 *                            If current is the first section, returns the last section.
		 * @return {Object}|false
		 */
		_get_sibling: function( direction, circular ) {

			circular = ( 'undefined' === circular ) ? true : circular;

			var max   = this.collection.size() - 1,
				index = this.collection.indexOf( this ),
				sibling_index;

			if ( 'next' === direction ) {
				sibling_index = index + 1;
			} else if ( 'prev' === direction ) {
				sibling_index = index - 1;
			}

			// Don't retrieve greater than max or less than min.
			if ( sibling_index <= max || sibling_index >= 0 ) {

				return this.collection.at( sibling_index );

			} else if ( circular ) {

				if ( 'next' === direction ) {
					return this.collection.first();
				} else if ( 'prev' === direction ) {
					return this.collection.last();
				}

			}

			return false;

		},

	}, Relationships ) );

} );
