/* global LLMS, $ */

/**
 * User event/interaction tracking.
 *
 * @since 3.36.0
 * @since 3.36.2 Fix JS error when settings aren't loaded.
 * @since 3.37.2 When adding an event to the storae also make sure the nonce is set for server-side verification.
 * @since 3.37.9 Fix IE compatibility issue related to usage of `Object.assign()`.
 * @since 3.37.14 Persist the tracking events via ajax when reaching the cookie size limit.
 * @since 5.0.0 Set `settings` as an empty object when no settings supplied.
 * @since 7.1.0 Only attempt to add a nonce to the datastore when a nonce exists in the settings object.
 */
LLMS.Tracking = function( settings ) {

	settings = settings || {};

	var self = this,
		store = new LLMS.Storage( 'llms-tracking' );

	settings = 'string' === typeof settings ? JSON.parse( settings ) : settings;

	/**
	 * Initialize / Bind all tracking event listeners.
	 *
	 * @since 3.36.0
	 * @since 5.0.0 Only attempt to add a nonce to the datastore when a nonce exists in the settings object.
	 * @since 7.1.0 Do not add a nonce to the datastore by default, will be added/updated
	 *              when storing an event to track.
	 *
	 * @return {void}
	 */
	function init() {

		self.addEvent( 'page.load' );

		window.addEventListener( 'beforeunload', onBeforeUnload );
		window.addEventListener( 'pagehide', onUnload );

		document.addEventListener( 'visibilitychange', onVisibilityChange );

	};

	/**
	 * Add an event.
	 *
	 * @since 3.36.0
	 * @since 3.36.2 Fix error when settings aren't loaded.
	 * @since 3.37.2 Always make sure the nonce is set for server-side verification.
	 * @since 3.37.14 Persist the tracking events via ajax when reaching the cookie size limit.
	 * @since 7.1.0 Only attempt to add a nonce to the datastore when a nonce exists in the settings object.
	 *
	 * @param string|obj event Event Id (type.event) or a full event object from `this.makeEventObj()`.
	 * @param int args Optional additional arguments to pass to `this.makeEventObj()`.
	 * @return {void}
	 */
	this.addEvent = function( event, args ) {

		args  = args || {};
		if ( 'string' === typeof event ) {
			args.event = event;
		}

		// If the event isn't registered in the settings don't proceed.
		if ( !settings.events || -1 === settings.events.indexOf( args.event ) ) {
			return;
		}

		// Make sure the nonce is set for server-side verification.
		if ( settings.nonce ) {
			store.set( 'nonce', settings.nonce );
		}

		event = self.makeEventObj( args );

		var all = store.get( 'events', [] );
		all.push( event );
		store.set( 'events', all );

		// If couldn't store the latest event because of size limits.
		if ( all.length > store.get( 'events', [] ).length ) {

			// Copy the cookie in a temporary variable.
			var _temp = store.getAll();
			// Clear the events from the cookie.
			store.clear('events');

			// Add the latest event to the temporary variable.
			_temp['events'].push( event );

			// Send the temporary variable as string via ajax.
			LLMS.Ajax.call( {
				data: {
					action: 'persist_tracking_events',
					'llms-tracking': JSON.stringify(_temp)
				},

				error: function( xhr, status, error ) {

					console.log( xhr, status, error );

				},
				success: function( r ) {

					if ( 'error' === r.code ) {
						console.log(r.code, r.message);
					}

				}

			} );

		}

	}

	/**
	 * Retrieve initialization settings.
	 *
	 * @since 3.36.0
	 *
	 * @return obj
	 */
	this.getSettings = function() {
		return settings;
	}

	/**
	 * Create an event object suitable to save as an event.
	 *
	 * @since 3.36.0
	 * @since 3.37.9 Use `$.extend()` in favor of `Object.assign()`.
	 *
	 * @param obj event {
	 *     Event hash
	 *
	 *     @param {string} event (Required) Event ID, eg: "page.load".
	 *     @param {url} url Event URL. (Optional, added automatically) Stored as metadata and used to infer an object_id for post events.
	 *     @param {time} float (Optional, added automatically) Timestamp (in milliseconds). Used for the event creation date.
	 *     @param {int} obj_id (Optional). The object ID. Inferred automatically via `url` if not provided.
	 *     @param {obj} meta (Optional) Hash of metadata to store with the event.
	 * }
	 * @return obj
	 */
	this.makeEventObj = function( event ) {
		return $.extend( event, {
			url: window.location.href,
			time: Math.round( new Date().getTime() / 1000 ),
		} );
	}


	/**
	 * Remove the visibility change event listener on window.beforeunload
	 *
	 * Prevents actual unloading from recording a blur event from the visibility change listener
	 *
	 * @param obj e JS event object.
	 * @return void
	 */
	function onBeforeUnload( e ) {
		document.removeEventListener( 'visibilitychange', onVisibilityChange );
	}

	/**
	 * Record a `page.exit` event on window.unload.
	 *
	 * @since 3.36.0
	 *
	 * @param obj e JS event object.
	 * @return void
	 */
	function onUnload( e ) {
		self.addEvent( 'page.exit' );
	}

	/**
	 * Record `page.blur` and `page.focus` events via document.visilibitychange events.
	 *
	 * @since 3.36.0
	 *
	 * @param obj e JS event object.
	 * @return void
	 */
	function onVisibilityChange( e ) {

		var event = document.hidden ? 'page.blur' : 'page.focus';
		self.addEvent( event );

	}

	// Initialize on the frontend only.
	if ( ! $( 'body' ).hasClass( 'wp-admin' ) ) {
		init();
	}

};

llms.tracking = new LLMS.Tracking( llms.tracking );
