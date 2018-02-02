define( [ 'Views/_Editable' ], function( Editable ) {

	return Backbone.View.extend( _.defaults( {

		el: '#llms-quiz-header',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {
			'click a[href="#llms-quiz-settings"]': 'toggle_settings',
		}, Editable.events ),

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'header',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-quiz-header-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			var change_events = [
				'change:_points',
				'change:_show_settings',
			];
			_.each( change_events, function( event ) {
				this.listenTo( this.model, event, this.render );
			}, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			this.init_editor( 'quiz-desc--' + this.model.get( 'id' ) );

			return this;

		},

		toggle_settings: function( event ) {

			event.preventDefault();
			var val = this.model.get( '_show_settings' ) ? false : true;
			this.model.set( '_show_settings', val );

		},

	}, Editable ) );

} );
