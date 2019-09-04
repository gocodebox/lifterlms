/**
 * Main Ajax class
 * Handles Primary Ajax connection
 *
 * @package LifterLMS/Scripts
 *
 * @since Unknown
 * @version Unknown
 */

LLMS.Ajax = {

	/**
	 * Url
	 *
	 * @type {String}
	 */
	url: window.ajaxurl || window.llms.ajaxurl,

	/**
	 * Type
	 *
	 * @type {[type]}
	 */
	type: 'post',

	/**
	 * Data
	 *
	 * @type {[type]}
	 */
	data: [],

	/**
	 * Cache
	 *
	 * @type {[type]}
	 */
	cache: false,

	/**
	 * DataType
	 * defaulted to json
	 *
	 * @type {String}
	 */
	dataType: 'json',

	/**
	 * Async
	 * default to false
	 *
	 * @type {Boolean}
	 */
	async: true,

	response:[],

	/**
	 * Initialize Ajax methods
	 * loads class methods
	 */
	init: function(obj) {

		// if obj is not of type object or null return false;
		if ( obj === null || typeof obj !== 'object' ) {
			return false;
		}

		// set object defaults if values are not supplied
		obj.url      = 'url'         in obj ? obj.url : this.url;
		obj.type     = 'type' 		in obj ? obj.type : this.type;
		obj.data     = 'data' 		in obj ? obj.data : this.data;
		obj.cache    = 'cache' 		in obj ? obj.cache : this.cache;
		obj.dataType = 'dataType'	in obj ? obj.dataType : this.dataType;
		obj.async    = 'async'		in obj ? obj.async : this.async;

		// add nonce to data object
		obj.data._ajax_nonce = wp_ajax_data.nonce;

		// add post id to data object
		var $R           = LLMS.Rest,
		query_vars       = $R.get_query_vars();
		obj.data.post_id = 'post' in query_vars ? query_vars.post : null;
		if ( ! obj.data.post_id && $( 'input#post_ID' ).length ) {
			obj.data.post_id = $( 'input#post_ID' ).val();
		}

		return obj;
	},

	/**
	 * Call
	 * Called by external classes
	 * Sets up jQuery Ajax object
	 *
	 * @param  {[object]} [object of ajax settings]
	 * @return {[mixed]} [false if not object or this]
	 */
	call: function(obj) {

		// get default variables if not included in call
		var settings = this.init( obj );

		// if init return a response of false
		if ( ! settings) {
			return false;
		} else {
			this.request( settings );
		}

		return this;

	},

	/**
	 * Calls jQuery Ajax on settings object
	 *
	 * @return {[object]} [this]
	 */
	request: function(settings) {

		$.ajax( settings );

		return this;

	}

};
