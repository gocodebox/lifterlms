/**
 * LifterLMS Admin Panel Javascript
 *
 * @since Unknown
 * @version 4.5.1
 *
 * @param obj $ Traditional jQuery reference.
 * @return void
 */
;( function( $ ) {

	window.llms = window.llms || {};

	window.llms.widgets = function() {

		this.$widgets      = $( '.llms-widget' );
		this.$info_toggles = $( '.llms-widget-info-toggle' );

		this.init = function() {
			this.bind();
		};

		this.bind = function() {

			this.$info_toggles.on( 'mouseenter mouseleave', function( evt ) {
				$(this).closest( '.llms-widget' )
					.toggleClass( 'info-showing', 'mouseenter' === evt.type );
			} );

		};

		// Go.
		this.init();

		return this;

	};

	var llms_widgets = new window.llms.widgets();

	/**
	 * Simple jQuery plugin to transform select elements into Select2-powered elements to query for Courses/Memberships via AJAX.
	 *
	 * @since 3.19.4
	 * @since 3.32.0 Added ability to fetch posts based on their post status.
	 * @since 3.37.2 Added ability to fetch posts (llms posts) filtered by their instructor id.
	 * @since 4.4.0 Update ajax nonce source.
	 *
	 * @param obj options Options passed to Select2.
	 *                    Each default option will pulled from the elements data-attributes.
	 * @return void
	 */
	$.fn.llmsPostsSelect2 = function( options ) {

		var self = this,
			options = options || {},
			defaults = {
				multiple: false,
				placeholder: undefined !== LLMS.l10n ? LLMS.l10n.translate( 'Select a Course/Membership' ) : 'Select a Course/Membership',
				post_type: self.attr( 'data-post-type' ) || 'post',
				post_statuses: self.attr( 'data-post-statuses' ) || 'publish',
				instructor_id: null,
				allow_clear: self.attr( 'data-post-type' ) || false,
				width: null,
			};

		$.each( defaults, function( setting ) {
			if ( self.attr( 'data-' + setting ) ) {
				options[ setting ] = self.attr( 'data-' + setting );
			}
		} );

		if ( 'multiple' === self.attr( 'multiple' ) ) {
			options.multiple = true;
		}

		options = $.extend( defaults, options );

		this.llmsSelect2( {
			allowClear: options.allow_clear,
			ajax: {
				dataType: 'JSON',
				delay: 250,
				method: 'POST',
				url: window.ajaxurl,
				data: function( params ) {
					return {
						action: 'select2_query_posts',
						page: ( params.page ) ? params.page - 1 : 0, // 0 index the pages to make it simpler for the database query
						post_type: options.post_type,
						instructor_id : options.instructor_id,
						post_statuses: options.post_statuses,
						term: params.term,
						_ajax_nonce: window.llms.ajax_nonce,
					};
				},
				processResults: function( data, params ) {

					// recursive function for creating
					function map_data( items ) {

						// this is a flat array of results
						// used when only one post type is selected
						// and to format children when using optgroups with multiple post types
						if ( Array.isArray( items ) ) {
							return $.map( items, function( item ) {
								return format_item( item );
							} );

							// this sets up the top level optgroups when using multiple post types
						} else {
							return $.map( items, function( item ) {
								return {
									text: item.label,
									children: map_data( item.items ),
								}
							} );
						}
					}

					// format a single result (option)
					function format_item( item ) {
						return {
							text: item.name,
							id: item.id,
						};
					}

					return {
						results: map_data( data.items ),
						pagination: {
							more: data.more
						}
					};

				},
			},
			cache: true,
			placeholder: options.placeholder,
			multiple: options.multiple,
			width: options.width,
		} );

	};

	// automatically setup any select with the `llms-posts-select2` class
	$( 'select.llms-posts-select2' ).llmsPostsSelect2();

	/**
	 * Simple jQuery plugin to transform select elements into Select2-powered elements to query for Students via AJAX
	 *
	 * @since Unknown
	 * @since 3.17.5 Unknown.
	 * @since 4.4.0 Update ajax nonce source.
	 *
	 * @param obj options Options passed to Select2. Each default option will be pulled from the elements data-attributes.
	 * @return void
	 */
	$.fn.llmsStudentsSelect2 = function( options ) {

		var self = this,
			options = options || {},
			defaults = {
				allow_clear: false,
				enrolled_in: '',
				multiple: false,
				not_enrolled_in: '',
				placeholder: undefined !== LLMS.l10n ? LLMS.l10n.translate( 'Select a student' ) : 'Select a student',
				roles: '',
				width: '100%',
			};

		$.each( defaults, function( setting ) {
			if ( self.attr( 'data-' + setting ) ) {
				options[ setting ] = self.attr( 'data-' + setting );
			}
		} );

		options = $.extend( defaults, options );

		this.llmsSelect2({
			allowClear: options.allow_clear,
			ajax: {
				dataType: 'JSON',
				delay: 250,
				method: 'POST',
				url: window.ajaxurl,
				data: function( params ) {
					return {
						_ajax_nonce: window.llms.ajax_nonce,
						action: 'query_students',
						page: params.page,
						not_enrolled_in: params.not_enrolled_in || options.not_enrolled_in,
						enrolled_in: params.enrolled_in || options.enrolled_in,
						roles: params.roles || options.roles,
						term: params.term,
					};
				},
				processResults: function( data, params ) {
					return {
						results: $.map( data.items, function( item ) {

							return {
								text: item.name + ' <' + item.email +'>',
								id: item.id,
							};

						} ),
						pagination: {
							more: data.more
						}
					};

				},
			},
			cache: true,
			placeholder: options.placeholder,
			multiple: options.multiple,
			width: options.width,
		});

		return this;

	};

	/**
	 * Scripts for use on the engagements settings tab for email provider connector plugins
	 *
	 * @since 3.40.0
	 */
	window.llms.emailConnectors = {

		/**
		 * Register a client
		 *
		 * Builds and submits a form used to direct the user to the connector's oAuth
		 * authorization endpoint.
		 *
		 * @since 3.40.0
		 *
		 * @param {String} url    Redirect URL.
		 * @param {Object} fields Hash of fields where the key is the field name and the value if the field value.
		 * @return {Void}
		 */
		registerClient: function( url, fields ) {

			var form = document.createElement( 'form' );
			form.setAttribute( 'method', 'POST' );
			form.setAttribute( 'action', url );

			function appendInput( name, value ) {
				var input = document.createElement( 'input' );
				input.setAttribute( 'type', 'hidden' );
				input.setAttribute( 'name', name );
				input.setAttribute( 'value', value );
				form.appendChild( input );
			}

			$.each( fields, function( key, val ) {
				appendInput( key, val );
			} );

			document.body.appendChild( form );
			form.submit();

		},

		/**
		 * Performs an AJAX request to perform remote installation of the connector plugin
		 *
		 * The callback will more than likely use `registerClient()` on success.
		 *
		 * @since 3.40.0
		 *
		 * @param {Object}   $btn     jQuery object for the connector button.
		 * @param {Object}   data     Hash of data used for the ajax request.
		 * @param {Function} callback Success callback function.
		 * @return {Void}
		 */
		remoteInstall: function( $btn, data, callback ) {

			$btn.parent().find( '.llms-error' ).remove();
			$.post( ajaxurl, data, callback ).fail( function( jqxhr ) {
				LLMS.Spinner.stop( $btn );
				var msg = jqxhr.responseJSON && jqxhr.responseJSON.message ? jqxhr.responseJSON.message : jqxhr.responseText;
				if ( msg ) {
					$( '<p class="llms-error">' + LLMS.l10n.replace( 'Error: %s', { '%s': msg } ) + '</p>' ).insertAfter( $btn );
				}
			} );

		}

	};

} )( jQuery );
