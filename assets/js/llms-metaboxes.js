/****************************************************************
 *
 * Contributor's Notice
 * 
 * This is a compiled file and should not be edited directly!
 * The uncompiled script is located in the "assets/private" directory
 * 
 ****************************************************************/

/**
 * LifterLMS Admin Panel Metabox Functions
 * @since    3.0.0
 * @version  3.21.0
 */
( function( $ ) {

	$.fn.llmsCollapsible = function() {

		var $group = this;

		this.on( 'click', '.llms-collapsible-header', function() {

			var $parent = $( this ).closest( '.llms-collapsible' ),
				$siblings = $parent.siblings( '.llms-collapsible' );

			$parent.toggleClass( 'opened' );

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
		/**
		 * LifterLMS Admin Metabox Repeater Field
		 * @since    3.11.0
		 * @version  3.23.0
		 */
		this.repeaters = {
		
			/**
			 * Reference to the parent metabox class
			 * @type  obj
			 */
			metaboxes: this,
		
			/**
			 * jQuery selector for all repeater elements on the current screen
			 * @type  {[type]}
			 */
			$repeaters: null,
		
			/**
			 * Init
			 * @return   void
			 * @since    3.11.0
			 * @version  3.23.0
			 */
			init: function() {
		
				var self = this;
		
				self.$repeaters = $( '.llms-mb-list.repeater' );
		
				if ( self.$repeaters.length ) {
		
					// wait for tinyMCE just in case their editors in the repeaters
					LLMS.wait_for( function() {
						return ( 'undefined' !== typeof tinyMCE );
					}, function() {
						self.load();
						self.bind();
					} );
		
					// on click of any post submit buttons add some data to the submit button
					// so we can see which button to trigger after repeaters are finished
					$( '#post input[type="submit"], #post-preview' ).on( 'click', function() {
						$( this ).attr( 'data-llms-clicked', 'yes' );
					} );
		
					// handle post submission
					$( '#post' ).on( 'submit', self.handle_submit );
		
				}
		
			},
		
			/**
			 * Bind DOM Events
			 * @return   void
			 * @since    3.11.0
			 * @version  3.13.0
			 */
			bind: function() {
		
				var self = this;
		
				self.$repeaters.each( function() {
		
					var $repeater = $( this ),
						$rows = $repeater.find( '.llms-repeater-rows' ),
						$model = $repeater.find( '.llms-repeater-model' );
		
					tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, $model.find( '.llms-mb-list.editor textarea' ).attr( 'id' ) );
		
					// for the repeater + button
					$repeater.find( '.llms-repeater-new-btn' ).on( 'click', function() {
						self.add_row( $repeater, null, true );
					} );
		
					// make repeater rows sortable
					$rows.sortable( {
						handle: '.llms-drag-handle',
						items: '.llms-repeater-row',
						start: function( event, ui ) {
							$rows.addClass( 'dragging' );
						},
						stop: function( event, ui ) {
							$rows.removeClass( 'dragging' );
		
							var $eds = ui.item.find( 'textarea.wp-editor-area' );
							$eds.each( function() {
								var ed_id = $( this ).attr( 'id' );
								tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, ed_id );
								tinyMCE.EditorManager.execCommand( 'mceAddEditor', true, ed_id );
							} );
		
							self.save( $repeater );
						},
					} );
		
					$repeater.on( 'click', '.llms-repeater-remove', function( e ) {
						e.stopPropagation();
						var $row = $( this ).closest( '.llms-repeater-row' );
						if ( window.confirm( LLMS.l10n.translate( 'Are you sure you want to delete this row? This cannot be undone.' ) ) ) {
							$row.remove();
							setTimeout( function() {
								self.save( $repeater );
							}, 1 );
						}
					} );
		
				} );
		
			},
		
			/**
			 * Add a new row to a repeater rows group
			 * @param    obj    $repeater  jQuery selector for the repeater to add a row to
			 * @param    obj    data       optional object of data to fill fields in the row with
			 * @param    bool   expand     if true, will automatically open the row after adding it to the dom
			 * @return 	 void
			 * @since    3.11.0
			 * @version  3.11.0
			 */
			add_row: function( $repeater, data, expand ) {
		
				var self = this,
					$rows = $repeater.find( '.llms-repeater-rows' ),
					$model = $repeater.find( '.llms-repeater-model' ),
					$row = $model.find( '.llms-repeater-row' ).clone(),
					new_index = $repeater.find( '.llms-repeater-row' ).length,
					editor = self.reindex( $row, new_index );
		
				if ( data ) {
					$.each( data, function( key, val ) {
		
						var $field = $row.find( '[name^="' + key + '"]');
		
						if ( $field.hasClass( 'llms-select2-student' ) ) {
							$.each( val, function( i, data ) {
								$field.append( '<option value="' + data.key + '" selected="selected">' + data.title + '</option>')
							} ) ;
							$field.trigger( 'change' );
						} else {
							$field.val( val );
						}
		
		
					} );
				}
		
				setTimeout( function() {
					self.bind_row( $row );
				}, 1 );
		
				$rows.append( $row );
				if ( expand ) {
					$row.find( '.llms-collapsible-header' ).trigger( 'click' );
				}
				tinyMCE.EditorManager.execCommand( 'mceAddEditor', true, editor );
		
				$repeater.trigger( 'llms-new-repeater-row', {
					$row: $row,
					data: data,
				} );
		
			},
		
			/**
			 * Bind DOM events for a single repeater row
			 * @param    obj   $row  jQuery selector for the row
			 * @return   void
			 * @since    3.11.0
			 * @version  3.13.0
			 */
			bind_row: function( $row ) {
		
				this.bind_row_header( $row );
		
				$row.find( '.llms-select2' ).llmsSelect2( {
					width: '100%',
				} );
		
				$row.find( '.llms-select2-student' ).llmsStudentsSelect2();
		
				this.metaboxes.bind_datepickers( $row.find( '.llms-datepicker' ) );
				this.metaboxes.bind_controllers( $row.find( '[data-is-controller]' ) );
				// this.metaboxes.bind_merge_code_buttons( $row.find( '.llms-merge-code-wrapper' ) );
		
			},
		
			/**
			 * Bind row header events
			 * @param    obj   $row  jQuery selector for the row
			 * @return   void
			 * @since    3.11.0
			 * @version  3.11.0
			 */
			bind_row_header: function( $row ) {
		
				// handle the title field binding
				var $title = $row.find( '.llms-repeater-title' ),
					$field = $row.find( '.llms-collapsible-header-title-field' );
		
				$title.attr( 'data-default', $title.text() );
		
				$field.on( 'keyup focusout blur', function() {
					var val = $( this ).val();
					if ( ! val ) {
						val = $title.attr( 'data-default' );
					}
					$title.text( val );
				} ).trigger( 'keyup' );
		
			},
		
			/**
			 * Handle WP Post form submission to ensure repeaters are saved before submitting the form to save/publish the post
			 * @param    obj   e  JS event object
			 * @return   void
			 * @since    3.11.0
			 * @version  3.23.0
			 */
			handle_submit: function( e ) {
		
				// get the button used to submit the form
				var $btn = $( '#post [data-llms-clicked="yes"]' ),
					$spinner = $btn.parent().find( '.spinner' );
		
				if ( $btn.is( '#post-preview' ) ) {
					$btn.removeAttr( 'data-llms-clicked' );
					return;
				}
		
				e.preventDefault();
		
				// core UX to prevent multi-click/or the appearance of a delay
				$( '#post input[type="submit"]' ).addClass( 'disabled' ).attr( 'disabled', 'disabled' );
				$spinner.addClass( 'is-active' );
		
				var self = window.llms.metaboxes.repeaters,
					i = 0,
					wait;
		
				self.$repeaters.each( function() {
					self.save( $( this ) );
				} );
		
				wait = setInterval( function() {
		
					if ( i >= 59 || ! $( '.llms-mb-list.repeater.processing' ).length ) {
		
						clearInterval( wait );
						$( '#post' ).off( 'submit', this.handle_submit );
						$spinner.removeClass( 'is-active' );
						$btn.removeClass( 'disabled' ).removeAttr( 'disabled' ).trigger( 'click' );
		
					} else {
		
						i++;
		
					}
		
				}, 1000 );
		
		
			},
		
			/**
			 * Load repereater data from the server and create rows in the DOM
			 * @return   void
			 * @since    3.11.0
			 * @version  3.12.1
			 */
			load: function() {
		
				var self = this;
		
				self.$repeaters.each( function() {
		
					var $repeater = $( this );
		
					// ensure the repeater is only loaded once to prevent duplicates resulting from duplicating binding
					// on certain sites which I cannot quite explain...
					if ( $repeater.hasClass( 'is-loaded' ) || $repeater.hasClass( 'processing' ) ) {
						return;
					}
		
					self.store( $repeater, 'load', function( data ) {
		
						$repeater.addClass( 'is-loaded' );
		
						$.each( data.data, function( i, obj ) {
							self.add_row( $repeater, obj, false );
						} );
		
						// for each row within the repeater
						$repeater.find( '.llms-repeater-rows .llms-repeater-row' ).each( function() {
							self.bind_row( $( this ) );
						} );
		
					} );
		
		
		
				} );
		
			},
		
			/**
			 * Reindex a row
			 * renames ids, attrs, and etc...
			 * Used when cloning the model for new rows
			 * @param    obj          $row  jQuery selector for the row
			 * @param    int|string   index  index (or id) to use when renaming
			 * @return   string
			 * @since    3.11.0
			 * @version  3.11.0
			 */
			reindex: function( $row, index ) {
		
				var old_index = $row.attr( 'data-row-order' ),
					$ed = $row.find( '.llms-mb-list.editor textarea' );
		
				tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, $ed.attr( 'id' ) );
		
				function replace_attr( $el, attr ) {
					$el.each( function() {
						var str = $( this ).attr( attr );
						$( this ).attr( attr, str.replace( old_index, index ) );
					} );
				};
		
				$row.attr( 'data-row-order', index );
		
				replace_attr( $row, 'data-row-order' );
		
				replace_attr( $row.find( 'button.insert-media' ), 'data-editor' );
		
				replace_attr( $row.find( 'input[name^="_llms"], textarea[name^="_llms"], select[name^="_llms"]' ), 'id' );
				replace_attr( $row.find( 'input[name^="_llms"], textarea[name^="_llms"], select[name^="_llms"]' ), 'name' );
				replace_attr( $row.find( '[data-controller]' ), 'data-controller' );
				replace_attr( $row.find( '[data-controller]' ), 'data-controller' );
				replace_attr( $row.find( 'button.wp-switch-editor' ), 'data-wp-editor-id' );
				replace_attr( $row.find( 'button.wp-switch-editor' ), 'id' );
				replace_attr( $row.find( '.wp-editor-tools' ), 'id' );
				replace_attr( $row.find( '.wp-editor-container' ), 'id' );
		
		
				return $ed.attr( 'id' );
		
			},
		
			/**
			 * Save a single repeaters data to the server
			 * @param    obj   $repeater  jQuery selector for a repeater element
			 * @return   vois
			 * @since    3.11.0
			 * @version  3.13.0
			 */
			save: function( $repeater ) {
				$repeater.trigger( 'llms-repeater-before-save', { $el: $repeater } );
				this.store( $repeater, 'save' );
			},
		
			/**
			 * Convert a repeater element into an array of objects that can be saved to the database
			 * @param    obj   $repeater  jQuery selector for a repeater element
			 * @return   void
			 * @since    3.11.0
			 * @version  3.11.0
			 */
			serialize: function( $repeater ) {
		
				var rows = [];
		
				$repeater.find( '.llms-repeater-rows .llms-repeater-row' ).each( function() {
		
					var obj = {};
		
					// easy...
					$( this ).find( 'input[name^="_llms"], select[name^="_llms"]' ).each( function() {
						obj[ $( this ).attr( 'name' ) ] = $( this ).val();
					} );
		
					// check if the textarea is a tinyMCE instance
					$( this ).find( 'textarea[name^="_llms"]' ).each( function() {
		
						var name = $( this ).attr( 'name' );
		
						// if it is an editor
						if ( tinyMCE.editors[ name ] ) {
							obj[ name ] = tinyMCE.editors[ name ].getContent();
						// grab the val of the textarea
						} else {
							obj[ name ] = $( this ).val();
						}
		
					} );
		
					rows.push( obj );
		
				} );
		
				return rows;
		
			},
		
			/**
			 * AJAX method for interacting with the repeater's handler on the server
			 * @param    obj       $repeater  jQuery selector for the repeater element
			 * @param    string    action     action to call [save|load]
			 * @param    function  cb         callback function
			 * @return   void
			 * @since    3.11.0
			 * @version  3.11.0
			 */
			store: function( $repeater, action, cb ) {
		
				cb = cb || function(){};
				var self = this,
					data = {
						action: $repeater.find( '.llms-repeater-field-handler' ).val(),
						store_action: action,
					};
		
				if ( 'save' === action ) {
					data.rows = self.serialize( $repeater );
				}
		
				LLMS.Ajax.call( {
					data: data,
					beforeSend: function() {
		
						$repeater.addClass( 'processing' );
						LLMS.Spinner.start( $repeater );
		
					},
					success: function( r ) {
		
						cb( r );
						LLMS.Spinner.stop( $repeater );
						$repeater.removeClass( 'processing' );
		
					}
		
				} );
		
			}
		
		};
		this.repeaters.init();
		

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

			// bind everything better and less repetatively...
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
		 * @param    obj   $controllerss  jQuery selctor for checkboxes to be bound as checkbox controllers
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
		 * @param    obj   $controllerss  jQuery selctor for elements to be bound as checkbox controllers
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
		 * @version  3.10.0
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

			// custom trigger when called when the engagement type changs
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
		 * @return   void
		 * @since    3.0.0
		 * @version  3.18.2
		 */
		this.bind_llms_membership = function() {

			// remove auto-enroll course
			$( 'a[href="#llms-course-remove"]' ).on( 'click', function( e ) {

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
							}, 400 );

						} else {

							$container.prepend( '<p class="error">' + r.message + '</p>' );

						}

						LLMS.Spinner.stop( $container );
					},
				} );

			} );

			// bulk enroll all members into a course
			$( 'a[href="#llms-course-bulk-enroll"]' ).on( 'click', function( e ) {

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

		};

		/**
		 * Actions for ORDERS
		 * @return   void
		 * @since    3.0.0
		 * @version  3.10.0
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
			// used below so the original field related data can be restored when switching back to the orignially selected gateway
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

					// always clear the value when switching
					// ensures that outdated data is removed from the DB
					$field.attr( 'value', '' );

					// if the field is enabled show it the field and, if we're switching back to the originally selected
					// gateway, reload the value from the dom
					if ( gateway_data[ field ].enabled ) {

						$wrap.show();
						$field.attr( 'required', 'required' );
						if ( gateway === $select.attr( 'data-original-value') ) {
							$field.val( $wrap.attr( 'data-llms-editable-value' ) );
						}

					// otherwise hide the field
					// this will ensure it gets updated in the database
					} else {

						$field.removeAttr( 'required' );
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

	// initalize the object
	window.llms.metaboxes = new Metaboxes();

} )( jQuery );
