/**
 * Main Syncing
 * Used with models and collections for all CRUD operations
 * @type  {Object}
 * @since    3.13.0
 * @version  3.13.0
 */
define( [], function() {

	return {

		url: ajaxurl,
		action: 'llms_builder',

		/**
		 * triggers AJAX call to CRUD
		 * @param    {string}   method   request method [read,create,update,delete]
		 * @param    {obj}      object   model or collection being synced
		 * @param    {obj}      options  optional AJAX options
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		sync: function( method, object, options ) {

			if ( typeof options.data === 'undefined' ) {
				options.data = {};
			}

			if ( object instanceof Backbone.Model ) {
				object_type = 'model';
				// console.log( object.hasChanged(), object.changed );
			} else if ( object instanceof Backbone.Collection ) {
				object_type = 'collection';
				// console.log( object );
				// object.each( function( model ) {
				// 	console.log( model.hasChanged(), model.changed );
				// } );
			}

			options.data.course_id = window.llms_builder.course.id;
			options.data.action_type = method;
			options.data.object_type = object_type; // eg collection or model
			options.data.data_type = object.type_id; // eg section or lesson
			options.data._ajax_nonce = wp_ajax_data.nonce;

			if ( undefined === options.data.action && undefined !== this.action ) {
				options.data.action = this.action;
			}

			if ( 'read' === method ) {
				return Backbone.sync( method, object, options );
			}

			var json = this.toJSON();
			var formattedJSON = {};

			if ( json instanceof Array ) {
				formattedJSON.models = json;
			} else {
				formattedJSON.model = json;
			}

			_.extend( options.data, formattedJSON );

			options.emulateJSON = true;

			return Backbone.sync.call( this, 'create', object, options );

		}
	};

} );
