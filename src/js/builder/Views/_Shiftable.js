/**
 * Shiftable view mixin function
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return {

		/**
		 * Conditionally hide action buttons based on section position in collection
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		maybe_hide_shiftable_buttons: function() {

			if ( ! this.model.collection ) {
				return;
			}

			var type = this.model.get( 'type' );

			if ( this.model.collection.first() === this.model ) {
				this.$el.find( '.shift-up--' + type ).hide();
			} else if ( this.model.collection.last() === this.model ) {
				this.$el.find( '.shift-down--' + type ).hide();
			}

		},

		/**
		 * Move an item in a collection from one position to another
		 *
		 * @param    int   old_index  current (old) index within the collection
		 * @param    int   new_index  desired (new) index within the collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		shift: function( old_index, new_index ) {

			var collection = this.model.collection;

			collection.remove( this.model );
			collection.add( this.model, { at: new_index } );
			collection.trigger( 'reorder' );

		},

		/**
		 * Move an item down the tree one position
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		shift_down: function( e ) {

			e.preventDefault();
			var index = this.model.collection.indexOf( this.model );
			this.shift( index, index + 1 );

		},

		/**
		 * Move an item up the tree one position
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		shift_up: function( e ) {

			e.preventDefault();
			var index = this.model.collection.indexOf( this.model );
			this.shift( index, index - 1 );

		},

	};

} );
