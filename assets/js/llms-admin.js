/**
 * LifterLMS Admin Panel Javascript
 *
 * @since    ??
 * @since    3.32.0 `llmsPostsSelect2` function allows posts fecthing based on post statuses.
 * @version  3.32.0
 *
 * @param    obj   $  traditional jQuery reference
 * @return   void
 */
;( function( $ ) {

	window.llms = window.llms || {};

	window.llms.widgets = function() {

		this.$widgets = $( '.llms-widget' );
		this.$info_toggles = $( '.llms-widget-info-toggle' );

		this.init = function() {

			this.bind();

		};

		this.bind = function() {

			var self = this;

			this.$info_toggles.on( 'hover', function() {

				var $toggle = $( this ),
					$widget = $toggle.closest( '.llms-widget' ),
					$info = $widget.find( '.llms-widget-info' ),
					action = ( $widget.hasClass( 'info-showing' ) ) ? 'hide' : 'show';

				self.$widgets.removeClass( 'info-showing' );

				if ( 'show' === action ) {

					$widget.addClass( 'info-showing' );

				}

			} );

		}

		// go
		this.init();

		return this;

	};

	var llms_widgets = new window.llms.widgets();

	/**
	 * Simple jQuery plugin to transform select elements into Select2-powered elements to query for Courses/Memberships via AJAX.
	 *
	 * @since 3.19.4
	 * @since 3.32.0 Added ability to fetch posts based on their post status.
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
						post_statuses: options.post_statuses,
						term: params.term,
						_ajax_nonce: wp_ajax_data.nonce
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
	 * @param    obj   options  options passed to Select2
	 *                          each default option will pulled from the elements data-attributes
	 * @return   void
	 * @since    ??
	 * @version  3.17.5
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
						_ajax_nonce: wp_ajax_data.nonce,
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

} )( jQuery );
