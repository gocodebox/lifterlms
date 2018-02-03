/**
 * Existing Lesson Popover content View
 * @since    3.13.0
 * @version  3.16.0
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
		 * @version  3.16.0
		 */
		add_lesson: function( event ) {

			Backbone.pubSub.trigger( 'lesson-search-select', event.params.data, event );

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
					dropdownParent: $( '.webui-popover-inner' ),
					// don't escape html from render_result
					escapeMarkup: function( markup ) {
						return markup;
					},
					placeholder: LLMS.l10n.translate( 'Search for existing lessons...' ),
					templateResult: self.render_result,
					width: '100%',
				} );
			}, 0 );
			return this;

		},

		render_result: function( res ) {

			var $html = $( '<div class="llms-existing-lesson-result" />' );

			if ( res.loading ) {
				return $html.append( res.text );
			}

			var $side = $( '<aside class="llms-existing-action" />' ),
				$main = $( '<div class="llms-existing-info" />' );
				icon = ( 'attach' === res.action ) ? 'paperclip' : 'clone',
				text = ( 'attach' === res.action ) ? 'Attach' : 'Clone';

			text = LLMS.l10n.translate( text );

			$side.append( '<i class="fa fa-' + icon + '" aria-hidden="true"></i><small>' + text + '</small>' );

			$main.append( '<h4>' + res.data.title + '</h4>' );
			$main.append( '<h5>' + LLMS.l10n.translate( 'ID' ) + ': <em>' + res.data.id + '</em></h5>' );
			if ( res.course_title ) {
				$main.append( '<h5>' + LLMS.l10n.translate( 'Course' ) + ': <em>' + res.course_title + '</em></h5>' );
			}

			return $html.append( $side ).append( $main );

		},

	} );

} );
