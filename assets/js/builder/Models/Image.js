/**
 * Image object model for use in various models for the 'image' attribute
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return Backbone.Model.extend( {

		defaults: {
			enabled: 'no',
			id: '',
			size: 'full',
			src: '',
		},

		initialize: function() {
			this.startTracking();
		},

	} );
} );
