/**
 * Section list (colletion) view
 * @return   void
 * @since    3.13.0
 * @version  [version]
 */
define( [ 'Collections/Sections', 'Mixins/SortableView', 'Views/Section' ], function( collection, Sortable, SectionView ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * DOM element for the view to be added within
		 * @type  {obj}
		 */
		el: $( '#llms-sections' ),

		/**
		 * collection association
		 * @type  {obj}
		 */
		collection: new collection,

		/**
		 * Add a section to the collection
		 * @param    {obj}   section  a section model
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		add_one: function( section ) {
			var view = new SectionView( { model: section } );
			this.$el.append( view.render().el );
		},

		/**
		 * Delete a section from the collection
		 * @param    {obj}   section     a model of the section
		 * @param    {obj}   collection  the collection to remove it from
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		destroy_one: function( section, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},

		/**
		 * Initializer
		 * Setup dom event
		 * & fetch the starting data for the collection
		 * @return   void
		 * @since    3.13.0
		 * @version  [version]
		 */
		initialize: function() {

			var self = this;

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.destroy_one );
			this.listenTo( this.collection, 'rerender', this.render );

			this.collection.fetch( {

				beforeSend: function() {

					Backbone.pubSub.trigger( 'lock' );

				},
				success: function( res ) {

					Backbone.pubSub.trigger( 'unlock' );
					Backbone.pubSub.trigger( 'init-complete' );

				},

			} );

		},

		/**
		 * Render the view
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		render: function() {

			this.$el.children().remove();

			if ( this.collection.length ) {
				this.collection.each( this.add_one, this );
			}

			Backbone.pubSub.trigger( 'rebind' );

			return this;

		},

	}, Sortable ) );

} );
