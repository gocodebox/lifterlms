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
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function get( type, name, data, options ) {

			if ( ! type[ name ] ) {
				console.log( '"' + name + '" not found.' );
				return false;
			}

			return new type[ name ]( data, options );

		}

		/**
		 * Instantiate a collection
		 * @param    string   name     Collection class name (EG: "Sections")
		 * @param    array    data     Array of model objects to pass to the constructor
		 * @param    obj      options  Object of options to pass to the constructor
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		this.get_collection = function( name, data, options ) {

			return get( Collections, name, data, options );

		};

		/**
		 * Instantiate a model
		 * @param    string   name     Model class name (EG: "Section")
		 * @param    obj      data     Object of model attributes to pass to the constructor
		 * @param    obj      options  Object of options to pass to the constructor
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		this.get_model = function( name, data, options ) {

			return get( Models, name, data, options );

		};

		/**
		 * Allows custom model registration by extending the default abstract model
		 * @param    string   name   model name
		 * @param    obj      props  properties to extend the abstract model with
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		this.register_model = function( name, props ) {
			Models[ name ] = Models['Abstract'].extend( props );
		};

		return this;

	};

} );
