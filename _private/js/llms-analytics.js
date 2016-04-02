( function( $ ) {

	window.llms = window.llms || {};


	window.llms.analytics = function() {

		this.charts_ready = false;
		this.data = $.parseJSON( $( '#llms-analytics-json' ).text() );
		this.timeout = 5000;

		this.$widgets = $( '.llms-widget' );


		this.init = function() {

			google.charts.load( 'current', {
				packages: [
					'corechart'
				]
			} );
      		google.charts.setOnLoadCallback( this.charts_ready );

			this.bind();
			this.load_widgets();

		};


		this.bind = function() {

			$( '.llms-datepicker' ).datepicker( {
				dateFormat: 'yy-mm-dd',
				maxDate: 0,
			} );


			$( '#llms-students-ids-filter' ).llmsStudentsSelect2( {
				multiple: true,
				placeholder: 'Filter by Student(s)',
				width: '280px',
			} );

		};

		this.charts_ready = function() {

			console.log( 'ready' );

		}


		this.load_widgets = function() {

			var self = this;

			this.$widgets.each( function() {

				self.load_widget( $( this ) );

			} );

		};


		this.load_widget = function( $widget ) {

			var self = this,
				method = $widget.attr( 'data-method' ),
				$content = $widget.find( 'h1' ),
				$retry = $widget.find( '.llms-reload-widget' ),
				content_text = 'Unknown error',
				status;

			$widget.addClass( 'is-loading' );

			$.ajax( {

				data: {
					action: 'llms_widget_' + method,
					dates: self.data.dates
				},
				method: 'POST',
				timeout: self.timeout,
				url: window.ajaxurl,
				success: function( r ) {

					status = 'success';

					if( r.response ) {

						content_text = r.response;

					}


				},
				error: function( r ) {

					status = 'error';

				},
				complete: function( r ) {

					console.log( r );

					if ( 'error' === status ) {

						if( 'timeout' === r.statusText ) {

							content_text = 'Request timed out';

						} else {

							content_text = 'Unknown error';

						}


						if ( ! $retry.length ) {

							$retry = $( '<a class="llms-reload-widget" href="#">Retry</a>' );
							$retry.on( 'click', function( e ) {

								e.preventDefault();
								self.load_widget( $widget );

							} );

							$widget.append( $retry );

						}

					}

					$widget.removeClass( 'is-loading' );
					$content.text( content_text );

				}

			} );

		};


		this.init();

		return this;

	};


	new window.llms.analytics();

} )( jQuery );



