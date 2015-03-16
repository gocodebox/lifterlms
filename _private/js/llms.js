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

	LLMS.init($);

	

})(jQuery);