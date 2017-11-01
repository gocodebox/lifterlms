/* global LLMS, $, wp_ajax_data */
/* jshint strict: false */

/**
 * Main Ajax class
 * Handles Primary Ajax connection
 * @since   1.0.0
 * @version [version]
 */
LLMS.Ajax = {

	/**
	 * url
	 * @type {String}
	 */
	url: window.ajaxurl || window.llms.ajaxurl,

	/**
	 * type
	 * @type {[type]}
	 */
	type: 'post',

	/**
	 * data
	 * @type {[type]}
	 */
	data: [],

	/**
	 * cache
	 * @type {[type]}
	 */
	cache: false,

	/**
	 * dataType
	 * defaulted to json
	 * @type {String}
	 */
	dataType: 'json',

	/**
	 * async
	 * default to false
	 * @type {Boolean}
	 */
	async: true,

	response:[],

	/**
	 * initilize Ajax methods
	 * loads class methods
	 * @since    1.0.0
	 * @version  [version]
	 */
	init: function( obj ) {

		var self = this;

		// if obj is not of type object or null return false;
		if ( obj === null || typeof obj !== 'object' ) {
			return false;
		}

		//set object defaults if values are not supplied
		obj.url			= self.url;
		obj.type 		= 'type' 		in obj ? obj.type 		: self.type;
		obj.data 		= 'data' 		in obj ? obj.data 		: self.data;
		obj.cache 		= 'cache' 		in obj ? obj.cache 		: self.cache;
		obj.dataType 	= 'dataType'	in obj ? obj.dataType 	: self.dataType;
		obj.async 		= 'async'		in obj ? obj.async 		: self.async;

		// add extra data when not using REST
		if ( !obj.llms_rest ) {

			// add nonce to data object
			obj.data._ajax_nonce = wp_ajax_data.nonce;

			//add post id to data object
			var $R = LLMS.Rest,
			query_vars = $R.get_query_vars();
			obj.data.post_id = 'post' in query_vars ? query_vars.post : null;
			if ( !obj.data.post_id && $( 'input#post_ID' ).length ) {
				obj.data.post_id = $( 'input#post_ID' ).val();
			}

		// use REST
		} else {

			obj.url = window.llms.rest.url + obj.llms_rest_endpoint;

			var before = obj.beforeSend;

			obj.beforeSend = function( xhr, settings ) {
				xhr.setRequestHeader( 'X-WP-Nonce', window.llms.rest.nonce );
				if ( 'function' === typeof before ) {
					return before( xhr, settings );
				}
			};

		}

		return obj;
	},

	/**
	 * Call
	 * Called by external classes
	 * Sets up jQuery Ajax object
	 * @param  {[object]} [object of ajax settings]
	 * @return {[mixed]} [false if not object or this]
	 */
	call: function(obj) {

		//get default variables if not included in call
		var settings = this.init(obj);

		//if init return a response of false
		if (!settings) {
			return false;
		} else {
			this.request(settings);
		}

		return this;

	},

	/**
	 * Calls jQuery Ajax on settings object
	 * @return {[object]} [this]
	 */
	request: function(settings) {

		$.ajax(settings);

		return this;

	}

};
