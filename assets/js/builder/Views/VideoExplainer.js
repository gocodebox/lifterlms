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
		 * Events
		 *
		 * @type {Object}
		 */
		events: {
			'click .llms-video-explainer-trigger': 'openPopup',
			'click .llms-video-explainer-close': 'closePopup',
			'click .llms-video-explainer-wrapper': 'closePopup',
		},

		/**
		 * Youtube video url
		 *
		 * @type {string}
		 */
		youtubeUrl: 'https://www.youtube.com/embed/kMd37cOsPIg',

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

		/**
		 * Open the popup
		 *
		 * @since [version]
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
		 * Close the popup
		 *
		 * @since [version]
		 * @param {Object} event JS event object.
		 * @return {void}
		 */
		closePopup: function( event ) {
			event.preventDefault();

			$( '.llms-video-explainer-wrapper' ).css( {
				display: 'none',
				opacity: '0',
			} );

			$( '.llms-video-explainer-iframe' ).attr( 'src', '' ).attr( 'src', this.youtubeUrl );
		},

	} );
} );
