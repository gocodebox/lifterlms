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
