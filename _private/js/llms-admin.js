( function( $ ) {

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


	$.fn.llmsStudentsSelect2 = function( options ) {

		var defaults = {
			multiple: false,
			placeholder: 'Select a student',
			width: '100%',
		};

		options = $.extend( defaults, options );

		this.llmsSelect2({
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
						_ajax_nonce: wp_ajax_data.nonce,
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
