;( function( $ ) {

	var llms_notifications = function() {

		var self = this,
			heartbeat_interval = 10000,
			notifications = [],
			dismissals = [],
			heartbeat;

		function bind_events() {
			$( 'body' ).on( 'click', '.llms-notification', function() {
				self.dismiss( $( this ) );
			} );
		};

		function clear_heartbeat() {
			clearInterval( heartbeat );
		};

		function do_heartbeat() {
			pump( function() {
				if ( ! self.has_notifications ) {
					return;
				}
				self.show_all();
			} );
		};

		pump = function( cb ) {

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

					dismissals = [];

					if ( r.success && r.data ) {
						self.queue( r.data.new );
					}


					cb();

				}
			} );

		};

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

		this.init = function() {

			var self = this;

			if ( ! this.is_user_logged_in() ) {
				return;
			}

			window.onbeforeunload = function() {
				do_heartbeat();
			};

			bind_events();

			do_heartbeat();
			start_heartbeat();

		};


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

		this.dismiss = function( $el ) {
			var self = this;
			$el.removeClass( 'visible' );
			dismissals.push( $el.attr( 'data-id' ) );
			setTimeout( function() {
				self.reposition( $el.next( '.llms-notification.visible' ) );
			}, 10 );
		};

		/**
		 * Determine if a notification already exists in the notifications array
		 * @param    int        id  notification id
		 * @return   int|false      index of the notification in the array OR false if not found
		 * @since    [version]
		 * @version  [version]
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

		this.get_offset = function( $relative_el ) {

			var spacer = 14;

			if ( ! $relative_el ) {
				$relative_el = $( '.llms-notification.visible' ).last()
			}

			if ( ! $relative_el.offset() ) {
				return 48;
			}

			var top = $relative_el.offset().top,
				height = $relative_el.outerHeight();

			return top + height + spacer;

		};

		this.get_html = function( n ) {

			var $html = $( '<div class="llms-notification llms-notification--' + n.type + '" data-id="' + n.id + '">' );

			if ( n.icon ) {
				$html.append( '<img class="llms-notification-icon" alt="' + n.title + '" src="' + n.icon + '">' );
			}

			if ( n.title ) {
				$html.append( '<h4 class="llms-notification-title">' + n.title + '</h4>' );
			}

			if ( n.body ) {
				$html.append( '<div class="llms-notification-body">' + n.body + '</div>' );
			}

			$html.append( '<i class="llms-notification-dismiss fa fa-times-circle" aria-hidden="true"></i>')

			return $html;

		};

		/**
		 * Determine if there are notifications to show
		 * @return   Boolean
		 * @since    [version]
		 * @version  [version]
		 */
		this.has_notifications = function() {
			return ( notifications.length );
		};

		/**
		 * Determine if a user is logged in
		 * @return   boolean
		 * @since    [version]
		 * @version  [version]
		 */
		this.is_user_logged_in = function() {
			return $( 'body' ).hasClass( 'logged-in' );
		};

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

		this.show_one = function( n ) {

			var self = this,
				$html = this.get_html( n );

			$( 'body' ).append( $html );
			$html.css( 'top', self.get_offset() );

			setTimeout( function() {
				$html.addClass( 'visible' );
			}, 1 );

		}

		// initalize
		this.init();

		return this;

	};

	window.llms = window.llms || {};
	window.llms.notifications = new llms_notifications();

} )( jQuery );
