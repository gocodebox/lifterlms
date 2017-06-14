/**
 * LifterLMS Basic Notifications Displayer
 * @since    3.8.0
 * @version  3.9.5
 */
;( function( $ ) {

	var llms_notifications = function() {

		var self = this,
			settings = ( window.llms && window.llms.notification_settings ) ? window.llms.notification_settings : {},
			heartbeat_interval = settings.heartbeat_interval ? settings.heartbeat_interval : 20000,
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
		 * @version  3.8.0
		 */
		function add_dismissal( id ) {
			if ( -1 === dismissals.indexOf( id ) ) {
				dismissals.push( id );
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
		 * @param    {[type]}   trigger  [description]
		 * @return   {[type]}            [description]
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		function do_heartbeat( trigger ) {

			pump( function() {

				if ( ( trigger && 'unload' === trigger ) || ! self.has_notifications ) {
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
		 * @version  3.8.0
		 */
		pump = function( cb ) {

			var clear_dismissals = dismissals.length ? true : false;

			LLMS.Ajax.call( {
				data: {
					action: 'notifications_heartbeart',
					dismissals: dismissals,
				},
				beforeSend: function() {

					if ( self.block_ajax ) {
						self.restart_heartbeat = true;
						clear_heartbeat();
						cb();
						return false;
					}

					self.block_ajax = true;

				},
				complete: function() {

					if ( self.restart_heartbeat ) {
						self.restart_heartbeat = false;
						start_heartbeat();
					}

					self.block_ajax = false;

				},
				success: function( r ) {

					dismissals = clear_dismissals ? [] : dismissals;

					if ( r.success && r.data ) {
						self.queue( r.data.new );
					}

					cb();

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
		 * Initialize
		 * @return   void
		 * @since    3.8.0
		 * @version  3.8.0
		 */
		this.init = function() {

			var self = this;

			if ( ! this.is_user_logged_in() ) {
				return;
			}

			window.onbeforeunload = function() {
				do_heartbeat( 'unload' );
			};

			bind_events();

			do_heartbeat();
			start_heartbeat();

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
		 * @version  3.8.0
		 */
		this.show_one = function( n ) {

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
