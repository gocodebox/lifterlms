/**
 * Sidebar Utilities View
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-utilities',

		events: {
			'click #llms-collapse-all': 'collapse_all',
			'click #llms-expand-all': 'expand_all'
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-utilities-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			// this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {
			this.$el.html( this.template() );
			return this;
		},

		/**
		 * Collapse all sections
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		collapse_all: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'collapse-all' );
		},

		/**
		 * Expand all sections
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		expand_all: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'expand-all' );
		},

	} );

} );
