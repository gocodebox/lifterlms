/**
 * Popover View
 *
 * @since 3.16.0
 * @version 4.0.0
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * Default Properties
		 *
		 * @type {Object}
		 */
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
		 *
		 * @type {String}
		 */
		tagName: 'div',

		/**
		 * Initialization callback func (renders the element on screen)
		 *
		 * @since 3.14.1
		 * @since 4.0.0 Add RTL support for popovers.
		 *
		 * @return void
		 */
		initialize: function( data ) {

			if ( this.$el.length ) {
				this.defaults.container = this.$el.parent();
			}

			this.args = _.defaults( data.args, this.defaults );

			// Reverse directions for RTL sites.
			if ( $( 'body' ).hasClass( 'rtl' ) ) {

				if ( -1 !== this.args.placement.indexOf( 'left' ) ) {
					this.args.placement = this.args.placement.replace( 'left', 'right' );
				} else if ( -1 !== this.args.placement.indexOf( 'right' ) ) {
					this.args.placement = this.args.placement.replace( 'right', 'left' );
				}

			}

			this.render();

		},

		/**
		 * Compiles the template and renders the view
		 *
		 * @since 3.16.0
		 *
		 * @return {Object} Instance of the Backbone.view.
		 */
		render: function() {

			this.$el.webuiPopover( this.args );
			return this;

		},

		/**
		 * Hide the popover
		 *
		 * @since 3.16.0
		 * @since 3.16.12 Unknown.
		 *
		 * @return {Object} Instance of the Backbone.view.
		 */
		hide: function() {

			this.$el.webuiPopover( 'hide' );
			return this;

		},

		/**
		 * Show the popover
		 *
		 * @since 3.16.0
		 * @since 3.16.12 Unknown.
		 *
		 * @return {Object} Instance of the Backbone.view.
		 */
		show: function() {

			this.$el.webuiPopover( 'show' );
			return this;

		},

	} );

} );
