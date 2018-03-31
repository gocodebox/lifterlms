/**
 * Utility functions for Models
 * @since    3.16.0
 * @version  3.17.1
 */
define( [], function() {

	return {

		fields: [],

		/**
		 * Retrieve the edit post link for the current model
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_edit_post_link: function() {

			if ( this.has_temp_id() ) {
				return '';
			}

			return window.llms_builder.admin_url + 'post.php?post=' + this.get( 'id' ) + '&action=edit';

		},

		/**
		 * Retrieve schema fields defined for the model
		 * @return   object
		 * @since    3.17.0
		 * @version  3.17.1
		 */
		get_settings_fields: function() {

			var schema = this.schema || {};
			return window.llms_builder.schemas.get( schema, this.get( 'type' ).replace( 'llms_', '' ), this );

		},

		/**
		 * Determine if the model has a temporary ID
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		has_temp_id: function() {

			return ( ! _.isNumber( this.get( 'id' ) ) && 0 === this.get( 'id' ).indexOf( 'temp_' ) );

		},

		/**
		 * Initializes 3rd party custom schema (field) data for a model
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		init_custom_schema: function() {

			var groups = _.filter( this.get_settings_fields(), function( group ) {
				return ( group.custom );
			} );

			_.each( groups, function( group ) {
				_.each( _.flatten(  group.fields ), function( field ) {


					var keys = [ field.attribute ],
						customs = this.get( 'custom' );

					if ( field.switch_attribute ) {
						keys.push( field.switch_attribute );
					}

					_.each( keys, function( key ) {
						var attr = field.attribute_prefix ? field.attribute_prefix + key : key;
						if ( customs && customs[ attr ] ) {
							this.set( key, customs[ attr ][0] );
						}
					}, this );

				}, this );
			}, this );

		},

	};

} );
