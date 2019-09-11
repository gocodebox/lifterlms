/* global LLMS, $ */

// = include ../vendor/js.cookie.js

/**
 * Store information in Local Storage by group.
 *
 * @since [version]
 *
 * @param string group Storage group id/name.
 */
LLMS.Storage = function( group ) {

	var self = this,
		store = Cookies.noConflict();

	/**
	 * Clear all data for the group.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	this.clearAll = function() {
		store.remove( group );
	};

	/**
	 * Clear a single item from the group by key.
	 *
	 * @since [version]
	 *
	 * @return obj
	 */
	this.clear = function( key ) {
		var data = self.getAll();
		delete data[ key ];
		return store.set( group, data );
	};

	/**
	 * Retrieve (and parse) all data stored for the group.
	 *
	 * @since [version]
	 *
	 * @return obj
	 */
	this.getAll = function() {
		return store.getJSON( group ) || {};
	}

	/**
	 * Retrieve an item from the group by key.
	 *
	 * @since [version]
	 *
	 * @param string key Item key/name.
	 * @param mixed default_val Item default value to be returned when item not found in the group.
	 * @return mixed
	 */
	this.get = function( key, default_val ) {
		var data = self.getAll();
		return data[ key ] ? data[ key ] : default_val;
	}

	/**
	 * Store an item in the group by key.
	 *
	 * @since [version]
	 *
	 * @param string key Item key name.
	 * @param mixed val Item value
	 * @return obj
	 */
	this.set = function( key, val ) {
		var data = self.getAll();
		data[ key ] = val;
		return store.set( group, data );
	};

}
