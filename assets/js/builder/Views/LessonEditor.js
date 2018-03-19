/**
 * Lesson Editor (Sidebar) View
 * @since    [version]
 * @version  [version]
 */
define( [
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Trashable'
	], function(
		Detachable,
		Editable,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		el: '#llms-editor-lesson',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {}, Detachable.events, Editable.events, Trashable.events ),

		/**
		 * Template function
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-lesson-settings-template' ),

		/**
		 * Init
		 * @param    obj   data  parent template data
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function( data ) {

			this.model = data.lesson;

			var change_events = [
				'change:date_available',
				'change:drip_method',
				'change:time_available',
			];
			_.each( change_events, function( event ) {
				this.listenTo( this.model, event, this.render );
			}, this );

		},

		/**
		 * Render the view
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			this.init_datepickers();
			this.init_selects();

			return this;

		},

	}, Detachable, Editable, Trashable ) );

} );
