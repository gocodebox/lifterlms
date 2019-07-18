/**
 * LifterLMS Admin Tables
 * @since    3.2.0
 * @version  3.28.1
 */
;( function( $, undefined ) {

	window.llms = window.llms || {};

	var AdminTables = function() {

		this.$tables = null;

		/**
		 * Initialize
		 * @return void
		 * @since  3.2.0
		 */
		this.init = function() {

			var self = this;

			self.$tables = $( '.llms-gb-table' );

			if ( self.$tables.length ) {
				self.bind();
			}

		};

		/**
		 * Bind DOM events
		 * @return   void
		 * @since    2.3.0
		 * @version  3.28.0
		 */
		this.bind = function() {

			var self = this;

			this.$tables.each( function() {

				var $table = $( this );

				$table.on( 'click', 'button[name="llms-table-paging"]', function( e ) {
					e.preventDefault();
					self.change_page( $table, $( this ) );
				} );

				$table.on( 'click', 'button[name="llms-table-export"]', function( e ) {
					e.preventDefault();
					self.export( $table, $( this ) );
				} );

				$table.on( 'click', 'a.llms-sortable', function( e ) {
					e.preventDefault();
					self.change_order( $table, $( this ) );
				} );

				$table.parent().find( '.llms-table-filters' ).on( 'change', 'select.llms-table-filter', function( e ) {
					self.change_filter( $table, $( this ) );
				} );

				$table.parent().find( '.llms-table-search' ).on( 'keyup', 'input', debounce( function( e ) {

					switch ( e.keyCode ) {

						case 37:
						case 38:
						case 39:
						case 40:
							return;
						break;

						default:
							self.search( $table, $( this ) );

					}

				}, 250 ) );

			} );

		};

		/**
		 * Handle clicks on sortable column headers
		 * @param    obj   $table   jQuery selector for the current table
		 * @param    obj   $anchor  jQuery selector for the clicked column head anchor
		 * @return   void
		 * @since    3.2.0
		 * @version  3.2.0
		 */
		this.change_order = function( $table, $anchor ) {

			this.reload( $table, {
				order: $anchor.attr( 'data-order' ),
				orderby: $anchor.attr( 'data-orderby' ),
				page: 1,
			} );

		};

		this.change_filter = function( $table, $select ) {

			this.reload( $table, {
				filter: $select.val(),
				filterby: $select.attr( 'name' ),
				page: 1,
			} );

		};

		/**
		 * Change the current page of the table on a next/back click
		 * @param    obj   $table  jQuery selector for the current table
		 * @param    obj   $btn    jQuery selector for the clicked button
		 * @return   void
		 * @since    3.2.0
		 * @version  3.4.0
		 */
		this.change_page = function( $table, $btn ) {

			this.reload( $table, {
				order: this.get_args( $table, 'order' ),
				orderby: this.get_args( $table, 'orderby' ),
				page: $btn.attr( 'data-page' ),
			} );

		};

		/**
		 * Handle
		 * @param    obj    $table    jQuery object for the table
		 * @param    obj    $btn      jQuery object for the clicked button
		 * @param    string filename  filename of the export in progress.
		 * @return   void
		 * @since    3.15.0
		 * @version  3.28.1
		 */
		this.export = function( $table, $btn, filename ) {

			var self = this,
				$msg = $table.find( '.llms-table-export .llms-table-export-msg' ),
				$progress = $table.find( '.llms-table-export .llms-table-progress' );

			function activate_button() {
				LLMS.Spinner.stop( $btn, 'small' );
				$btn.removeAttr( 'disabled' );
			}

			LLMS.Ajax.call( {
				data: $.extend( {
					action: 'export_admin_table',
					handler: $table.attr( 'data-handler' ),
					filename: filename,
				}, JSON.parse( $table.attr( 'data-args' ) ) ),
				beforeSend: function() {

					if ( ! $btn.attr( 'disabled' ) ) {
						$btn.attr( 'disabled', 'disabled' );
						LLMS.Spinner.start( $btn, 'small' );
					}

				},
				error: function( jqXHR, status, error ) {

					var msg = LLMS.l10n.translate( 'An error was encountered generating the export' );
					activate_button();
					$progress.hide();
					$msg.html( '<span class="llms-error">' + msg + ': ' + error + '</span>' );
					console.error( jqXHR );

				},
				success: function( res ) {

					if ( ! res.success && res.message ) {

						activate_button();
						$progress.hide();
						$msg.html( '<span class="llms-error">' + res.message + '</span>' );

					} else if ( res.success && res.data && res.data.progress ) {

						$msg.html( '' );

						// only show a progress bar if it's going to take more than one request.
						if ( ! $progress.is( 'visible' ) && res.data.progress !== 100 ) {
							$progress.css( 'display', 'inline-block' );
						}

						$progress.find( '.llms-table-progress-text' ).text( res.data.progress + '%' );
						$progress.find( '.llms-table-progress-inner' ).css( 'width', res.data.progress + '%' );

						// if we're not finished, make another request.
						if ( 100 !== res.data.progress ) {
							self.export( $table, $btn, res.data.filename );

						// finished, download the file and cleanup the interface.
						} else {
							setTimeout( function() {
								var id = 'llms-dl-export';
								$( '#' + id ).remove();
								$( '<a />', {
									id: id,
									href: res.data.url,
									style: 'display: hidden;',
									download: '',
								} ).appendTo( 'body' );
								$( '#' + id )[0].click();
								activate_button();
								$progress.hide();
							}, 1000 );
						}

					}

				}
			} );

		}

		/**
		 * Retrieve arguments stored in the table and parse into a readable object
		 * @param    obj     $table  jQuery selector for the current table
		 * @param    string  item    key to grab a specific value from the args object
		 * @return   mixed
		 * @since    3.2.0
		 * @version  3.2.0
		 */
		this.get_args = function( $table, item ) {

			var args = JSON.parse( $table.attr( 'data-args' ) );

			if ( item ) {
				return ( args[ item ] ) ? args[ item ] : false;
			} else {
				return args;
			}

		};

		/**
		 * Reload a table
		 * @param    obj   $table  jQuery selector for the current table
		 * @param    obj   args    arguments to pass with the ajax query
		 * @return   void
		 * @since    3.2.0
		 * @version  3.2.0
		 */
		this.reload = function( $table, args ) {

			args = $.extend( {
				action: 'get_admin_table_data',
				handler: $table.attr( 'data-handler' ),
			}, JSON.parse( $table.attr( 'data-args' ) ), args );

			LLMS.Ajax.call( {
				data: args,
				beforeSend: function() {

					LLMS.Spinner.start( $table.closest( '.llms-table-wrap' ) );

				},
				success: function( r ) {

					LLMS.Spinner.stop( $table.closest( '.llms-table-wrap' ) )

					if ( r.success ) {

						$table.attr( 'data-args', r.data.args );

						$table.find( 'thead' ).replaceWith( r.data.thead );
						$table.find( 'tbody' ).replaceWith( r.data.tbody );
						$table.find( 'tfoot' ).replaceWith( r.data.tfoot );

					}

				}
			} );

		};

		/**
		 * Executes an AJAX search query
		 * @param    obj   $table  jQuery selector for the current table
		 * @param    obj   $input  jQuery selector for the search input
		 * @return   void
		 * @since    3.2.0
		 * @version  3.2.0
		 */
		this.search = function( $table, $input ) {

			var val = $input.val()
				len = val.length;

			if ( 0 === len || len >= 3 ) {
				this.reload( $table, {
					page: 1,
					search: $input.val(),
				} );
			}

		};

		/**
		 * Throttle function by a delay in ms
		 * @param    Function  fn     callback function
		 * @param    int       delay  delay in millisecond
		 * @return   function
		 * @since    3.2.0
		 * @version  3.2.0
		 */
		function debounce( fn, delay ) {
			var timer = null;
			return function () {
				var context = this,
					args = arguments;
				window.clearTimeout( timer );
				timer = window.setTimeout( function () {
					fn.apply( context, args );
				}, delay );
			};
		}

		// go
		this.init();

	};

	// initialize the object
	window.llms.admin_tables = new AdminTables();

} )( jQuery );
