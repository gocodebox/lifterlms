/**
 * Constructor functions for constructing models, views, and collections
 * @since    3.16.0
 * @version  3.17.1
 */
define( [
		'Collections/loader',
		'Models/loader',
		'Views/_loader'
	], function(
		Collections,
		Models,
		Views
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
		 * @since    3.17.0
		 * @version  3.17.0
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
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.get_model = function( name, data, options ) {

			return get( Models, name, data, options );

		};

		/**
		 * Let 3rd parties extend a view using any of the mixin (_) views
		 * @param    {obj}     view     base object used for the view
		 * @param... {string}  extends  any number of strings that should be mixed into the view
		 * @return   obj
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		this.extend_view = function() {

			var view = arguments[0],
				i = 1;

			while ( arguments[ i ] ) {

				var classname = arguments[ i ];
				if ( Views[ classname ] ) {

					if ( view.events && Views[ classname ].events ) {
						view.events = _.defaults( view.events, Views[ classname ].events );
					}

					view = _.defaults( view, Views[ classname ] );

				}

				i++;
			}

			return Backbone.View.extend( view );

		};

		/**
		 * Allows custom collection registration by extending the default BackBone collection
		 * @param    string   name   model name
		 * @param    obj      props  properties to extend the collection with
		 * @return   void
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		this.register_collection = function( name, props ) {

			Collections[ name ] = Backbone.Collection.extend( props );

		};

		/**
		 * Allows custom model registration by extending the default abstract model
		 * @param    string   name   model name
		 * @param    obj      props  properties to extend the abstract model with
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.register_model = function( name, props ) {

			Models[ name ] = Models['Abstract'].extend( props );

		};

		return this;

	};

} );
