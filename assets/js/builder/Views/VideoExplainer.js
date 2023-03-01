/**
 * Sidebar Utilities View
 *
 * @since    7.0.1
 * @version  7.0.1
 */
define( [], function() {
	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 *
		 * @type  {string}
		 */
		el: '#llms-video-explainer',

		/**
		 * Wrapper Tag name
		 *
		 * @type  {string}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 *
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-video-explainer-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 *
		 * @return   void
		 * @since    7.0.1
		 * @version  7.0.1
		 */
		initialize() {

			// this.render();
		},

		/**
		 * Compiles the template and renders the view
		 *
		 * @return   self (for chaining)
		 * @since    7.0.1
		 * @version  7.0.1
		 */
		render() {
			this.$el.html( this.template() );
			return this;
		},

	} );
} );
