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

	/**
	 * Delete a quiz attempt from student reporting screen
	 * @return   void
	 * @since    3.4.4
	 * @version  3.4.4
	 */
	$( 'a[href="#llms-delete-quiz-attempt"]' ).on( 'click', function( e ) {

		e.stopPropagation();
		e.preventDefault();

		if ( ! window.confirm( LLMS.l10n.translate( 'delete_quiz_attempt' ) ) ) {
			return;
		}

		var $this = $( this ),
			$wrap = $this.closest( '.llms-quiz-attempt' );

		LLMS.Ajax.call( {
			data: {
				action: 'delete_quiz_attempt',
				attempt: $this.attr( 'data-attempt' ),
				lesson: $this.attr( 'data-lesson' ),
				quiz: $this.attr( 'data-quiz' ),
				user: $this.attr( 'data-user' ),
			},
			beforeSend: function() {
				LLMS.Spinner.start( $wrap, 'small' );
			},
			success: function( r ) {

				// show error
				if ( r.code ) {

					alert( r.message );

				// success, reload
				} else if ( r.success ) {

					window.location.reload();

				// unknown error...
				} else {

					alert( LLMS.l10n.translate( 'An unknown error occurred, please try again.' ) );
				}

			}
		} );

	} );

} )( jQuery );
