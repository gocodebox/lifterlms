/**
 * Single Lesson View
 * @since    3.13.0
 * @version  [version]
 */
define( [ 'Views/_Editable', 'Views/_Shiftable' ], function( Editable, Shiftable ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
				'data-section-id': this.model.get( 'parent_section' ),
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-lesson',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {
			'click .llms-headline': 'on_click',
			'click .shift-up--lesson': 'shift_up',
			'click .shift-down--lesson': 'shift_down',
			'click .detach--lesson': 'detach',
			'click .trash--lesson': 'trash',
		}, Editable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		id: function() {
			return 'llms-lesson-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-lesson-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function() {

			this.render();

			this.listenTo( this.model, 'change', this.render );

			Backbone.pubSub.on(  'lesson-selected', this.on_select, this );
			Backbone.pubSub.on(  'new-lesson-added', this.on_select, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  [version]
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );
			this.maybe_hide_shiftable_buttons();
			if ( this.model.get( '_selected' ) ) {
				this.$el.addClass( 'selected' );
			} else {
				this.$el.removeClass( 'selected' );
			}
			return this;

		},

		/**
		 * Remove lesson from course and delete it
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		detach: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			if ( window.confirm( LLMS.l10n.translate( 'Are you sure you want to remove this lesson from the course?' ) ) ) {

				this.model.collection.remove( this.model );
				Backbone.pubSub.trigger( 'model-detached', this.model );

			}


		},

		on_click: function( event ) {

			var $el = $( event.target );
			if ( $el.is( '.llms-input' ) ) {
				return;
			}

			Backbone.pubSub.trigger( 'lesson-selected', this.model );

			this.model.set( '_selected', true );

		},

		on_select: function( model ) {

			if ( this.model.id !== model.id ) {
				this.model.set( '_selected', false );
			}

		},

		/**
		 * Remove lesson from course and delete it
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		trash: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			if ( window.confirm( LLMS.l10n.translate( 'Are you sure you want to move this lesson to the trash?' ) ) ) {

				this.model.collection.remove( this.model );
				Backbone.pubSub.trigger( 'model-trashed', this.model );

			}


		},

	}, Editable, Shiftable ) );

} );
