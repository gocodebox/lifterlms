;
( function( $ ) {
	/**
	 * Simple jQuery plugin to transform select elements into Select2-powered elements to query for Courses/Memberships via AJAX
	 * @param    obj   options  options passed to Select2
	 *                          each default option will pulled from the elements data-attributes
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */

	$.fn.llms_product_select = function( options ) {

		var self = this,
			options = options || {},
			defaults = {
				multiple: false,
				placeholder: undefined !== LLMS.l10n ? LLMS.l10n.translate( 'Select a Course/Membership' ) : 'Select a Course/Membership',
				post_type: self.attr( 'data-post-type' ) || 'post',
				allow_clear: self.attr( 'data-post-type' ) || false,
			};

		$.each( defaults, function( setting ) {
			if ( self.attr( 'data-' + setting ) ) {
				options[ setting ] = self.attr( 'data-' + setting );
			}
		} );

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
		} );
	};

	$( '.llms-bulk-enroll-product' ).llms_product_select();

} )( jQuery );
