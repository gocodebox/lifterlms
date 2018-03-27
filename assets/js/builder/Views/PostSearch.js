/**
 * Post Popover Serach content View
 * @since    3.16.0
 * @version  3.17.0
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		events: {
			'select2:select': 'add_post',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'select',

		/**
		 * Initializer
		 * @param    obj   data  customize the search box with data
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		initialize: function( data ) {

			this.post_type = data.post_type;
			this.searching_message = data.searching_message || LLMS.l10n.translate( 'Searching...' );

		},

		/**
		 * Select event, adds the existing lesson to the course
		 * @param    obj   event  select2:select event object
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		add_post: function( event ) {

			var type = this.$el.attr( 'data-post-type' );

			Backbone.pubSub.trigger( type.replace( 'llms_', '' ) + '-search-select', event.params.data, event );
			this.$el.val( null ).trigger( 'change' );

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.12
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
								post_type: self.post_type,
								term: params.term,
								page: params.page,
								_ajax_nonce: wp_ajax_data.nonce,
							};
						},
						// error: function( xhr, status, error ) {
						// 	console.log( status, error );
						// },
					},
					dropdownParent: $( '.wrap.lifterlms.llms-builder' ),
					// don't escape html from render_result
					escapeMarkup: function( markup ) {
						return markup;
					},
					placeholder: self.searching_message,
					templateResult: self.render_result,
					width: '100%',
				} );
				self.$el.attr( 'data-post-type', self.post_type );
			}, 0 );
			return this;

		},

		/**
		 * Render a nicer UI for each search result in the in the Select2 search results
		 * @param    object   res  result data
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		render_result: function( res ) {

			var $html = $( '<div class="llms-existing-lesson-result" />' );

			if ( res.loading ) {
				return $html.append( res.text );
			}

			var $side = $( '<aside class="llms-existing-action" />' ),
				$main = $( '<div class="llms-existing-info" />' );
				icon = ( 'attach' === res.action ) ? 'paperclip' : 'clone',
				text = ( 'attach' === res.action ) ? LLMS.l10n.translate( 'Attach' ) : LLMS.l10n.translate( 'Clone' );

			$side.append( '<i class="fa fa-' + icon + '" aria-hidden="true"></i><small>' + text + '</small>' );

			$main.append( '<h4>' + res.data.title + '</h4>' );
			$main.append( '<h5>' + LLMS.l10n.translate( 'ID' ) + ': <em>' + res.data.id + '</em></h5>' );

			_.each( res.parents, function( parent ) {
				$main.append( '<h5>' + parent + '</em></h5>' );
			} );

			return $html.append( $side ).append( $main );

		},

	} );

} );
