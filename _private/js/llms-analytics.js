/**
 * LifterLMS Admin Reporting Widgets & Charts
 * @since    3.0.0
 * @version  3.2.0
 */
;( function( $, undefined ) {

	window.llms = window.llms || {};

	var Analytics = function() {

		this.charts_loaded = false;
		this.data = {};
		this.query = $.parseJSON( $( '#llms-analytics-json' ).text() );
		this.timeout = 8000;

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


		this.charts_ready = function() {

			window.llms.analytics.charts_loaded = true;
			window.llms.analytics.draw_chart();

		};


		this.draw_chart = function() {

			if ( ! this.charts_loaded || ! this.is_loading_finished() ) {
				return;
			}

			var self = this,
				chart = new google.visualization.ComboChart( document.getElementById( 'llms-charts-wrapper' ) ),
				data = self.get_chart_data(),
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
				// 	// ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
				// 	// ['2004/05',  165,      938,         522,             998,           450,      614.6],
				// 	// ['2005/06',  135,      1120,        599,             1268,          288,      682],
				// 	// ['2006/07',  157,      1167,        587,             807,           397,      623],
				// 	// ['2007/08',  139,      1110,        615,             968,           215,      609.4],
				// 	// ['2008/09',  136,      691,         629,             1026,          366,      569.6]
				// ] )

			if ( data.length ) {

				data = google.visualization.arrayToDataTable( data );
				data.sort([{column: 0}])
				chart.draw( data, options );

			}



		};

		this.is_loading_finished = function() {
			if ( $( '.llms-widget.is-loading' ).length ) {
				return false;
			}
			return true;
		};

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

					if( 'undefined' !== typeof r.response ) {

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

					// console.log( r );

					if ( 'error' === status ) {

						if( 'timeout' === r.statusText ) {

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
					$content.text( content_text );

					self.widget_finished( $widget );

				}

			} );

		};

		this.get_date_diff = function() {

			var end = new Date( this.query.dates.end ),
				start = new Date( this.query.dates.start );

			return Math.abs( end.getTime() - start.getTime() );

		};

		this.get_chart_data_object = function() {

			var self = this,
				max_for_days = ( ( 1000 * 3600 * 24 ) * 30 ) * 4, // 4 months in seconds
				diff = this.get_date_diff(),
				data = {},
				res, i, d, date;

			for ( var method in self.data ) {

				if ( ! self.data.hasOwnProperty( method ) ) {
					continue;
				}

				if ( 'object' !== typeof self.data[ method ].chart_data || 'object' !== typeof self.data[ method ].results ) {
					continue;
				}

				res = self.data[ method ].results;

				for ( i = 0; i < res.length; i++ ) {

					d = new Date( res[i].date );

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

			return data;

		};

		this.get_chart_data = function() {

			var self = this,
				obj = self.get_chart_data_object(),
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


		this.get_empty_data_object = function( date ) {

			var self = this,
				obj = {
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

		this.get_chart_headers = function() {

			var self = this,
				h = [];

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

		this.get_chart_series_options = function() {

			var self = this,
				options = {}
				i = 0;

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

				}

				i++;

			}

			return options;

		};

		this.widget_finished = function( $widget ) {

			if ( this.is_loading_finished() ) {
				this.draw_chart();
			}


		};

		this.init();

		return this;

	};


	window.llms.analytics = new Analytics();

} )( jQuery );



