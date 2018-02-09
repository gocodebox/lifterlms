/**
 * Main sidebar view
 * @since    3.16.0
 * @version  3.16.7
 */
define( [
		'Views/Editor',
		'Views/Elements',
		'Views/Utilities',
		'Views/_Subview'
	], function(
		Editor,
		Elements,
		Utilities,
		Subview
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current builder state
		 * @type  {String}
		 */
		state: 'builder', // [builder|editor]

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			elements: {
				class: Elements,
				instance: null,
				state: 'builder',
			},
			utilities: {
				class: Utilities,
				instance: null,
				state: 'builder',
			},
			editor: {
				class: Editor,
				instance: null,
				state: 'editor',
			},
		},

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-builder-sidebar',

		/**
		 * DOM events
		 * @type  {Object}
		 */
		events: {
			'click #llms-save-button': 'save_now',
			'click #llms-exit-button': 'exit_now',
			'click .llms-builder-error': 'clear_errors',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'aside',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-sidebar-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data ) {

			// save a reference to the main Course view
			this.CourseView = data.CourseView;

			this.render();

			Backbone.pubSub.on( 'current-save-status', this.changes_made, this );

			Backbone.pubSub.on( 'heartbeat-send', this.heartbeat_send, this );
			Backbone.pubSub.on( 'heartbeat-tick', this.heartbeat_tick, this );

			Backbone.pubSub.on( 'lesson-selected', this.on_lesson_select, this );
			Backbone.pubSub.on( 'sidebar-editor-close', this.on_editor_close, this );

			this.$saveButton = $( '#llms-save-button' );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function( view_data ) {

			view_data = view_data || {};

			this.$el.html( this.template() );

			this.render_subviews( _.extend( view_data, {
				SidebarView: this,
			} ) );

			var $el = $( '.wrap.lifterlms.llms-builder' );
			if ( 'builder' === this.state ) {
				$el.removeClass( 'editor-active' );
			} else {
				$el.addClass( 'editor-active' );
			}

			this.$saveButton = this.$el.find( '#llms-save-button' );

			return this;

		},

		/**
		 * Adds error message element
		 * @param    {[type]}   $err  [description]
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_error: function( $err ) {

			this.$el.find( '.llms-builder-save' ).prepend( $err );

		},

		/**
		 * Clear any existing error message elements
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		clear_errors: function() {

			this.$el.find( '.llms-builder-save .llms-builder-error' ).remove();

		},

		/**
		 * Update save status button when changes are detected
		 * runs on an interval to check status of course regularly for unsaved changes
		 * @param    obj   sync  instance of the sync controller
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		changes_made: function( sync ) {

			// if a save is currently running, don't do anything
			if ( sync.saving ) {
				return;
			}

			if ( sync.has_unsaved_changes ) {

				this.$saveButton.attr( 'data-status', 'unsaved' );
				this.$saveButton.removeAttr( 'disabled' );

			} else {

				this.$saveButton.attr( 'data-status', 'saved' );
				this.$saveButton.attr( 'disabled', 'disabled' );

			}

		},

		/**
		 * Exit the builder and return to the WP Course Editor
		 * @return   void
		 * @since    3.16.7
		 * @version  3.16.7
		 */
		exit_now: function() {

			window.location.href = window.llms_builder.CourseModel.get_edit_post_link();

		},

		/**
		 * Triggered when a heartbeat send event starts containing builder information
		 * @param    obj   sync  instance of the sync controller
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		heartbeat_send: function( sync ) {

			if ( sync.saving ) {
				LLMS.Spinner.start( this.$saveButton.find( 'i' ), 'small' );
				this.$saveButton.attr( {
					'data-status': 'saving',
					disabled: 'disabled',
				} );
			}

		},

		/**
		 * Triggered when a heartbeat tick completes and updates save status or appends errors
		 * @param    obj   sync  instance of the sync controller
		 * @param    obj   data  updated data
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		heartbeat_tick: function( sync, data ) {

			if ( ! sync.saving ) {

				var status = 'saved';

				this.clear_errors();

				if ( 'error' === data.status ) {

					status = 'error';

					var msg = data.message,
						$err = $( '<ol class="llms-builder-error" />' );

					if ( 'object' === typeof msg ) {
						_.each( msg, function( txt ) {
							$err.append( '<li>' + txt + '</li>' );
						} );
					} else {
						$err = $err.append( '<li>' + msg + '</li>' );;
					}

					this.add_error( $err );

				}

				this.$saveButton.find( '.llms-spinning' ).remove();
				this.$saveButton.attr( {
					'data-status': status,
					disabled: 'disabled',
				} );

			}

		},

		/**
		 * Determine if the editor is the currently active state
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		is_editor_active: function() {

			return ( 'editor' === this.state );

		},

		/**
		 * Triggered when the editor closes, updates state to be the course builder view
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_editor_close: function() {

			this.set_state( 'builder' ).render();

		},

		/**
		 * When a lesson is selected, opens the sidebar to the editor view
		 * @param    obj   lesson_model  instance of the lesson model which was selected
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_lesson_select: function( lesson_model, tab ) {

			if ( 'editor' !== this.state ) {
				this.set_state( 'editor' );
			} else {
				this.remove_subview( 'editor' );
			}

			this.render( {
				model: lesson_model,
				tab: tab,
			} );

		},

		/**
		 * Save button click event
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		save_now: function() {

			window.llms_builder.sync.save_now();

		},

	}, Subview ) );

} );
