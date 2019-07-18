/**
 * LifterLMS Admin Panel Metabox Functions
 *
 * @since 3.0.0
 * @since 3.30.0 Made autoenroll table sortable, added AJAX save for adding new courses.
 * @version 3.30.0
 */
( function( $ ) {

	/**
	 * jQuery plugin to allow "collapsible" sections
	 *
	 * @return  jQuery object
	 * @since   3.0.0
	 * @version 3.29.0
	 */
	$.fn.llmsCollapsible = function() {

		var $group = this;

		this.on( 'click', '.llms-collapsible-header', function() {

			var $parent = $( this ).closest( '.llms-collapsible' ),
				$siblings = $parent.siblings( '.llms-collapsible' );

			$parent.toggleClass( 'opened' ).trigger( 'llms-collapsible-toggled' );

			$parent.find( '.llms-collapsible-body' ).slideToggle( 400 );

			$siblings.each( function() {
				$( this ).removeClass( 'opened' );
				$( this ).find( '.llms-collapsible-body' ).slideUp( 400 );
			} );

		} );

		return this;

	};

	window.llms = window.llms || {};

	var Metaboxes = function() {

		/**
		 * load all partials
		 */
		//= include ../partials/*.js

		/**
		 * Initialize
		 * @return   void
		 * @since    3.0.0
		 * @version  3.13.0
		 */
		this.init = function() {

			var self = this;

			$( '.llms-select2-post' ).each( function() {
				self.post_select( $( this ) );
			} );

			$( '.llms-collapsible-group' ).llmsCollapsible();

			this.bind_tabs();

			// bind everything better and less repetitively...
			var bindings = [
				{
					selector: $( '.llms-datepicker' ),
					func: 'bind_datepickers',
				},
				{
					selector: $( '.llms-select2' ),
					func: function( $selector ) {
						$selector.llmsSelect2( {
							width: '100%',
						} );
					},
				},
				{
					selector: $( '.llms-select2-student' ),
					func: function( $selector ) {
						$selector.llmsStudentsSelect2();
					}
				},
				{
					selector: $( 'input[type="checkbox"][data-controls]' ),
					func: 'bind_cb_controllers',
				},
				{
					selector: $( '[data-is-controller]' ),
					func: 'bind_controllers',
				},
				{
					selector: $( '.llms-table' ),
					func: 'bind_tables',
				},
				{
					selector: $( '.llms-merge-code-wrapper' ),
					func: 'bind_merge_code_buttons',
				},
				{
					selector: $( 'a.llms-editable' ),
					func: 'bind_editables',
				},
			];

			// bind all the bindables but don't bind things in repeaters
			$.each( bindings, function( index, obj ) {

				if ( obj.selector.length ) {

					// reduce the selector to exclude items in a repeater
					var reduced = obj.selector.filter( function() {
						return ( 0 === $( this ).closest( '.llms-repeater-model' ).length );
					} );

					// bind by string
					if ( 'string' === typeof obj.func ) {
						self[ obj.func ]( reduced );
					}
					// bind by an anonymous function
					else if ( 'function' === typeof obj.func ) {
						obj.func( reduced );
					}

				}

			} );

			// if a post type is set & a bind exists for it, bind it
			if ( window.llms.post.post_type ) {

				var func = 'bind_' + window.llms.post.post_type;

				if ( 'function' === typeof this[func] ) {

					this[func]();

				}

			}

		};

		/**
		 * Bind checkboxes that control the display of other elements
		 * @param    obj   $controllers  jQuery selector for checkboxes to be bound as checkbox controllers
		 * @return   void
		 * @since    3.0.0
		 * @version  3.11.0
		 */
		this.bind_cb_controllers = function( $controllers ) {

			$controllers = $controllers || $( 'input[type="checkbox"][data-controls]' );

			$controllers.each( function() {

				var $cb = $( this ),
					$controlled = $( $cb.attr( 'data-controls' ) ).closest( '.llms-mb-list' );

				$cb.on( 'change', function() {

					if ( $( this ).is( ':checked' ) ) {

						$controlled.slideDown( 200 );

					} else {

						$controlled.slideUp( 200 );

					}

				} );

				$cb.trigger( 'change' );

			} );

		};

		/**
		 * Bind elements that control the display of other elements
		 * @param    obj   $controllers  jQuery selector for elements to be bound as checkbox controllers
		 * @return   void
		 * @since    3.0.0
		 * @version  3.11.0
		 */
		this.bind_controllers = function( $controllers ) {

			$controllers = $controllers || $( '[data-is-controller]' );

			$controllers.each( function() {

				var $el = $( this ),
					$controlled = $( '[data-controller="#' + $el.attr( 'id' ) + '"]' ),
					val;

				$el.on( 'change', function() {

					if ( 'checkbox' === $el.attr( 'type' ) ) {

						val = $el.is( ':checked' ) ? $el.val() : 'false';

					} else {

						val = $el.val();

					}

					$controlled.each( function() {

						var possible = $( this ).attr( 'data-controller-value' ),
							vals = [];

						if ( -1 !== possible.indexOf( ',' ) ) {

							vals = possible.split( ',' );

						} else {

							vals.push( possible );

						}

						if ( -1 !== vals.indexOf( val ) ) {

							$( this ).slideDown( 200 );

						} else {

							$( this ).slideUp( 200 );

						}

					} );


				} );

				$el.trigger( 'change' );

			} );

		};

		/**
		 * Bind a single datepicker element
		 * @param    obj   $el  jQuery selector for the input to bind the datepicker to
		 * @return   void
		 * @since    3.0.0
		 * @version  3.10.0
		 */
		this.bind_datepicker = function( $el ) {
			var format = $el.attr( 'data-format' ) || 'mm/dd/yy',
				maxDate = $el.attr( 'data-max-date' ) || null,
				minDate = $el.attr( 'data-min-date' ) || null;
			$el.datepicker( {
				dateFormat: format,
				maxDate: maxDate,
				minDate: minDate,
			} );
		}

		/**
		 * Bind all LifterLMS datepickers
		 * @param    obj   $datepickers  jQuery selector for the elements to bind
		 * @return   void
		 * @since    3.0.0
		 * @version  3.11.0
		 */
		this.bind_datepickers = function( $datepickers ) {

			var self = this;

			$datepickers = $datepickers || $('.llms-datepicker');

			$datepickers.each( function() {
				self.bind_datepicker( $( this ) );
			} );

		};

		/**
		 * Bind llms-editable metabox fields and related dom interactions
		 * @return   void
		 * @since    3.10.0
		 * @version  3.28.0
		 */
		this.bind_editables = function() {

			var self = this;

			function make_editable( $field ) {

				var $label = $field.find( 'label' ).clone(),
					name = $field.attr( 'data-llms-editable' ),
					type = $field.attr( 'data-llms-editable-type' ),
					required = $field.attr( 'data-llms-editable-required' ) || 'no',
					val = $field.attr( 'data-llms-editable-value' ),
					$input;

				required = ( 'yes' === required ) ? ' required="required"' : '';

				if ( 'select' === type ) {

					var options = JSON.parse( $field.attr( 'data-llms-editable-options' ) ),
						selected;

					$input = $( '<select name="' + name + '"' + required + ' />');
					for ( var key in options ) {
						selected = val === key ? ' selected="selected"' : '';
						$input.append( '<option value="' + key + '"' + selected + '>' + options[ key ] + '</option>' );
					}


				} else if ( 'datetime' === type ) {

					$input = $( '<div class="llms-datetime-field" />' );

					val = JSON.parse( val );
					var format = $field.attr( 'data-llms-editable-date-format' ) || '',
						min_date = $field.attr( 'data-llms-editable-date-min' ) || '',
						max_date = $field.attr( 'data-llms-editable-date-max' ) || '';

					$picker = $( '<input class="llms-date-input llms-datepicker" data-format="' + format + '" data-max-date="' + max_date + '" data-min-date="' + min_date + '" name="' + name + '[date]" type="text" value="' +  val.date + '">' );
					self.bind_datepicker( $picker );
					$input.append( $picker );
					$input.append( '<em>@</em>');

					$input.append( '<input class="llms-time-input" max="23" min="0" name="' + name + '[hour]" type="number" value="' +  val.hour + '">' );
					$input.append( '<em>:</em>');
					$input.append( '<input class="llms-time-input" max="59" min="0" name="' + name + '[minute]" type="number" value="' +  val.minute + '">' );

				} else {

					$input = $( '<input name="' + name + '" type="' + type + '" value="' + val + '"' + required + '>');
				}

				$field.empty().append( $label ).append( $input );
				if ( 'select' === type ) {
					setTimeout( function() {
						$input.trigger( 'change' );
					}, 100 );
				}

			};

			$( 'a.llms-editable' ).on( 'click', function( e ) {

				e.preventDefault();

				var $btn = $( this ),
					$fields;

				if ( $btn.attr( 'data-fields' ) ) {
					$fields = $( $btn.attr( 'data-fields' ) );
				} else {
					$fields = $btn.closest( '.llms-metabox-section' ).find( '[data-llms-editable]' );
				}

				$btn.remove();

				$fields.each( function() {
					make_editable( $( this ) );
				} );

			} );

		};

		/**
		 * Bind Engagement post type JS
		 * @return   void
		 * @since    3.1.0
		 * @version  3.1.0
		 */
		this.bind_llms_engagement = function() {

			var self = this;

			// when the engagement type changes we need to do some things to the UI
			$( '#_llms_engagement_type' ).on( 'change', function() {

				$( '#_llms_engagement' ).trigger( 'llms-engagement-type-change', $( this ).val() );

			} );

			// custom trigger when called when the engagement type changes
			$( '#_llms_engagement' ).on( 'llms-engagement-type-change', function( e, engagement_type ) {

				var $select = $( this );

				switch ( engagement_type ) {

					/**
					 * core engagements related to a CPT
					 */
					case 'achievement':
					case 'certificate':
					case 'email':

						var cpt = 'llms_' + engagement_type;

						$select.val( null ).attr( 'data-post-type', cpt ).trigger( 'change' );
						self.post_select( $select );

					break;

					/**
					 * Allow other plugins and developers to hook into the engagement type change action
					 */
					default:

						$select.trigger( 'llms-engagement-type-change-external', engagement_type );

				}

			} );

		};

		/**
		 * Actions for memberships
		 *
		 * @since 3.0.0
		 * @since 3.30.0 Made autoenroll table sortable, added AJAX save for adding new courses.
		 * @version 3.30.0
		 *
		 * @return   void
		 */
		this.bind_llms_membership = function() {

			var $table = $( '.llms-mb-list._llms_content_table' );

			/**
			 * Hide/Show empty message header row depending on the number of rows in the tbody
			 *
			 * @since 3.30.0
			 * @version 3.30.0
			 *
			 * @return void
			 */
			function toggle_header_row() {

				var $rows = $table.find( 'tbody tr' );
				if ( 1 === $rows.length ) {
					$rows.first().show();
				} else {
					$rows.first().hide();
				}
			}

			/**
			 * Retrieve an array of course IDs in the table.
			 *
			 * @since 3.30.0
			 * @version 3.30.0
			 *
			 * @return array
			 */
			function get_course_ids() {

				var courses = [];
				$table.find( 'tbody tr a[href="#llms-course-remove"]' ).each( function() {
					courses.push( $( this ).attr( 'data-id' ) );
				} );
				return courses;

			}

			// On init, toggle the header row visibility.
			toggle_header_row();

			// remove auto-enroll course
			$table.on( 'click', 'a[href="#llms-course-remove"]', function( e ) {

				e.preventDefault();

				var $el = $( this ),
					$row = $el.closest( 'tr' ),
					$container = $el.closest( '.llms-mb-list' );

				LLMS.Spinner.start( $container );

				window.LLMS.Ajax.call( {
					data: {
						action: 'membership_remove_auto_enroll_course',
						course_id: $el.attr( 'data-id' ),
					},
					beforeSend: function() {

						$container.find( 'p.error' ).remove();

					},
					success: function( r ) {

						if ( r.success ) {

							$row.fadeOut( 200 );
							setTimeout( function() {
								$row.remove();
								toggle_header_row();
							}, 400 );

						} else {

							$container.prepend( '<p class="error">' + r.message + '</p>' );

						}

						LLMS.Spinner.stop( $container );
					},
				} );

			} );

			// bulk enroll all members into a course
			$table.on( 'click', 'a[href="#llms-course-bulk-enroll"]', function( e ) {

				e.preventDefault();

				var $el = $( this ),
					$row = $el.closest( 'tr' ),
					$container = $el.closest( '.llms-mb-list' );

				if ( ! window.confirm( LLMS.l10n.translate( 'Click okay to enroll all active members into the selected course. Enrollment will take place in the background and you may leave your site after confirmation. This action cannot be undone!' ) ) ) {
					return;
				}

				LLMS.Spinner.start( $container );

				window.LLMS.Ajax.call( {
					data: {
						action: 'bulk_enroll_membership_into_course',
						course_id: $el.attr( 'data-id' ),
					},
					beforeSend: function() {
						$container.find( 'p.error' ).remove();
					},
					success: function( r ) {

						if ( r.success ) {

							$el.replaceWith( '<strong style="float:right;">' + r.data.message + '&nbsp;&nbsp;</strong>' );

						} else {

							$container.prepend( '<p class="error">' + r.message + '</p>' );

						}

						LLMS.Spinner.stop( $container );
					},
				} );

			} );

			// Add an item to the autoenroll table on select.
			$( '#_llms_auto_enroll' ).on( 'change', function() {

				var id = $( this ).val(),
					title = $( this ).find( 'option[value="' + $( this ).val() + '"]').text();

				// If there's no ID
				if ( ! id ) {
					return;
				// Prevent Dupes.
				} else if ( -1 !== get_course_ids().indexOf( id ) ) {

					alert( LLMS.l10n.replace( '"%s" is already in the course list.', { '%s': title } ) )

					// reset the select field.
					$( this ).val( '' ).trigger( 'change' );

					return;

				}

				var $table = $( '.llms-mb-list._llms_content_table' );
					$tr = $( '<tr />' );

				$tr.append( '<td><span class="llms-drag-handle" style="color:#999;"><i class="fa fa-ellipsis-v" aria-hidden="true" style="margin-right:2px;"></i><i class="fa fa-ellipsis-v" aria-hidden="true"></i></span></td>' );
				$tr.append( '<td><a href="' + window.llms.admin_url + 'post.php?action=edit&post=' + id + '">' + title + '</a></td>' );
				$tr.append( '<td><a class="llms-button-danger small" data-id="' + id + '" href="#llms-course-remove" style="float:right;">' + LLMS.l10n.translate( 'Remove course' ) + '</a><a class="llms-button-secondary small" data-id="' + id + '" href="#llms-course-bulk-enroll" style="float:right;">' + LLMS.l10n.translate( 'Enroll All Members' ) + '</a></td>' );

				// append the element to the table.
				$table.find('table tbody' ).append( $tr );

				// reset the select field.
				$( this ).val( '' ).trigger( 'change' );

				// Show the header row.
				toggle_header_row();

				// trigger a save event.
				$table.trigger( 'llms-save-autoenroll-courses' );

			} );

			// Make autoenrollment table sortable.
			$table.find( 'table tbody' ).sortable( {
				handle: '.llms-drag-handle',
				// Save order on stop.
				stop: function( event, ui ) {
					ui.item.closest( '.llms-mb-list' ).trigger( 'llms-save-autoenroll-courses' );
				},
			} );

			// Save courses & course order.
			$table.on( 'llms-save-autoenroll-courses', function() {

				var $container = $( this );

				LLMS.Spinner.start( $container );

				window.LLMS.Ajax.call( {
					data: {
						action: 'llms_save_membership_autoenroll_courses',
						courses: get_course_ids(),
					},
					error: function( jqxhr, code, error_msg ) {
						alert( error_msg );
					},
					complete: function() {
						LLMS.Spinner.stop( $container );
					},
				} );

			} );

		};

		/**
		 * Actions for ORDERS
		 * @return   void
		 * @since    3.0.0
		 * @version  3.28.0
		 */
		this.bind_llms_order = function() {

			$( 'button[name="llms-refund-toggle"]' ).on( 'click', function() {

				var $btn = $( this ),
					$row = $btn.closest( 'tr' ),
					txn_id = $row.attr( 'data-transaction-id' ),
					refundable_amount = $btn.attr( 'data-refundable' ),
					gateway_supports = ( '1' === $btn.attr( 'data-gateway-supports' ) ) ? true : false,
					gateway_title = $btn.attr( 'data-gateway' ),
					$new_row = $( '#llms-txn-refund-model .llms-txn-refund-form' ).clone(),
					$gateway_btn = $new_row.find( '.gateway-btn' );

				// configure and add the form
				if ( 'remove' !== $btn.attr( 'data-action' ) ) {

					$btn.text( LLMS.l10n.translate( 'Cancel' ) );
					$btn.attr( 'data-action', 'remove' );
					$new_row.find( 'input' ).removeAttr( 'disabled' );
					$new_row.find( 'input[name="llms_refund_amount"]' ).attr( 'max', refundable_amount );
					$new_row.find( 'input[name="llms_refund_txn_id"]' ).val( txn_id );

					if ( gateway_supports ) {
						$gateway_btn.find( '.llms-gateway-title' ).text( gateway_title );
						$gateway_btn.show();
					}

					$row.after( $new_row );

				} else {

					$btn.text( LLMS.l10n.translate( 'Refund' ) );
					$btn.attr( 'data-action', '' );
					$row.next( 'tr' ).remove();

				}

			} );

			$( 'button[name="llms-manual-txn-toggle"]' ).on( 'click', function() {

				var $btn = $( this ),
					$row = $btn.closest( 'tr' ),
					$new_row = $( '#llms-manual-txn-model .llms-manual-txn-form' ).clone();

				// configure and add the form
				if ( 'remove' !== $btn.attr( 'data-action' ) ) {

					$btn.text( LLMS.l10n.translate( 'Cancel' ) );
					$btn.attr( 'data-action', 'remove' );
					$new_row.find( 'input' ).removeAttr( 'disabled' );

					$row.after( $new_row );

				} else {

					$btn.text( LLMS.l10n.translate( 'Record a Manual Payment' ) );
					$btn.attr( 'data-action', '' );
					$row.next( 'tr' ).remove();

				}

			} );

			// cache the original value when focusing on a payment gateway select
			// used below so the original field related data can be restored when switching back to the originally selected gateway
			$( '.llms-metabox' ).one( 'focus', '.llms-metabox-field[data-llms-editable="payment_gateway"] select', function() {

				if ( ! $( this ).attr( 'data-original-value' ) ) {
					$( this ).attr( 'data-original-value', $( this ).val() );
				}

			} );

			// when selecting a new payment gateway get field data and update the dom to only display the fields
			// supported/needed by the newly selected gateway
			$( '.llms-metabox' ).on( 'change', '.llms-metabox-field[data-llms-editable="payment_gateway"] select', function() {

				var $select = $( this ),
					gateway = $select.val(),
					data = JSON.parse( $select.closest( '.llms-metabox-field' ).attr( 'data-gateway-fields' ) ),
					gateway_data = data[ gateway ];

				for ( var field in gateway_data ) {

					var $field = $( 'input[name="' + gateway_data[ field ].name + '"]' ),
						$wrap = $field.closest( '.llms-metabox-field' );

					// if the field is enabled show it the field and, if we're switching back to the originally selected
					// gateway, reload the value from the dom
					if ( gateway_data[ field ].enabled ) {

						$wrap.show();
						$field.attr( 'required', 'required' );
						$field.removeAttr( 'disabled' );

						if ( gateway === $select.attr( 'data-original-value') ) {
							$field.val( $wrap.attr( 'data-llms-editable-value' ) );
						}

					// otherwise hide the field
					// this will ensure it gets updated in the database
					} else {

						// always clear the value when switching
						// ensures that outdated data is removed from the DB
						$field.attr( 'value', '' );

						$field.removeAttr( 'required' );
						// $field.attr( 'disabled', 'disabled' );
						$wrap.hide();

					}

				}

			} );

		};

		/**
		 * Binds custom llms merge code buttons
		 * @return   void
		 * @since    3.1.0
		 * @version  3.9.2
		 */
		this.bind_merge_code_buttons = function( $wrappers ) {

			$wrappers = $wrappers || $( '.llms-merge-code-wrapper' );

			$wrappers.find( '.llms-merge-code-button' ).on( 'click', function() {

				$( this ).next( '.llms-merge-codes' ).toggleClass( 'active' );

			} );

			$wrappers.find( '.llms-merge-codes li' ).on( 'click', function() {

				var $el = $( this ),
					$parent = $el.closest( '.llms-merge-codes' ),
					target = $parent.attr( 'data-target' ),
					code = $el.attr( 'data-code' );

				// dealing with a tinymce instance
				if ( -1 === target.indexOf( '#' ) ) {

					var editor = window.tinymce.editors[ target ];
					if ( editor ) {
						editor.insertContent( code );
					} // fallback in case we can't access the editor directly
					else {
						alert( LLMS.l10n.translate( 'Copy this code and paste it into the desired area' ) + ': ' + code );
					}

				}
				// dealing with a DOM id
				else {

					$( target ).val( $( target ).val() + code );

				}

				$parent.removeClass( 'active' );

			} );

		};

		/**
		 * Bind metabox tabs
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind_tabs = function() {
			$( '.llms-nav-tab-wrapper .tabs li' ).on( 'click', function() {

				var $btn = $( this ),
					$metabox = $btn.closest( '.llms-mb-container' ),
					tab_id = $btn.attr( 'data-tab' );

				$btn.siblings().removeClass( 'llms-active' );

				$metabox.find( '.tab-content' ).removeClass( 'llms-active' );

				$btn.addClass( 'llms-active' );
				$( '#' + tab_id ).addClass( 'llms-active' );

			} );
		};

		/**
		 * Enable WP Post Table searches for applicable select2 boxes
		 * @return   void
		 * @since    3.0.0
		 * @version  3.21.0
		 */
		this.post_select = function( $el ) {

			var multi = 'multiple' === $el.attr( 'multiple' );

			$el.llmsPostsSelect2( {
				width: multi ? '100%' : '65%',
			} );

			if ( multi || $el.attr( 'data-no-view-button' ) ) {
				return;
			}

			// add a "View" button to see what the selected page looks like
			var msg = LLMS.l10n.translate( 'View' ),
				$btn = $( '<a class="llms-button-secondary small" style="margin-left:5px;" target="_blank" href="#">' +  msg + ' <i class="fa fa-external-link" aria-hidden="true"></i></a>' );
			$el.next( '.select2' ).after( $btn );

			$el.on( 'change', function() {
				var id = $( this ).val();
				if ( id ) {
					$btn.attr( 'href', '/?p=' + id ).show();
				} else {
					$btn.hide();
				}
			} ).trigger( 'change' );

		};

		/**
		 * Bind dom events for .llms-tables
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind_tables = function() {

			$( '.llms-table button[name="llms-expand-table"]' ).on( 'click', function() {

				var $btn = $( this ),
					$table = $btn.closest( '.llms-table' )

				// switch the text on the button if alt text is found
				if ( $btn.attr( 'data-text' ) ) {
					var text = $btn.text();
					$btn.text( $btn.attr( 'data-text' ) );
					$btn.attr( 'data-text', text );
				}

				// switch classes on all expandable elements
				$table.find( '.expandable' ).each( function() {

					if ( $( this ).hasClass( 'closed' ) ) {
						$( this ).addClass( 'opened' ).removeClass( 'closed' );
					} else {
						$( this ).addClass( 'closed' ).removeClass( 'opened' );
					}

				} );

			} );

		};

		// go
		this.init();

	};

	// initialize the object
	window.llms.metaboxes = new Metaboxes();

} )( jQuery );
