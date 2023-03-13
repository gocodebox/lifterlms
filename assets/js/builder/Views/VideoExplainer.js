/**
 * Sidebar Utilities View
 *
 * @since [version]
 * @version [version]
 */
define( [], function() {
	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 *
		 * @type {string}
		 */
		el: '#llms-video-explainer',

		/**
		 * Wrapper Tag name
		 *
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 *
		 * @type {Function}
		 */
		template: wp.template( 'llms-video-explainer-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 *
		 * @since [version]
		 * @version [version]
		 * @type {Function}
		 * @return {void}
		 */
		initialize: function() {
			// this.render();
		},

		/**
		 * Compiles the template and renders the view
		 *
		 * @since [version]
		 * @version [version]
		 * @type {Function}
		 * @return {self}
		 */
		render: function() {
			this.$el.html( this.template() );
			return this;
		},

	} );
} );
