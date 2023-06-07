/**
 * Sidebar Utilities View
 *
 * @since 7.2.0
 * @version 7.2.0
 */
define( [], function() {
	return Backbone.View.extend( {

		/**
		 * HTML element selector.
		 *
		 * @type {string}
		 */
		el: '#llms-video-explainer',

		/**
		 * Wrapper Tag name.
		 *
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Events.
		 *
		 * @type {Object}
		 */
		events: {
			'click .llms-video-explainer-trigger': 'openPopup',
			'click .llms-video-explainer-close': 'closePopup',
			'click .llms-video-explainer-wrapper': 'closePopup',
		},

		/**
		 * Get the underscore template.
		 */
		template: wp.template( 'llms-video-explainer-template' ),

		/**
		 * Compiles the template and renders the view.
		 *
		 * @since 7.2.0
		 *
		 * @return {self}
		 */
		render: function() {
			this.$el.html( this.template() );
			return this;
		},

		/**
		 * Open the popup.
		 *
		 * @since 7.2.0
		 *
		 * @param {Object} event JS event object.
		 * @return {void}
		 */
		openPopup: function( event ) {
			event.preventDefault();

			$( '.llms-video-explainer-wrapper' ).css( {
				display: 'flex',
				opacity: '1',
			} );
		},

		/**
		 * Close the popup.
		 *
		 * @since 7.2.0
		 *
		 * @param {Object} event JS event object.
		 * @return {void}
		 */
		closePopup: function( event ) {
			event.preventDefault();

			$( '.llms-video-explainer-wrapper' ).css( {
				display: 'none',
				opacity: '0',
			} );

			const iframe = $( '.llms-video-explainer-iframe' );
			const src = iframe.attr( 'src' );

			iframe.attr( 'src', '' ).attr( 'src', src );
		},

	} );
} );
