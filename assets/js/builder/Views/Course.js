/**
 * Single Course View
 * @since    3.13.0
 * @version  3.14.0
 */
define( [ 'Mixins/EditableView' ], function( Editable ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-lesson',

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-course-info',

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		id: function() {
			return 'llms-course-' + this.model.id;
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
		template: wp.template( 'llms-course-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {
			this.render();
			this.listenTo( this.model, 'sync', this.render );
		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

	}, Editable ) );

} );
