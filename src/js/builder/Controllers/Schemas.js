/**
 * Model schema functions
 *
 * @since    3.17.0
 * @version  3.17.0
 */
define( [], function() {

	/**
	 * Main Schemas class
	 *
	 * @param    obj   schemas  schemas definitions initialized via PHP filters
	 * @return   obj
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	return function( schemas ) {

		// initialize any custom schemas defined via PHP
		var custom_schemas = schemas;
		_.each( custom_schemas, function( type ) {
			_.each( type, function( schema ) {
				schema.custom = true;
			} );
		} );

		/**
		 * Retrieve a schema for a given model by type
		 * Extends default schemas definitions with custom 3rd party definitions
		 *
		 * @param    obj      schema      default schema definition from the model (or empty object if none defined)
		 * @param    string   model_type  the model type ('lesson', 'quiz', etc)
		 * @param    obj      model       Instance of the Backbone.Model for the given model
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.get = function( schema, model_type, model ) {

			// extend the default schema with custom php schemas for the type if they exist
			if ( custom_schemas[ model_type ] ) {
				schema = _.extend( schema, custom_schemas[ model_type ] );
			}

			return schema;

		};

		return this;

	};

} );
