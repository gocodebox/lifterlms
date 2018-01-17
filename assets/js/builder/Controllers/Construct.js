/**
 * Lessons Collection
 * @since    3.13.0
 * @version  [version]
 */
define( [
		'Collections/loader',
		'Models/loader',
	], function(
		Collections,
		Models
	) {

	return function() {

		/**
		 * Internal getter
		 * Constructs new Collections, Models, and Views
		 * @param    obj      type     type of object to construct [Collection,Model,View]
		 * @param    string   name     name of the object to construct
		 * @param    obj      data     object data to pass into the object's constructor
		 * @param    obj      options  object options to pass into the constructor
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		function get( type, name, data, options ) {

			if ( ! type[ name ] ) {
				console.log( ' "' + name + '" not found.' );
				return false;
			}

			return new type[ name ]( data, options );

		}

		this.get_collection = function( name, data, options ) {

			return get( Collections, name, data, options );

		};

		this.get_model = function( name, data, options ) {

			return get( Models, name, data, options );

		};

		return this;

	};

} );
