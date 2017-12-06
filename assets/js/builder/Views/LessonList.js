/**
 * Lesson List (collection) view
 * @since    3.13.0
 * @version  3.13.0
 */
define( [ 'Mixins/SortableView', 'Views/Lesson' ], function( Sortable, LessonView ) {

	return  Backbone.View.extend( _.defaults( {

		/**
		 * Add a lesson to the collection
		 * @param    {obj}   lesson  model of the lesson
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		add_one: function( lesson ) {
			var view = new LessonView( { model: lesson } );
			this.$el.append( view.render().el );
		},

		/**
		 * Initializer
		 * Bind collection events
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		initialize: function() {

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.remove_one );
			this.listenTo( this.collection, 'remove', this.remove_one );
			this.listenTo( this.collection, 'rerender', this.render );

		},

		/**
		 * Remove a lesson from a collection
		 * @param    {obj}      lesson      model of the lesson to remove
		 * @param    {obj}      collection  collection to remove the lesson from
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		remove_one: function( lesson, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},


		/**
		 * Render the view
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		render: function() {
			this.$el.children().remove();
			this.collection.each( this.add_one, this );
			// App.Methods.sortable();
			return this;
		},

	}, Sortable ) );

} );
