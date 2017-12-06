/**
 * Existing Lesson Popover content View
 * @since    3.13.0
 * @version  3.14.0
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		events: {
			'select2:select': 'add_lesson',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'select',

		/**
		 * Select event, adds the existing lesson to the course
		 * @param    obj   event  select2:select event object
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		add_lesson: function( event ) {

			WebuiPopovers.hide( $( '#llms-existing-lesson' ) );

			Instance.Tools.create_item( 'lesson', {
				id: event.params.data.id,
				title: event.params.data.title
			} );

			this.$el.val( null ).trigger( 'change' );

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		render: function() {
			var self = this;
			setTimeout( function () {
				self.$el.llmsSelect2( {
					ajax: {
						dataType: 'JSON',
						delay: 250,
						method: 'POST',
						url: window.ajaxurl,
						data: function( params ) {
							return {
								action: 'llms_builder',
								action_type: 'search',
								course_id: window.llms_builder.course.id,
								term: params.term,
								page: params.page,
								_ajax_nonce: wp_ajax_data.nonce,
							};
						},
						// error: function( xhr, status, error ) {
						// 	console.log( status, error );
						// },
					},
					placeholder: LLMS.l10n.translate( 'Search for existing lessons...' ),
					dropdownParent: $( '.webui-popover-inner' ),
					width: '100%',
				} );
			}, 0 );
			return this;

		},

	} );

} );
