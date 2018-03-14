/**
 * Sidebar Elements View
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return Backbone.View.extend( {

		el: '#llms-editor-lesson',

		template: wp.template( 'llms-lesson-settings-template' ),

		initialize: function( data ) {

			this.model = data.lesson;

		},

		render: function() {

			this.$el.html( this.template( this.model ) );

			return this;

		},

	} );

} );
