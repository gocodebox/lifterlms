/**
 * Product Options MetaBox
 * Displays on Course & Membership Post Types
 *
 * @since    [version]
 * @version  [version]
 */
( function( $ ) {

	window.llms = window.llms || {};

	window.llms.builder = function() {

		this.$wrapper = null;

		/**
		 * Initialize
		 *
		 * @return  void
		 * @since   [version]
		 * @version [version]
		 */
		this.init = function() {

			var self = this;

			this.$wrapper = $( '.llms-course-builder.wrap' );
			this.save_status = null;
			this.$save_status = $( '#save-status' );

			// LLMS.Spinner.start( $( '#llms-spinner-el' ), 'small' );

			setTimeout( function() {
				wp.heartbeat.connectNow();
			}, 5000 );

			console.log( window.llms_course );

			add_sections( window.llms_course.sections );

			this.bind();

		};

		function add_lesson( section_id, lesson, order ) {

			var order = ! order ? lesson.order  : order,
				template = wp.template( 'llms-lesson-template' ),
				$html = template( lesson ),
				$section = $( '#llms-section-' + section_id ),
				$lessons = $section.find( '.llms-lessons' );

			if ( ! $lessons.find( '.llms-lesson' ).length ) {
				$lessons.append( $html );
			} else {
				$lessons.find( '.llms-lesson:nth-child(' + ( order - 1 ) + ')' ).after( $html );
			}

		}

		function add_lessons( section_id, lessons ) {
			$.each( lessons, function( i, lesson ) {
				add_lesson( section_id, lesson );
			} );
		};

		function add_section( section, order, include_lessons ) {

			var include_lessons = undefined === include_lessons ? true : include_lessons,
				order = ! order ? section.order  : order,
				template = wp.template( 'llms-section-template' ),
				$el = $( template( section ) ),
				$sections = $( '#llms-sections' )
				$section = $( '#llms-section-' + section.id );


			// section already exists ??
			// if ( $section ) {}

			if ( ! $sections.find( '.llms-section' ).length ) {
				$sections.append( $el );
			} else {
				$sections.find( '.llms-section:nth-child(' + ( order - 1 ) + ')' ).after( $el );
			}

			if ( include_lessons && section.lessons ) {
				add_lessons( section.id, section.lessons );
				$el.attr( 'data-loaded', 'yes' );
			}

		};

		function add_sections( sections ) {

			$.each( sections, function( i, section ) {

				// more sections but don't load them
				if ( ! section.title ) {
					$( '#llms-builder-load-sections' ).show();
					return false;
				} else {
					add_section( section );
				}

			} );

		};

		/**
		 * Bind DOM Events
		 *
		 * @return void
		 * @since   [version]
		 * @version [version]
		 */
		this.bind = function() {

			var self = this;

			self.sortable();

			$( '#llms-builder-load-sections' ).on( 'click', function( e ) {
				e.preventDefault();
				self.make_request( 'load_sections', { course: self.get_unloaded_sections() }, function( ret ) {
					console.log( ret );
				} );
			} );

			self.$wrapper.on( 'click', 'a[href="#llms-toggle"]', function( e ) {
				e.preventDefault();
				self.toggle_item( $( this ).closest( '.llms-builder-item' ) );
			} );

			self.$wrapper.on( 'click', 'a[href="#llms-toggle-all"]', function( e ) {
				e.preventDefault();

				var func = 'open' === $( this ).attr( 'data-action' ) ? 'addClass' : 'removeClass';

				$( '.llms-builder-item' )[ func ]( 'opened' );

			} );

			self.$wrapper.on( 'dblclick', '[data-llms-editable]', function( e ) {
				e.preventDefault();
				self.make_editable( $( this ) );
			} );

			self.$wrapper.on( 'focusout blur', 'input.llms-inline-edit', function( e ) {
				e.preventDefault();
				self.save_edits( $( this ) );
			} );

			self.$wrapper.on( 'keypress', 'input.llms-inline-edit', function( e ) {

				var $input = $( this );

				switch ( e.keyCode ) {
					case 13 :
						$input.trigger( 'blur' );
					break;
				}

			} );

		};


		this.get_unloaded_sections = function() {

			var sections = window.llms_course.sections;

			for ( var section in sections ) {
				console.log( sections[section] );
				if ( sections[ section ].lessons ) {
					delete sections[ section ];
				}

			}

			console.log( sections );

			return sections;

		};


		this.make_editable = function( $el ) {

			var val = $el.text(),
				field = $el.attr( 'data-llms-editable' ),
				type = ( 'title' === field ) ? 'text' : 'text',
				$html;

			$el.addClass( 'active' );

			if ( 'text' === type ) {
				$html = $( '<input class="llms-inline-edit" data-original="' + val + '" type="text" value="' + val + '">' );
			}

			$el.html( $html );

			$html.focus();
			$html[0].setSelectionRange( val.length, val.length );

		};

		this.save_edits = function( $input ) {

			var self = this,
				$el = $input.closest( '.llms-inline-edit-wrap' ),
				data = {
					id: $el.closest( '[data-id]' ).attr( 'data-id' ),
					field: $el.attr( 'data-llms-editable' ),
					value: $input.val(),
				};

			// no changes were made
			if ( data.value === $input.attr( 'data-original' ) ) {

				$el.text( data.value ).removeClass( 'active' );

			} else {

				self.make_request( 'save_edits', data, function( r ) {

					if ( r.success && r.data.value ) {
						$el.text( r.data.value ).removeClass( 'active' );
					}

				} );

			}

		};

		this.sortable = function() {

			var self = this;

			$( '.llms-sections' ).sortable( {
				handle: '.drag-section',
				items: '.llms-section',
				placeholder: 'llms-section llms-sortable-placeholder',
				start: function( event, ui ) {
					$( '.llms-sections' ).addClass( 'dragging' );
				},
				stop: function( event, ui ) {
					$( '.llms-sections' ).removeClass( 'dragging' );
				},
			} );

			$( '.llms-lessons' ).sortable( {
				connectWith: '.llms-lessons',
				handle: '.drag-lesson',
				items: '.llms-lesson',
				placeholder: 'llms-lesson llms-sortable-placeholder',
				start: function( event, ui ) {
					$( '.llms-lessons' ).addClass( 'dragging' );
				},
				stop: function( event, ui ) {
					ui.item.removeAttr( 'style' )
						.closest( '.llms-section' ).addClass( 'opened' );
					$( '.llms-lessons' ).removeClass( 'dragging' );
				},
				over: function( event, ui ) {
					$( '#' + event.target.offsetParent.id ).addClass( 'hover-opened' );
					// self.toggle_item( ui.placeholder.closest( '.llms-lessons' ) );
				},
				out: function( event, ui ) {
					$( '#' + event.target.offsetParent.id ).removeClass( 'hover-opened' );
				}
			} );

		}

		this.make_request = function( method, data, cb, $wrapper ) {

			var self = this;

			$wrapper = $wrapper || self.$wrapper;

			data = $.extend( {
				action: 'llms_builder',
				method: method,
			}, data );

			LLMS.Ajax.call( {
				data: data,
				beforeSend: function() {

					$wrapper.addClass( 'processing' );
					LLMS.Spinner.start( $wrapper );

				},
				success: function( r ) {

					console.log( r );
					LLMS.Spinner.stop( $wrapper );
					$wrapper.removeClass( 'processing' );

					cb( r )

				}

			} );
		}

		this.toggle_item = function( $item ) {

			var self = this,
				action = $item.hasClass( 'opened' ) ? 'close' : 'open';

			if ( 'close' === action ) {
				$item.removeClass( 'opened' );
			} else {

				$item.addClass( 'opened' );

				if ( 'no' === $item.attr( 'data-loaded' ) ) {

					var data = {
						id: $item.attr( 'data-id' ),
					};

					self.make_request( 'load_item', data, function( r ) {

						if ( r.success && r.data.item && r.data.item_type ) {

							switch ( r.data.item_type ) {

								case 'section' :

									add_lessons( r.data.item.id, r.data.item.lessons );
									$( '#llms-section-' + r.data.item.id ).attr( 'data-loaded', 'yes' );
									self.sortable();

								break;

							}

						}

					}, $item );

				}

			}

		};

		// go
		this.init();

	};

	var a = new window.llms.builder();

} )( jQuery );
