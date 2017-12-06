/**
 * Single Lesson View
 * @since    3.13.0
 * @version  3.14.4
 */

define( [ 'Mixins/EditableView', 'Mixins/ShiftableView' ], function( Editable, Shiftable ) {

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
				'data-section-id': this.model.get( 'section_id' ),
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-lesson',

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		events: _.defaults( {
			'drop-lesson': 'drop',
			'update-parent': 'update_parent',
			'click .llms-action-icon.section-prev': 'section_prev',
			'click .llms-action-icon.section-next': 'section_next',
			'click .llms-action-icon.trash': 'delete_lesson',
			'click .llms-action-icon.detach': 'detach_lesson',
		}, Editable.events, Shiftable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		id: function() {
			return 'llms-lesson-' + this.model.id;
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
		template: wp.template( 'llms-lesson-template' ),

		/**
		 * Event handler for lesson deletion
		 * requires a confirmation before removing from collection & syncing
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		delete_lesson: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			var msg = LLMS.l10n.translate( 'Are you sure you want to permanently delete this lesson?' );

			if ( ! window.confirm( msg ) ) {
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
		 * Removes a lesson from the course (turns it into an orphan)
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		detach_lesson: function( event ) {

			event.stopPropagation();
			event.preventDefault();
			this.model.trigger( 'detach' );

		},

		/**
		 * Draggable/Sortable DROP event
		 * @param    {obj}   event            js event obj
		 * @param    {obj}   $item            jQuery obj of the dropped item
		 * @param    {int}   to_section_id    id of the section the lesson was dropped into
		 * @param    {int}   from_section_id  id of the section the lesson came from (may be undefined)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		drop: function( event, $item, to_section_id, from_section_id ) {

			var self = this,
				to_collection = Instance.Syllabus.collection.get( to_section_id ).Lessons.collection,
				from_collection = ! this.model.collection ? null : Instance.Syllabus.collection.get( from_section_id ).Lessons.collection,
				auto_save = true;

			// create if the model doesn't have a collection
			if ( ! this.model.collection ) {
				var id = self.model.id;
				auto_save = false;
				self.model.set( 'section_id', to_section_id );
				to_collection.create( self.model, {
					beforeSend: function() {
						Instance.Status.add( id );
					},
					success: function( res ) {
						Instance.Status.remove( id );
						self.model.collection.sync_order();
					},
				} );
			}

			this.$el.trigger( 'update-sort', [ this.model, $item.index() + 1, to_collection, from_collection, auto_save ] );

		},

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function() {
			this.listenTo( this.model, 'sync', this.render );
		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			if ( ! this.model.get( 'section_id' ) ) {
				return this;
			}
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

		/**
		 * Event handler for moving a lesson to the next section in the sections collection
		 * this moves lesson DOWN a section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		section_next: function() {

			var from_section = this.model.get_section(),
				to_section = from_section.get_next(),
				from_collection = from_section.Lessons.collection
				to_collection = to_section.Lessons.collection,

			$( '#llms-section-' + to_section.id ).addClass( 'opened' );

			// update the parent
			this.model.set( 'section_id', to_section.id );

			// trigger resorts on the collections
			this.$el.trigger( 'update-sort', [ this.model, 1, to_collection, from_collection ] );

		},

		/**
		 * Event handler for moving a lesson to the previous section in the sections collection
		 * this moves lesson UP a section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		section_prev: function() {

			var from_section = this.model.get_section(),
				to_section = from_section.get_prev(),
				from_collection = from_section.Lessons.collection
				to_collection = to_section.Lessons.collection,

			$( '#llms-section-' + to_section.id ).addClass( 'opened' );

			// update the parent
			this.model.set( 'section_id', to_section.id );

			// trigger resorts on the collections
			this.$el.trigger( 'update-sort', [ this.model, to_collection.next_order(), to_collection, from_collection ] );

		},

		/**
		 * jQuery UI sortable "receieve" callback
		 * when a lesson is moved to a new section the drop event handles most stuff
		 * but this updates the section_id attribute on the lesson
		 * @param    {obj}   event  js event object
		 * @param    {obj}   item   jQuery ui sortable item object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		update_parent: function( event, item ) {
			this.model.set( 'section_id', $( item ).closest( '.llms-section' ).attr( 'data-id' ) );
		},

	}, Editable, Shiftable ) );

} );
