/**
 * LifterLMS Admin Tables
 * @since  3.2.0
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
		 * @version  2.3.0
		 */
		this.bind = function() {

			var self = this;

			this.$tables.each( function() {

				var $table = $( this );

				$table.on( 'click', 'button[name="llms-table-paging"]', function() {
					self.change_page( $table, $( this ) );
				} );

				$table.on( 'click', 'a.llms-sortable', function( e ) {
					e.preventDefault();
					self.change_order( $table, $( this ) );
				} );

			} );

		};

		this.change_order = function( $table, $anchor ) {

			// console.log( $anchor.attr( 'data-order' ), $anchor.attr( 'data-orderby' ) );

			this.reload( $table, {
				order: $anchor.attr( 'data-order' ),
				orderby: $anchor.attr( 'data-orderby' ),
				page: 1,
			} );

		};

		/**
		 * Change the current page of the table on a next/back click
		 * @param    obj   $table  jQuery selector for the current table
		 * @param    obj   $btn    jQuery selector for the clicked button
		 * @return   void
		 * @since    3.2.0
		 * @version  3.2.0
		 */
		this.change_page = function( $table, $btn ) {

			var curr = this.get_args( $table, 'page' ),
				new_page;

			switch ( $btn.data( 'dir' ) ) {
				case 'back': new_page = curr - 1; break;
				case 'next': new_page = curr + 1; break;
			}

			this.reload( $table, {
				order: this.get_args( $table, 'order' ),
				orderby: this.get_args( $table, 'orderby' ),
				page: new_page,
			} );

		};

		this.get_args = function( $table, item ) {

			var args = JSON.parse( $table.attr( 'data-args' ) );

			if ( item ) {
				return ( args[ item ] ) ? args[ item ] : false;
			} else {
				return args;
			}

		};

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

		// go
		this.init();

	};

	// initalize the object
	window.llms.admin_tables = new AdminTables();

} )( jQuery );
