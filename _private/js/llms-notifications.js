/**
 * LifterLMS Basic Notifications Displayer
 * @since    3.8.0
 * @version  [version]
 */
;( function( $ ) {

	var llms_notifications = function() {

		var self = this,
			settings = ( window.llms && window.llms.notification_settings ) ? window.llms.notification_settings : {},
			heartbeat_delay = settings.heartbeat_delay ? settings.heartbeat_delay : 0,
			heartbeat_interval = settings.heartbeat_interval ? settings.heartbeat_interval : 30000,
			notifications = [],
			dismissals = [],
			heartbeat;

		/**
		 * Bind dom events
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		function bind_events() {
			$( 'body' ).on( 'click', '.llms-notification-dismiss', function() {
				self.dismiss( $( this ).closest( '.llms-notification' ) );
			} );
		};

		/**
		 * Add a dismissal to the array of dismissals to be pushed to the server
		 * during the next heartbeat
		 * @param    int   id  notification ID
		 * @since    3.8.0
		 * @version  [version]
		 */
		function add_dismissal( id ) {

			$.map( dismissals, function( item ) {
				if ( id == item.id ) {
					return;
				}
			} );

			dismissals.push( {
				id: id,
				status: 'read',
			} );

		};

		/**
		 * Remove a dismissal from the array of dismissals
		 * @param    int   id  notification id
		 * @return   {[type]}
		 * @since    [version]
		 * @version  [version]
		 */
		function remove_dismissal( id ) {

			var index = null;

			$.map( dismissals, function( item, i ) {
				if ( id == item.id ) {
					index = i;
					return;
				}
			} );

			if ( null !== index ) {
				dismissals.splice( index, 1 );
			}

		};

		/**
		 * Clear the currently running heartbeat
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		function clear_heartbeat() {
			clearInterval( heartbeat );
		};

		/**
		 * Heartbeat callback function
		 * @return   void
		 * @since    3.8.0
		 * @version  [version]
		 */
		function do_heartbeat() {

			pump( function() {

				if ( self.restart_heartbeat ) {
					self.restart_heartbeat = false;
					start_heartbeat();
				}

				self.block_ajax = false;

				if ( ! self.has_notifications ) {
					return;
				}
				self.show_all();

			} );

		};

		/**
		 * Heartbeat function
		 * @param    {Function}  cb  callbace
		 * @return   void
		 * @since    3.8.0
		 * @version  [version]
		 */
		pump = function( cb ) {

			// ajax is blocked, restart the heart and try again on the next interval
			if ( self.block_ajax ) {
				self.restart_heartbeat = true;
				clear_heartbeat();
				return cb();
			}

			// block ajax until this pump is finished
			self.block_ajax = true;

			if ( ! dismissals.length ) {

				retrieve( function() {
					cb();
				} );

			} else {

				update( function() {
					retrieve( function() {
						cb();
					} );
				} );

			}

		};

		/**
		 * GET request to retrieve new notifications
		 * @param    {Function}  cb  callback function
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		retrieve = function( cb ) {

			LLMS.Ajax.call( {
				llms_rest: true,
				llms_rest_endpoint: 'notifications',
				type: 'GET',
				data: {
					per_page: 5,
					status: 'new',
					subscriber: 'self',
					type: 'basic',
				},
				success: function( r, status, xhr ) {

					if ( 'success' === status && r.length ) {
						self.queue( r );
						self.empties = 0;
					} else {
						// slow down the interval every 3 empty requests
						self.empties++;
						if ( 0 === self.empties % 3 ) {
							heartbeat_interval += heartbeat_interval / 2;
							self.restart_heartbeat = true;
							clear_heartbeat();
						}
					}

					cb();

				}
			} );
		}

		/**
		 * PUT request to update notification read status
		 * @param    {Function}  cb  callback function
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update = function( cb ) {

			LLMS.Ajax.call( {
				llms_rest: true,
				llms_rest_endpoint: 'notifications/batch',
				type: 'PUT',
				data: {
					update: dismissals,
				},
				success: function( r, status, xhr ) {

					$.each( r.update, function( i, item ) {
						remove_dismissal( item.id );
					} );
					if ( cb ) {
						cb();
					}

				}

			} );

		};

		/**
		 * Start the heartbeat
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		function start_heartbeat() {
			heartbeat = setInterval( do_heartbeat, heartbeat_interval );
		};

		/**
		 * Prevent multiple simultaneous ajax calls from being made
		 * @type  {Boolean}
		 */
		this.block_ajax = false;

		/**
		 * If a heartbeat request is blocked, this will be used to restart it
		 * @type  {Boolean}
		 */
		this.restart_heartbeat = false;

		/**
		 * Count empty retrieve requests
		 * @type  {Number}
		 */
		this.empties = 0;

		/**
		 * Initialize
		 * @return   void
		 * @since    3.8.0
		 * @version  [version]
		 */
		this.init = function() {

			var self = this;

			if ( ! this.is_user_logged_in() ) {
				return;
			}

			window.onbeforeunload = function() {

				if ( dismissals.length ) {
					update();
				}

			};

			bind_events();

			setTimeout( function() {
				do_heartbeat();
				setTimeout( function() {
					start_heartbeat();
				}, heartbeat_delay );
			}, heartbeat_delay );

		};

		/**
		 * Queue notifications to be displayed
		 * @param    object   new_notis  array of notifications
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.queue = function( new_notis ) {

			var self = this;

			for ( var n in new_notis ) {

				if ( ! new_notis.hasOwnProperty( n ) ) {
					continue;
				}

				// add the new notification if it doesnt exist
				if ( false === self.notification_exists( new_notis[ n ].id ) ) {

					notifications.push( new_notis[ n ] );

				}

			}

		};

		/**
		 * Dismiss a notification
		 * @param    obj   $el  notification dom element
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.dismiss = function( $el ) {
			var self = this;
			$el.removeClass( 'visible' );
			add_dismissal( $el.attr( 'data-id' ) );
			setTimeout( function() {
				self.reposition( $el.next( '.llms-notification.visible' ) );
			}, 10 );
		};

		/**
		 * Determine if a notification already exists in the notifications array
		 * @param    int        id  notification id
		 * @return   int|false      index of the notification in the array OR false if not found
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.notification_exists = function( id ) {

			for ( var noti in notifications ) {

				if ( ! notifications.hasOwnProperty( noti ) ) {
					continue;
				}

				if ( id === notifications[ noti ].id ) {
					return noti;
				}

			}

			return false;

		};

		/**
		 * Get the vertiacl offset (on screen) relative to an element
		 * used for notification positiioning
		 * @param    obj   $relative_el  element to get an offset relative to
		 * @return   int
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.get_offset = function( $relative_el ) {

			var spacer = 12;

			if ( ! $relative_el ) {
				$relative_el = $( '.llms-notification.visible' ).last()
			}

			if ( ! $relative_el.offset() ) {
				return 24;
			}

			var top = $relative_el.offset().top,
				height = $relative_el.outerHeight();

			return top + height + spacer;

		};

		/**
		 * Determine if there are notifications to show
		 * @return   Boolean
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.has_notifications = function() {
			return ( notifications.length );
		};

		/**
		 * Determine if a user is logged in
		 * @return   boolean
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.is_user_logged_in = function() {
			return $( 'body' ).hasClass( 'logged-in' );
		};

		/**
		 * Reposition elements, starting with the specified element
		 * @param    obj   $start_el  element to start repositioning with
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.reposition = function( $start_el ) {

			var self = this,
				selector = '.llms-notification.visible',
				$next_el;

			if ( ! $start_el.length ) {
				$start_el = $( selector ).first();
			}

			$start_el.css( 'top', self.get_offset( $start_el.prevAll( selector ).first() ) );

			$next_el = $start_el.next( selector );
			if ( $next_el.length ) {
				setTimeout( function() {
					self.reposition( $next_el );
				}, 150 );
			}


		};

		/**
		 * Show all queued notifications
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.show_all = function() {

			var self = this,
				i = 0,
				interval;

			interval = setInterval( function() {

				if ( i < notifications.length ) {

					if ( ! notifications[ i ].shown ) {
						notifications[ i ].shown = true;
						self.show_one( notifications[ i ] );
					}
					i++;

				} else {

					clearInterval( interval );

				}

			}, 100 );

		}

		/**
		 * Show a single notification
		 * @param    object   n  notification object data
		 * @return   void
		 * @since    3.8.0
		 * @version  [version]
		 */
		this.show_one = function( n ) {

			if ( ! n.html ) {
				add_dismissal( n.id );
				return;
			}

			var self = this,
				$html = $( n.html );

			$html.find( 'a' ).on( 'click', function( e ) {
				e.preventDefault();
				var $this = $( this );
				add_dismissal( $html.attr( 'data-id' ) );
				window.location = $this.attr( 'href' );
			} );

			$( 'body' ).append( $html );
			$html.css( 'top', self.get_offset() );

			setTimeout( function() {
				$html.addClass( 'visible' );
			}, 1 );

			// if it's auto dismissing, set up a dismissal
			if ( $html.attr( 'data-auto-dismiss' ) ) {
				// automatically schedule automatic dismissals
				// to prevent the notification from displaying again
				// if the auto-dismiss timeout isn't reached
				// before the page unloads
				add_dismissal( $html.attr( 'data-id' ) );
				setTimeout( function() {
					self.dismiss( $html );
				}, $html.attr( 'data-auto-dismiss' ) );
			}

		}

		// initalize
		this.init();

		return this;

	};

	window.llms = window.llms || {};
	window.llms.notifications = new llms_notifications();

} )( jQuery );
