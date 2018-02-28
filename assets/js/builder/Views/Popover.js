/**
 * Single Quiz View
 * @since    3.16.0
 * @version  3.16.0
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
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.webuiPopover( this.args );
			return this;

		},

		/**
		 * Hide the popover
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		hide: function() {

			this.$el.webuiPopover( 'hide' );
			return this;

		},

		/**
		 * Show the popover
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		show: function() {

			this.$el.webuiPopover( 'show' );
			return this;

		},

	} );

} );
