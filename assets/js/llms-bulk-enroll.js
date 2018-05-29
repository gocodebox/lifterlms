$.fn.llms_product_select = function( $el ) {

	var post_type = $el.attr( 'data-post-type' ) || post,
		allow_clear = $el.attr( 'data-post-type' ) || false;

	$el.llmsSelect2( {
		allowClear: allow_clear,
		ajax: {
			dataType: 'JSON',
			delay: 250,
			method: 'POST',
			url: window.ajaxurl,
			data: function( params ) {
				return {
					action: 'select2_query_posts',
					page: ( params.page ) ? params.page - 1 : 0, // 0 index the pages to make it simpler for the database query
					post_type: post_type,
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
		width: '80%',
	} );
};

(function($){

	$( '.llms-bulk-enroll-product' ).llms_product_select();

});
