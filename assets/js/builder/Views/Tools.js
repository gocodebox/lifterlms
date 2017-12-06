/**
 * "Tools" sidebar view
 * @since    3.13.0
 * @version  [version]
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * Dom Element
		 * @type  {obj}
		 */
		el: $( '#llms-builder-tools' ),

		/**
		 * Dom Events
		 * @type     {Object}
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		events: {
			'click button.llms-add-item': 'add_item',
			'click button#llms-existing-lesson': 'show_search_popover',
			'click a.bulk-toggle': 'bulk_toggle',
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			// this.listenTo( Instance.Syllabus.collection, 'add', this.maybe_disable );
			// this.listenTo( Instance.Syllabus.collection, 'remove', this.maybe_disable );
			// this.listenTo( Instance.Syllabus.collection, 'sync', this.maybe_disable );

		},

		/**
		 * Add a new item to the syllabus (section or lesson)
		 * @param    {obj}   event  js event object
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		add_item: function( event ) {

			event.preventDefault();

			var $btn = $( event.target ),
				type = $btn.attr( 'data-model' );

			this.create_item( type, {
				id: _.uniqueId( type + '_temp_' )
			} );

		},

		/**
		 * Bulk expan and collapse the syllabus via click events
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		bulk_toggle: function( event ) {

			event.preventDefault();
			var $btn = $( event.target ),
				which = $btn.attr( 'data-action' );
			$( '.llms-section .llms-action-icon.' + which ).trigger( 'click' );

		},

		/**
		 * Creates a model (section or lesson)
		 * @param    string   type   model type [section or lesson]
		 * @param    obj      model  model object
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		create_item: function( type, model ) {

			var collection = 'section' === type ? Instance.Syllabus.collection : App.Methods.get_last_section().Lessons.collection;

			collection.create( model, {
				beforeSend: function() {
					Instance.Status.add( model.id );
				},
				success: function( res ) {
					Instance.Status.remove( model.id );
				},
			} );

			var $el = $( '#llms-' + type + '-' + model.id );
			$el.addClass( 'brand-new' );

			setTimeout( function() {
				$el.removeClass( 'brand-new' );
			}, 10 );

			// open section
			if ( 'lesson' === type ) {
				$el.closest( '.llms-section' ).addClass( 'opened' );
			}

			// scroll to bottom
			var $wrap = $( '#llms-course-syllabus' );
			$wrap.animate( {
				scrollTop: $wrap[0].scrollHeight - $wrap[0].clientHeight,
			}, 200 );

			App.Methods.sortable();

		},

		/**
		 * Disable the lesson tools when there's no section in the course
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		maybe_disable: function() {

			var $btns = $( '#llms-new-lesson, #llms-existing-lesson' );

			if ( ! Instance.Syllabus.collection.length ) {
				$btns.attr( 'disabled', 'disabled' );
			} else {
				$btns.removeAttr( 'disabled' );
			}

		},

		/**
		 * Show a popover to select an existing lesson
		 * @param    obj   e  js click event obj
		 * @return   void
		 * @since    3.14.4
		 * @version  [version]
		 */
		show_search_popover: function( e ) {

			WebuiPopovers.show( e.target, {
				animation: 'pop',
				backdrop: true,
				content: new Views.LessonSearchPopover().render().$el,
				closeable: true,
				dismissible: true,
				multi: false,
				placement: 'left',
				title: LLMS.l10n.translate( 'Add an Existing Lesson' ),
				trigger: 'manual',
				width: 480,
				onShow: function( $popover ) {
					$( '.webui-popover-backdrop' ).one( 'click', function() {
						WebuiPopovers.hide( e.target );
					} );
				},
			} );

		},

	} );

} );
