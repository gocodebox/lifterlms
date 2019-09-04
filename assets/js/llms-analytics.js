;/**
 * LifterLMS Admin Reporting Widgets & Charts
 *
 * @since 3.0.0
 * @since 3.17.2 Unknown.
 * @since 3.33.1 Fix issue that produced series options not aligned with the chart data.
 */( function( $, undefined ) {

	window.llms = window.llms || {};

	/**
	 * LifterLMS Admin Analytics
	 *
	 * @since    3.0.0
	 * @version  3.5.0
	 */
	var Analytics = function() {

		this.charts_loaded = false;
		this.data          = {};
		this.query         = $.parseJSON( $( '#llms-analytics-json' ).text() );
		this.timeout       = 8000;

		this.$widgets = $( '.llms-widget[data-method]' );

		/**
		 * Initializer
		 *
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
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

		/**
		 * Bind DOM events
		 *
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind = function() {

			$( '.llms-datepicker' ).datepicker( {
				dateFormat: 'yy-mm-dd',
				maxDate: 0,
			} );

			$( '#llms-students-ids-filter' ).llmsStudentsSelect2( {
				multiple: true,
				placeholder: LLMS.l10n.translate( 'Filter by Student(s)' ),
			} );

			$( 'a[href="#llms-toggle-filters"]' ).on( 'click', function( e ) {
				e.preventDefault();
				$( '.llms-analytics-filters' ).slideToggle( 100 );
			} );

			$( '#llms-custom-date-submit' ).on( 'click', function() {
				$( 'input[name="range"]' ).val( 'custom' );
			} );

			$( '#llms-date-quick-filters a.llms-nav-link[data-range]' ).on( 'click', function( e ) {

				e.preventDefault();
				$( 'input[name="range"]' ).val( $( this ).attr( 'data-range' ) );

				$( 'form.llms-reporting-nav' ).submit();

			} );

		};

		/**
		 * Called  by Google Charts when the library is loaded and ready
		 *
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.charts_ready = function() {

			window.llms.analytics.charts_loaded = true;
			window.llms.analytics.draw_chart();

		};

		/**
		 * Render the chart
		 *
		 * @return   void
		 * @since    3.0.0
		 * @version  3.17.6
		 */
		this.draw_chart = function() {

			if ( ! this.charts_loaded || ! this.is_loading_finished() ) {
				return;
			}

			var el = document.getElementById( 'llms-charts-wrapper' );

			if ( ! el ) {
				return;
			}

			var self    = this,
				chart   = new google.visualization.ComboChart( el ),
				data    = self.get_chart_data(),
				options = {
					chartArea: {
						height: '75%',
						width: '85%',
						// bottom: 20,
						// left: 20,
						// right: 20,
						// top: 20,
					},
					colors: ['#606C38','#E85D75','#EF8354','#C64191','#731963'],
					height: 560,
					lineWidth: 4,
					seriesType: 'bars',
					series: self.get_chart_series_options(),
					vAxes: {
						0: {
							format: 'currency',
						},
						1: {
							format: '',
						},
					},
			};
				// data = google.visualization.arrayToDataTable( [
				// ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
				// ['2004/05',  165,      938,         522,             998,           450,      614.6],
				// ['2005/06',  135,      1120,        599,             1268,          288,      682],
				// ['2006/07',  157,      1167,        587,             807,           397,      623],
				// ['2007/08',  139,      1110,        615,             968,           215,      609.4],
				// ['2008/09',  136,      691,         629,             1026,          366,      569.6]
				// ] )

			if ( data.length ) {

				data = google.visualization.arrayToDataTable( data );
				data.sort( [{column: 0}] );
				chart.draw( data, options );

			}

		};

		/**
		 * Check if a widget is still loading
		 *
		 * @return   bool
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.is_loading_finished = function() {
			if ( $( '.llms-widget.is-loading' ).length ) {
				return false;
			}
			return true;
		};

		/**
		 * Start loading all widgets on the current screen
		 *
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.load_widgets = function() {

			var self = this;

			this.$widgets.each( function() {

				self.load_widget( $( this ) );

			} );

		};

		/**
		 * Load a specific widget
		 *
		 * @param    obj   $widget  jQuery selector of the widget element
		 * @return   void
		 * @since    3.0.0
		 * @version  3.16.8
		 */
		this.load_widget = function( $widget ) {

			var self         = this,
				method       = $widget.attr( 'data-method' ),
				$content     = $widget.find( 'h1' ),
				$retry       = $widget.find( '.llms-reload-widget' ),
				content_text = LLMS.l10n.translate( 'Error' ),
				status;

			$widget.addClass( 'is-loading' );

			$.ajax( {

				data: {
					action: 'llms_widget_' + method,
					dates: self.query.dates,
					courses: self.query.current_courses,
					memberships: self.query.current_memberships,
					students: self.query.current_students,
				},
				method: 'POST',
				timeout: self.timeout,
				url: window.ajaxurl,
				success: function( r ) {

					status = 'success';

					if ( 'undefined' !== typeof r.response ) {

						content_text = r.response;

						self.data[method] = {
							chart_data: r.chart_data,
							response: r.response,
							results: r.results,
						};

						$retry.remove();

					}

				},
				error: function( r ) {

					status = 'error';

				},
				complete: function( r ) {

					if ( 'error' === status ) {

						if ( 'timeout' === r.statusText ) {

							content_text = LLMS.l10n.translate( 'Request timed out' );

						} else {

							content_text = LLMS.l10n.translate( 'Error' );

						}

						if ( ! $retry.length ) {

							$retry = $( '<a class="llms-reload-widget" href="#">' + LLMS.l10n.translate( 'Retry' ) + '</a>' );
							$retry.on( 'click', function( e ) {

								e.preventDefault();
								self.load_widget( $widget );

							} );

							$widget.append( $retry );

						}

					}

					$widget.removeClass( 'is-loading' );
					$content.html( content_text );

					self.widget_finished( $widget );

				}

			} );

		};

		/**
		 * Get the time in seconds between the queried dates
		 *
		 * @return   int
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.get_date_diff = function() {

			var end   = new Date( this.query.dates.end ),
				start = new Date( this.query.dates.start );

			return Math.abs( end.getTime() - start.getTime() );

		};

		/**
		 * Builds an object of data that can be used to, ultimately, draw the screen's chart
		 *
		 * @return   obj
		 * @since    3.0.0
		 * @version  3.1.6
		 */
		this.get_chart_data_object = function() {

			var self         = this,
				max_for_days = ( ( 1000 * 3600 * 24 ) * 30 ) * 4, // 4 months in seconds
				diff         = this.get_date_diff(),
				data         = {},
				res, i, d, date;

			for ( var method in self.data ) {

				if ( ! self.data.hasOwnProperty( method ) ) {
					continue;
				}

				if ( 'object' !== typeof self.data[ method ].chart_data || 'object' !== typeof self.data[ method ].results ) {
					continue;
				}

				res = self.data[ method ].results;

				if ( res ) {

					for ( i = 0; i < res.length; i++ ) {

						d = this.init_date( res[i].date );

						// group by days
						if ( diff <= max_for_days ) {
							date = new Date( d.getFullYear(), d.getMonth(), d.getDate() );
						}
						// group by months
						else {
							date = new Date( d.getFullYear(), d.getMonth(), 1 );
						}

						if ( ! data[ date ] ) {
							data[ date ] = this.get_empty_data_object( date )
						}

						switch ( self.data[ method ].chart_data.type ) {

							case 'amount':
								data[ date ][ method ] = data[ date ][ method ] + ( res[i][ self.data[ method ].chart_data.key ] * 1 );
							break;

							case 'count':
							default:
								data[ date ][ method ]++;
							break;

						}

					}

				}

			}

			return data;

		};

		/**
		 * Get the data google charts needs to initiate the current chart
		 *
		 * @return   obj
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.get_chart_data = function() {

			var self = this,
				obj  = self.get_chart_data_object(),
				data = self.get_chart_headers();

			for ( var date in obj ) {

				if ( ! obj.hasOwnProperty( date ) ) {
					continue;
				}

				var row = [ obj[ date ]._date ];

				for ( var item in obj[ date ] ) {
					if ( ! obj[ date ].hasOwnProperty( item ) ) {
						continue;
					}

					// skip meta items
					if ( 0 === item.indexOf( '_' ) ) {
						continue;
					}

					row.push( obj[ date ][ item ] );
				}

				data.push( row );

			}

			return data;

		};

		/**
		 * Get a stub of the data object used by this.get_data_object
		 *
		 * @param    string   date  date to instantiate the object with
		 * @return   obj
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.get_empty_data_object = function( date ) {

			var self = this,
				obj  = {
					_date: date,
			};

			for ( var method in self.data ) {
				if ( ! self.data.hasOwnProperty( method ) ) {
					continue;
				}

				if ( self.data[ method ].chart_data ) {
					obj[ method ] = 0;
				}

			}

			return obj;

		};

		/**
		 * Builds an array of chart header data
		 *
		 * @return   array
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.get_chart_headers = function() {

			var self = this,
				h    = [];

			// date headers go first
			h.push( {
				label: LLMS.l10n.translate( 'Date' ),
				id: 'date',
				type: 'date',
			} );

			for ( var method in self.data ) {
				if ( ! self.data.hasOwnProperty( method ) ) {
					continue;
				}

				if ( self.data[ method ].chart_data ) {
					h.push( self.data[ method ].chart_data.header );
				}

			}

			return [ h ];

		};

		/**
		 * Get a object of series options needed to draw the chart.
		 *
		 * @since 3.0.0
		 * @since Fix issue that produced series options not aligned with the chart data.
		 *
		 * @return void
		 */
		this.get_chart_series_options = function() {

			var self    = this,
				options = {}
				i       = 0;

			for ( var method in self.data ) {
				if ( ! self.data.hasOwnProperty( method ) ) {
					continue;
				}

				if ( self.data[ method ].chart_data ) {

					var type = self.data[ method ].chart_data.type;

					options[ i ] = {
						type: ( 'count' === type ) ? 'bars' : 'line',
						targetAxisIndex: ( 'count' === type ) ? 1 : 0,
					};

					i++;

				}

			}

			return options;

		};

		/**
		 * Instantiate a Date instance via a date string
		 *
		 * @param    string   string  date string, expected format should be from php date( 'Y-m-d H:i:s' )
		 * @return   obj
		 * @since    3.1.4
		 * @version  3.1.5
		 */
		this.init_date = function( string ) {

			var parts, date, time;

			parts = string.split( ' ' );

			date = parts[0].split( '-' );
			time = parts[1].split( ':' );

			return new Date( date[0], date[1] - 1, date[2], time[0], time[1], time[2] );

		};

		/**
		 * Called when a widget is finished loading
		 * Updates the current chart with the new data from the widget
		 *
		 * @param    obj   $widget  jQuery selector of the widget element
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.widget_finished = function( $widget ) {

			if ( this.is_loading_finished() ) {
				this.draw_chart();
			}

		};

		// go
		this.init();

		// return
		return this;

	};

	window.llms.analytics = new Analytics();

} )( jQuery );
