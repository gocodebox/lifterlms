/**
 * Main Builder Area View
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: $( '#llms-builder-main' ),

		initialize: function() {

			var self = this;

			Backbone.pubSub.on( 'rebind', this.bind_all, this );
			Backbone.pubSub.on( 'lock', this.loading_start, this );
			Backbone.pubSub.on( 'unlock', this.loading_stop, this );

			Backbone.pubSub.on( 'init-complete', function() {

				self.bind_all();
				// start the mini spinner that never stops
				LLMS.Spinner.start( $( '#llms-spinner-el' ), 'small' );

			}, this );


		},

		bind_all: function() {

			this.draggable();
			this.sortable();

		},

		/**
		 * Bind Draggable Events
		 * Powers draggable items in the tools sidebar
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		draggable: function() {
			$( '#llms-new-section' ).draggable( {
				cancel: false,
				connectToSortable: '.llms-sections',
				helper: function() {
					var section = new Models.Section( {
						id: _.uniqueId( 'section_temp_' ),
					} );
					return new Views.Section( { model: section } ).render().$el;
				},
				start: function() {
					$( '.llms-sections' ).addClass( 'dragging' );
				},
				stop: function() {
					$( '.llms-sections' ).removeClass( 'dragging' );
				},
			} );
			$( '#llms-new-lesson' ).draggable( {
				cancel: false,
				connectToSortable: '.llms-lessons',
				helper: function() {
					var lesson = new Models.Lesson( {
						id: _.uniqueId( 'lesson_temp_' ),
					} );
					return new Views.Lesson( { model: lesson } ).render().$el;
				},
				start: function() {
					$( '.llms-lessons' ).addClass( 'dragging' );
				},
				stop: function() {
					$( '.llms-lessons' ).removeClass( 'dragging' );
				},
			} );
		},

		loading_start: function() {

			this.$el.addClass( 'loading' );
			LLMS.Spinner.start( this.$el );

		},

		loading_stop: function() {

			this.$el.removeClass( 'loading' );
			LLMS.Spinner.stop( this.$el );

		},

		/**
		 * Bind jQuery UI Sortable events
		 * Powers draggable course elements in the syllabus area
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		sortable: function() {
			$( '.llms-sections' ).sortable( {
				cursor: 'move',
				cursorAt: {
					top: 10,
					left: 10,
				},
				handle: '.drag-section',
				items: '.llms-section',
				placeholder: 'llms-section llms-sortable-placeholder',
				tolerance: 'pointer',
				start: function( event, ui ) {
					ui.item.css( 'height', 'auto' );
					$( '.llms-sections' ).addClass( 'dragging' );
				},
				stop: function( event, ui ) {
					ui.item.trigger( 'drop-section', ui.item.index() );
					$( '.llms-sections' ).removeClass( 'dragging' );
				},
			} );

			$( '.llms-lessons' ).sortable( {
				cursor: 'move',
				cursorAt: {
					top: 10,
					left: 10,
				},
				connectWith: '.llms-lessons',
				handle: '.drag-lesson',
				items: '.llms-lesson',
				placeholder: 'llms-lesson llms-sortable-placeholder',
				tolerance: 'pointer',
				start: function( event, ui ) {
					$( '.llms-lessons' ).addClass( 'dragging' );
				},
				stop: function( event, ui ) {

					event.stopPropagation();

					var from_section = ui.item.attr( 'data-section-id' ),
						curr_section = ui.item.closest( '.llms-section' ).attr( 'data-id' );

					ui.item.trigger( 'drop-lesson', [ ui.item, curr_section, from_section ] );

					ui.item.removeAttr( 'style' )
						.closest( '.llms-section' ).addClass( 'opened' );

					$( '.llms-lessons' ).removeClass( 'dragging' );

				},
				receive: function( event, ui ) {
					ui.item.trigger( 'update-parent', ui.item );
					ui.item.removeAttr( 'style' )
						.closest( '.llms-section' ).addClass( 'opened' );
				},
				over: function( event, ui ) {
					$( '#' + event.target.offsetParent.id ).addClass( 'hover-opened' );
				},
				out: function( event, ui ) {
					$( '#' + event.target.offsetParent.id ).removeClass( 'hover-opened' );
				}
			} );
		}

	} );

} );
