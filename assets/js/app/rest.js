/**
 * Rest Methods
 * Manages URL and Rest object parsing
 *
 * @package LifterLMS/Scripts
 *
 * @since Unknown
 * @version  Unknown
 */

LLMS.Rest = {

	/**
	 * Init
	 * loads class methods
	 */
	init: function() {
		this.bind();
	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 *
	 * @return {[type]} [description]
	 */
	bind: function() {
	},

	/**
	 * Searches for string matches in url path
	 *
	 * @param  {Array}  strings [Array of strings to search for matches]
	 * @return {Boolean}         [Was a match found?]
	 */
	is_path: function( strings ) {

		var path_exists = false,
			url         = window.location.href;

		for ( var i = 0; i < strings.length; i++ ) {

			if ( url.search( strings[i] ) > 0 && ! path_exists ) {

				path_exists = true;
			}
		}

		return path_exists;
	},

	/**
	 * Retrieves query variables
	 *
	 * @return {[Array]} [array object of query variable key=>value pairs]
	 */
	get_query_vars: function() {

		var vars   = [], hash,
			hashes = window.location.href.slice( window.location.href.indexOf( '?' ) + 1 ).split( '&' );

		for (var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split( '=' );
			vars.push( hash[0] );
			vars[hash[0]] = hash[1];
		}

		return vars;
	}

};
