/**
 * Tutorial Walkthrough View
 * @since    3.13.0
 * @version  3.14.0
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-builder-tutorial',

		/**
		 * Dom Events
		 * @type     {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		events: {
			'click #llms-start-tut': 'start',
		},

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-builder-tutorial-template' ),

		is_active: false,
		current_step: 0,
		steps: [],

		/**
		 * Get object of current step data
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_current_step: function() {
			return this.steps[ this.current_step ];
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			// this.listenTo( Instance.Syllabus.collection, 'add', this.maybe_render );
			// this.listenTo( Instance.Syllabus.collection, 'remove', this.maybe_render );
			// this.listenTo( Instance.Syllabus.collection, 'sync', this.maybe_render );

			this.steps = window.llms_builder.tutorial;

		},

		/**
		 * Enables / Disables the tutorial box
		 * Shows the tutorial when there's no sections in the course
		 * Hides when there are sectiosn in the course
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		maybe_render: function() {

			if ( ! Instance.Syllabus.collection.length ) {
				this.render();
			} else {
				this.$el.fadeOut( 200 );
			}

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			this.$el.html( this.template() );
			this.$el.fadeIn( 200 );
			return this;

		},

		/**
		 * Start the popover tutorial walkthrough
		 * @param    obj   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		start: function( e ) {
			e.preventDefault();
			this.show_next_step( 0 );
		},

		/**
		 * Show a popover for the next step and bind necessary one-time events
		 * @param    int   next_step  step index
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		show_next_step: function( next_step ) {

			this.current_step = next_step;

			var self = this,
				step = this.get_current_step(),
				$content = $( '<div class="llms-tutorial-content" />' );

			$content.append( '<p>' + step.content_main + '</p>' );
			if ( step.content_action ) {
				$content.append( '<p><strong>' + step.content_action + '</strong></p>' );
			}

			if ( step.buttons ) {
				$.each( step.buttons, function( type, text ) {
					var $btn = $( '<button class="llms-button-primary small" type="button">' + text + '</button>' );
					$content.append( $btn );
				} );
			}

			WebuiPopovers.show( step.el, {
				animation: 'pop',
				// backdrop: true,
				content: $content,
				closeable: true,
				// container: '.wrap.llms-course-builder',
				// multi: true,
				placement: step.placement || 'auto',
				title: ( this.current_step + 1 ) + '. ' + step.title,
				trigger: 'manual',
				width: 340,
				onShow: function( $el ) {

					self.is_active = true;

					$( step.el ).add( $el.find( 'button' ) ).one( 'click', function() {
						$el.remove();
						if ( self.current_step < self.steps.length - 1 ) {
							self.show_next_step( self.current_step + 1 );
						} else {
							self.is_active = false;
						}
					} );

				},
				onHide: function() {

					self.is_active = false;

				},
			} );

		},

	} );

} );
