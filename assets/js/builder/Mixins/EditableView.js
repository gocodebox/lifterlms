/**
 * Handles UX and Events for inline editing of views
 * Use with a Model's View
 * Allows editing model.title field via .llms-editable-title elements
 * @type     {Object}
 * @since    3.13.0
 * @version  3.14.1
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
			'focusout .llms-editable-title': 'on_blur',
			'keydown .llms-editable-title': 'on_keydown',
		},

		/**
		 * Determine if changes have been made to the element
		 * @param    {[obj]}   event  js event object
		 * @return   {Boolean}        true when changes have been made, false otherwise
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		has_changed: function( event ) {
			var $el = $( event.target );
			return ( $el.attr( 'data-original-content' ) !== $el.text() );
		},

		/**
		 * Blur/focusout function for .llms-editable-title elements
		 * Automatically saves changes if changes have been made
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		on_blur: function( event ) {

			event.stopPropagation();

			var self = this,
				changed = this.has_changed( event );

			if ( changed ) {
				this.save_edits( event );
			}

		},

		/**
		 * Keydown function for .llms-editable-title elements
		 * Blurs
		 * @param    {obj]}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.1
		 */
		on_keydown: function( event ) {

			event.stopPropagation();

			var self = this,
				key = event.which || event.keyCode;

			switch ( key ) {

				case 13: // enter
					event.preventDefault();
					event.target.blur();
				break;

				case 27: // escape
					event.preventDefault();
					this.revert_edits( event );
					event.target.blur();
				break;

			}

		},

		/**
		 * Helper to undo changes
		 * Bound to "escape" key via on_keydwon function
		 * @param    {[type]}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		revert_edits: function( event ) {
			var $el = $( event.target ),
				val = $el.attr( 'data-original-content' );
			$el.text( val );
		},

		/**
		 * Sync chages to the model and DB
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  [version]
		 */
		save_edits: function( event ) {

			var $el = $( event.target ),
				val = $el.text(),
				save_id = 'edit_' + this.model.id;

			this.model.set( 'title', val ).save( null, {
				beforeSend: function() {
					window.llms_builder.Instance.Status.add( save_id );
				},
				error: function( res ) {
					console.log( res );
				},
				success: function( res ) {
					window.llms_builder.Instance.Status.remove( save_id );
				},
			} );

		},

	};

} );
