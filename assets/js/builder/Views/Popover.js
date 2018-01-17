/**
 * Single Quiz View
 * @since    3.13.0
 * @version  [version]
 */
define( [], function() {

	return Backbone.View.extend( {

		defaults: {
			placement: 'auto',
			// container: document.body,
			width: 'auto',
			trigger: 'manual',
			style: 'light',
			animation: 'pop',
			title: '',
			content: '',
			closeable: false,
			backdrop: false,
			onShow: function( $el ) {},
			onHide: function( $el ) {},
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function( data ) {

			if ( this.$el.length ) {
				this.defaults.container = this.$el.parent();
			}

			this.args = _.defaults( data.args, this.defaults );
			this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {

			this.$el.webuiPopover( this.args );
			return this;

		},

		hide: function() {

			this.$el.webuiPopover( 'hide' );

		},

		show: function() {

			this.$el.webuiPopover( 'show' );

		},

	} );

} );
