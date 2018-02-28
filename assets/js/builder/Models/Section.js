/**
 * Section Model
 * @since    3.16.0
 * @version  3.16.12
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
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				lessons: [],
				order: this.collection ? this.collection.length + 1 : 1,
				parent_course: window.llms_builder.course.id,
				title: LLMS.l10n.translate( 'New Section' ),
				type: 'section',

				_expanded: false,
				_selected: false,
			};
		},

		/**
		 * Initialize
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

		},

		/**
		 * Add a lesson to the section
		 * @param    obj   data     hash of lesson data (creates new lesson)
		 *                          or existing lesson as a Backbone.Model
		 * @param    obj   options  has of options
		 * @return   obj            Backbone.Model of the new/updated lesson
		 * @since    3.16.0
		 * @version  3.16.11
		 */
		add_lesson: function( data, options ) {

			data = data || {};
			options = options || {};

			if ( data instanceof Backbone.Model ) {
				data.set( 'parent_section', this.get( 'id' ) );
				data.set_parent( this );
			} else {
				data.parent_section = this.get( 'id' );
			}

			return this.get( 'lessons' ).add( data, options );

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'sections' );
			}

			return LLMS.l10n.translate( 'section' );
		},

		/**
		 * Get next section in the collection
		 * @param    bool     circular   if true handles the collection in a circle
		 *                               	if current is the last section, returns the first section
		 *                               	if current is the first section, returns the last section
		 * @return   obj|false
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		get_next: function( circular ) {
			return this._get_sibling( 'next', circular );
		},

		/**
		 * Get prev section in the collection
		 * @param    bool     circular   if true handles the collection in a circle
		 *                               	if current is the last section, returns the first section
		 *                               	if current is the first section, returns the last section
		 * @return   obj|false
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		get_prev: function( circular ) {
			return this._get_sibling( 'prev', circular );
		},

		/**
		 * Get a sibling section
		 * @param    string   direction  siblings direction [next|prev]
		 * @param    bool     circular   if true handles the collection in a circle
		 *                               	if current is the last section, returns the first section
		 *                               	if current is the first section, returns the last section
		 * @return   obj|false
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		_get_sibling: function( direction, circular ) {

			circular = ( 'undefined' === circular ) ? true : circular;

			var max = this.collection.size() - 1,
				index = this.collection.indexOf( this ),
				sibling_index;

			if ( 'next' === direction ) {
				sibling_index = index + 1;
			} else if ( 'prev' === direction ) {
				sibling_index = index - 1;
			}

			// dont retrieve greater than max or less than min
			if ( sibling_index <= max || sibling_index <= 0 ) {

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
