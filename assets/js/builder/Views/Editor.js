/**
 * Sidebar Editor View
 * @since    [version]
 * @version  [version]
 */
define( [ 'Views/LessonEditor', 'Views/Quiz', 'Views/_Subview' ], function( LessonEditor, Quiz, Subview ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'lesson', // [lesson|quiz]

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			lesson: {
				class: LessonEditor,
				instance: null,
				state: 'lesson',
			},
			quiz: {
				class: Quiz,
				instance: null,
				state: 'quiz',
			},
		},

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-editor',

		events: {
			'click .llms-editor-nav a[href="#llms-editor-close"]': 'close_editor',
			'click .llms-editor-nav a:not([href="#llms-editor-close"])': 'switch_tab',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-editor-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function( data ) {

			this.SidebarView = data.SidebarView;
			if ( data.tab ) {
				this.state = data.tab;
			}

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function( view_data ) {

			view_data = view_data || {};

			this.$el.html( this.template( this ) );

			this.render_subviews( _.extend( view_data, {
				lesson: this.model,
			} ) );

			return this;

		},

		/**
		 * Click event for close sidebar editor button
		 * Sends event to main SidebarView to trigger editor closing events
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		close_editor: function( event ) {

			event.preventDefault();
			Backbone.pubSub.trigger( 'sidebar-editor-close' );

		},

		/**
		 * Click event for switching tabs in the editor navigation
		 * @param    object  event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		switch_tab: function( event ) {

			event.preventDefault();

			var $btn = $( event.target ),
				view = $btn.attr( 'data-view' ),
				$tab = this.$el.find( $btn.attr( 'href' ) );

			this.set_state( view ).render();

			// Backbone.pubSub.trigger( 'editor-tab-activated', $btn.attr( 'href' ).substring( 1 ) );

		},

	}, Subview ) );

} );
