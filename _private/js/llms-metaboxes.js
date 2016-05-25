( function( $ ) {

	window.llms = window.llms || {};


	window.llms.metaboxes = function() {

		/**
		 * Initialize
		 * @return void
		 *
		 * @since  3.0.0
		 */
		this.init = function() {

			if ( $( '.llms-datepicker' ) ) {

				this.bind_datepickers();

			}

			// if a post type is set & a bind exists for it, bind it
			if ( window.llms.post.post_type ) {

				var func = 'bind_' + window.llms.post.post_type;

				if ( 'function' === typeof this[func] ) {

					this[func]();

				}

			}

		};


		this.bind_datepickers = function() {

			$('.llms-datepicker').datepicker( {
				dateFormat: "mm/dd/yy"
			} );

		};



		this.bind_llms_order = function() {

			$conditionals = $( '.llms-metabox .show-conditionally' );
			$conditionals.hide();

			$( '#_llms_order_status' ).on( 'change', function() {
				var $el = $( this ),
					$box = $el.closest( '.llms-metabox' ),
					$show = $box.find( '.' + $el.val() );

				$conditionals.hide();

				if ( $show.length ) {

					$show.show();

				} else {

					$box.find( '.default' ).show();

				}

				$box.find( $( this ).val() );

			} ).trigger( 'change' );

		};



		// go
		this.init();

	};

	var a = new window.llms.metaboxes();

} )( jQuery );
