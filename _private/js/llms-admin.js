( function( $ ) {

	window.llms = window.llms || {};

	$.fn.llmsStudentsSelect2 = function( options ) {

		var defaults = {
			multiple: false,
			placeholder: 'Select a product',
			width: '100%',
		};

		options = $.extend( defaults, options );

		this.select2({
			allowClear: false,
			ajax: {
				dataType: 'JSON',
				delay: 250,
				method: 'POST',
				url: window.ajaxurl,
				data: function( params ) {
					return {
						term: params.term,
						page: params.page,
						action: 'query_students',
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
