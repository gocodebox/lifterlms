/**
 * Single Section View
 * @since    3.13.0
 * @version  3.14.0
 */
define( [ 'Collections/Lessons', 'Mixins/EditableView', 'Mixins/ShiftableView', 'Views/LessonList' ], function( LessonCollection, Editable, Shiftable, LessonListView ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * Return CSS classes for the html wrapper element
		 * @return   string
		 * @since    3.14.5
		 * @version  3.14.5
		 */
		className: function() {
			var classes = [ 'llms-builder-item', 'llms-section' ];
			if ( this.model.get( 'opened' ) ) {
				classes.push( 'opened' );
			}
			return classes.join( ' ' );
		},

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.13.0
		 * @version  3.14.0
		 */
		events: _.defaults( {
			'click .llms-action-icon.expand': 'lessons_show',
			'click .llms-action-icon.collapse': 'lessons_hide',
			'click .llms-action-icon.trash': 'delete_section',
			'drop-section': 'drop',
		}, Editable.events, Shiftable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		id: function() {
			return 'llms-section-' + this.model.id;
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
		template: wp.template( 'llms-section-template' ),

		/**
		 * Handles deletion of a section
		 * Will only delete empty sections
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		delete_section: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			// can't delete sections with lessons
			if ( this.model.Lessons.collection.length ) {
				alert( LLMS.l10n.translate( 'You must remove all lessons before deleting a section.' ) );
				return;
			}

			var del_id = 'delete_' + this.model.id;

			this.model.destroy( {
				beforeSend: function() {
					Instance.Status.add( del_id );
				},
				success: function( res ) {
					Instance.Status.remove( del_id );
				},
			} );

		},

		/**
		 * jQuery UI sortable drop event handler
		 * @param    {obj}   event  js event object
		 * @param    {int}   index  new index of the dropped element
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		drop: function( event, index ) {

			var self = this,
				auto_save = true;

			// create if the model doesn't have a collection
			if ( ! this.model.collection ) {
				var id = self.model.id;
				auto_save = false;
				Instance.Syllabus.collection.create( self.model, {
					beforeSend: function() {
						Instance.Status.add( id );
					},
					success: function( res ) {
						Instance.Status.remove( id );
						self.model.collection.sync_order();
					},
				} );
			}

			self.$el.trigger( 'update-sort', [ self.model, index + 1, self.model.collection, null, auto_save ] );

		},

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		initialize: function() {

			// this.listenTo( this.model, 'sync', this.render );

			if ( ! this.model.Lessons ) {

				// setup lessons child view & collection
				this.model.Lessons = new LessonListView( {
					collection: new LessonCollection,
				} );
				this.model.Lessons.collection.add( this.model.get( 'lessons' ) );

			}


		},

		/**
		 * Hide lessons in the section
		 * @param    {obj}   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		lessons_hide: function( e ) {
			e.preventDefault();
			this.$el.removeClass( 'opened' );
			this.model.set( 'opened', false );
		},

		/**
		 * Show lessons in the section
		 * @param    {obj}   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		lessons_show: function( e ) {
			e.preventDefault();
			this.$el.addClass( 'opened' );
			this.model.set( 'opened', true );
		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		render: function() {

			// render inside
			this.$el.html( this.template( this.model.toJSON() ) );

			this.model.Lessons.setElement( this.$el.find( '.llms-lessons' ) ).render();

			// if the id has changed (when creating a new section for example) update the attributes and id
			if ( this.$el.attr( 'id' ) != this.model.id ) {
				this.$el.attr( 'id', this.id() );
				this.$el.attr( this.attributes() );
			}

			return this;

		},

	}, Editable, Shiftable ) );

} );
