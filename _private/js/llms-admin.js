/**
 * LifterLMS Admin Panel Javascript
 * @param    obj   $  traditional jQuery reference
 * @return   void
 * @since    ??
 * @version  3.13.0
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
	 * Simple jQuery plugin to transform select elements into Select2-powered elements to query for Students via AJAX
	 * @param    obj   options  options passed to Select2
	 *                          each default option will pulled from the elements data-attributes
	 * @return   void
	 * @since    ??
	 * @version  3.13.0
	 */
	$.fn.llmsStudentsSelect2 = function( options ) {

		var self = this,
			options = options || {},
			defaults = {
				allow_clear: false,
				enrolled_in: '',
				multiple: false,
				not_enrolled_in: '',
				placeholder: 'Select a student',
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
