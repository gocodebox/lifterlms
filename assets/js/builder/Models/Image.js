/**
 * Image object model for use in various models for the 'image' attribute
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return Backbone.Model.extend( {

		defaults: {
			enabled: 'no',
			id: '',
			size: 'full',
			src: '',
		},

	} );
} );
