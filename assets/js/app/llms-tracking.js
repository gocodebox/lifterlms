/* global LLMS, $ */

/**
 * User event/interaction tracking.
 *
 * @since 3.36.0
 * @since 3.36.2 Fix JS error when settings aren't loaded.
 */
LLMS.Tracking = function( settings ) {

	var self = this,
		store = new LLMS.Storage( 'llms-tracking' );

	settings = 'string' === typeof settings ? JSON.parse( settings ) : settings;

	/**
	 * Initialize / Bind all tracking event listeners.
	 *
	 * @since 3.36.0
	 *
	 * @return {void}
	 */
	function init() {

		// Set the nonce for server-side verification.
		store.set( 'nonce', settings.nonce );

		self.addEvent( 'page.load' );

		window.addEventListener( 'beforeunload', onBeforeUnload );
		window.addEventListener( 'unload', onUnload );

		document.addEventListener( 'visibilitychange', onVisibilityChange );

	};

	/**
	 * Add an event.
	 *
	 * @since 3.36.0
	 * @since 3.36.2 Fix error when settings aren't loaded.
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

		event = self.makeEventObj( args );

		var all = store.get( 'events', [] );
		all.push( event );
		store.set( 'events', all );

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
		return Object.assign( event, {
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
