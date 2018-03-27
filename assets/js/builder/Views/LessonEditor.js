/**
 * Lesson Editor (Sidebar) View
 * @since    3.17.0
 * @version  3.17.0
 */
define( [
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Trashable',
		'Views/_Subview',
		'Views/SettingsFields'
	], function(
		Detachable,
		Editable,
		Trashable,
		Subview,
		SettingsFields
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default',

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			settings: {
				class: SettingsFields,
				instance: null,
				state: 'default',
			},
		},

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
		 * @since    3.17.0
		 * @version  3.17.0
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

			// when the "has_prerequisite" attr is toggled ON
			// trigger the prereq select object to set the default (first available) prereq for the lesson
			this.listenTo( this.model, 'change:has_prerequisite', function( lesson, val ) {
				if ( 'yes' === val ) {
					this.$el.find( 'select[name="prerequisite"]' ).trigger( 'change' );
				}
			} );

		},

		/**
		 * Render the view
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			this.remove_subview( 'settings' );

			this.render_subview( 'settings', {
				el: '#llms-lesson-settings-fields',
				model: this.model,
			} );

			this.init_datepickers();
			this.init_selects();

			return this;

		},

	}, Detachable, Editable, Trashable, Subview, SettingsFields ) );

} );
