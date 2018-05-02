/**
 * Main LLMS Namespace
 * @type {[type]}
 */
var LLMS = window.LLMS || {};
(function($){
	'use strict';
	/**
	 * load all app modules
	 */
	//= include ../app/*.js

	/**
	 * Initializes all classes within the LLMS Namespace
	 * @return {[type]} [description]
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
	 * Wait for matchHeight to load
	 * @param    {Function}  cb  callback function to run when matchheight is ready
	 * @return   void
	 * @since    3.0.0
	 * @version  3.16.6
	 */
	LLMS.wait_for_matchHeight = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.matchHeight );
		}, cb );
	}

	/**
	 * Wait for webuiPopover to load
	 * @param    {Function}  cb  callback function to run when matchheight is ready
	 * @return   void
	 * @since    3.9.1
	 * @version  3.16.6
	 */
	LLMS.wait_for_popover = function( cb ) {
		this.wait_for( function() {
			return ( undefined !== $.fn.webuiPopover );
		}, cb );
	}

	/**
	 * Wait for a dependency to load and then run a callback once it has
	 * Temporary fix for a less-than-optimal assets loading function on the PHP side of things
	 * @param    {Function}    test  a function that returns a truthy if the dependency is loaded
	 * @param    {Function}    cb    a callback function executed once the dependency is loaded
	 * @return   void
	 * @since    3.9.1
	 * @version  3.9.1
	 */
	LLMS.wait_for = function( test, cb ) {

		var counter = 0,
			interval;

		interval = setInterval( function() {

			// if we get to 30 seconds log an error message
			if ( counter >= 300 ) {

				console.log( 'could not load dependency' );

			// if we can't access ye, increment and wait...
			} else {

				// bind the events, we're good!
				if ( test() ) {

					cb();

				} else {

					console.log( 'waiting...' );
					counter++;
					return;

				}

			}

			clearInterval( interval );

		}, 100 );

	};

	LLMS.init($);


})(jQuery);
