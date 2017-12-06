/**
 * Handles UX and Events for shifting views up and down
 * Use with a Model's View
 * Used with Section and Lesson views
 * @type     {Object}
 * @since    3.13.0
 * @version  3.13.0
 */
define( [], function() {

	return {

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		events: {
			'click .llms-action-icon.shift-down': 'shift_down',
			'click .llms-action-icon.shift-up': 'shift_up',
		},

		/**
		 * Shift model one space down (to the right or +1) in it's collection
		 * automatically resorts other items in collection and syncs collection to db
		 * @param    obj   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		shift_down: function( e ) {
			e.stopPropagation();
			e.preventDefault();
			this.$el.trigger( 'update-sort', [ this.model, this.model.get( 'order' ) + 1, this.model.collection ] );
		},

		/**
		 * Shift model one space up (to the right or -1) in it's collection
		 * automatically resorts other items in collection and syncs collection to db
		 * @param    obj   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		shift_up: function( e ) {
			e.stopPropagation();
			e.preventDefault();
			this.$el.trigger( 'update-sort', [ this.model, this.model.get( 'order' ) - 1, this.model.collection ] );
		},

	};

} );
