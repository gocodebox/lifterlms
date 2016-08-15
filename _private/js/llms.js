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
	//= include /app/*.js

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
	 * our asset enqueue is all screwed up and I'm too tired to fix it
	 * so we're going to run this little dependency check
	 * and wait for matchHeight to be available before binding
	 *
	 * @param    {Function}  cb  callback function to run when matchheight is ready
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	LLMS.wait_for_matchHeight = function( cb ) {

		var counter = 0,
			interval;

		interval = setInterval( function() {

			// if we get to 30 seconds log an error message
			// and really who cares if the element heights aren't matched
			if ( counter >= 300 ) {

				console.log( 'cannot match access plan item heights.');

			// if we can't access ye, increment and wait...
			} else if ( 'undefined' === typeof $.fn.matchHeight ) {

				counter++;
				return;

			// bind the events, we're good!
			} else {

				cb();

			}

			clearInterval( interval );

		}, 100 );
	}

	LLMS.init($);



})(jQuery);
