/**
 * Main LLMS Namespace
 *
 * @since 1.0.0
 * @version 5.3.3
 */

var LLMS = window.LLMS || {};
( function( $ ){

	'use strict';

	/**
	 * Load all app modules
	 */
	// = include ../app/*.js

	// = include ../llms-spinner.js

	/**
	 * Initializes all classes within the LLMS Namespace
	 *
	 * @since Unknown
	 *
	 * @return {void}
	 */
	LLMS.init = function() {

		for (var func in LLMS) {

			if ( typeof LLMS[func] === 'object' && LLMS[func] !== null ) {

				if ( LLMS[func].init !== undefined ) {

					if ( typeof LLMS[func].init === 'function') {
						LLMS[func].init();
					}

				}

			}

		}

	};

	/**
	 * Determine if the current device is touch-enabled
	 *
	 * @since 3.24.3
	 *
	 * @see {@link https://stackoverflow.com/a/4819886/400568}
	 *
	 * @return {Boolean} Whether or not the device is touch-enabled.
	 */
	LLMS.is_touch_device = function() {

		var prefixes = ' -webkit- -moz- -o- -ms- '.split( ' ' );
		var mq       = function( query ) {
			return window.matchMedia( query ).matches;
		}

		if ( ( 'ontouchstart' in window ) || window.DocumentTouch && document instanceof DocumentTouch ) {
			return true;
		}

		/**
		 * Include the 'heartz' as a way to have a non matching MQ to help terminate the join.
		 *
		 * @see {@link https://git.io/vznFH}
		 */
		var query = ['(', prefixes.join( 'touch-enabled),(' ), 'heartz', ')'].join( '' );
		return mq( query );

	};

	/**
	 * Wait for matchHeight to load
	 *
	 * @since 3.0.0
	 * @since 3.16.6 Unknown.
	 * @since 5.3.3 Pass a dependency name to `wait_for()`.
	 *
	 * @param {Function} cb Callback function to run when matchheight is ready.
	 * @return {void}
	 */
	LLMS.wait_for_matchHeight = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.matchHeight );
		}, cb, 'matchHeight' );
	}

	/**
	 * Wait for webuiPopover to load
	 *
	 * @since 3.9.1
	 * @since 3.16.6 Unknown.
	 *
	 * @param {Function} cb Callback function to run when matchheight is ready.
	 * @return {void}
	 */
	LLMS.wait_for_popover = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.webuiPopover );
		}, cb, 'webuiPopover' );
	}

	/**
	 * Wait for a dependency to load and then run a callback once it has
	 *
	 * Temporary fix for a less-than-optimal assets loading function on the PHP side of things.
	 *
	 * @since 3.9.1
	 * @since 5.3.3 Added optional `name` parameter.
	 *
	 * @param {Function} test A function that returns a truthy if the dependency is loaded.
	 * @param {Function} cb   A callback function executed once the dependency is loaded.
	 * @param {string}   name The dependency name.
	 * @return {void}
	 */
	LLMS.wait_for = function( test, cb, name ) {

		var counter = 0,
			interval;

		name = name ? name : 'unnamed';

		interval = setInterval( function() {

			// If we get to 30 seconds log an error message.
			if ( counter >= 300 ) {

				console.log( 'Unable to load dependency: ' + name );

				// If we can't access yet, increment and wait...
			} else {

				// Bind the events, we're good!
				if ( test() ) {
					cb();
				} else {
					// console.log( 'Waiting for dependency: ' + name );
					counter++;
					return;
				}

			}

			clearInterval( interval );

		}, 100 );

	};

	LLMS.init( $ );

} )( jQuery );
