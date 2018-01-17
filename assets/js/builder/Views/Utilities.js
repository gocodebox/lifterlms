/**
 * Sidebar Utilities View
 * @since    [version]
 * @version  [version]
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
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			// this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {
			this.$el.html( this.template() );
			return this;
		},

		/**
		 * Collapse all sections
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		collapse_all: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'collapse-all' );
		},

		/**
		 * Expand all sections
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		expand_all: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'expand-all' );
		},

	} );

} );
