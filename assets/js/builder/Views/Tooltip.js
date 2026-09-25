/**
 * Floating tooltips for the course builder.
 *
 * CSS ::before/::after tips are clipped by overflow:hidden/scroll ancestors
 * (editor sidebar, quiz question list) and can stack under the sidebar.
 * This renders a single tooltip on document.body and keeps it in the viewport.
 *
 * @since 10.2.0
 */
define( [], function() {

	return Backbone.View.extend( {

		/**
		 * Tooltip element id.
		 *
		 * @since 10.2.0
		 *
		 * @type {String}
		 */
		id: 'llms-builder-tooltip',

		/**
		 * Tooltip element class.
		 *
		 * @since 10.2.0
		 *
		 * @type {String}
		 */
		className: 'llms-builder-tooltip',

		/**
		 * Tooltip wrapper tag.
		 *
		 * @since 10.2.0
		 *
		 * @type {String}
		 */
		tagName: 'div',

		/**
		 * Hover/focus delay before showing the tooltip (ms).
		 *
		 * @since 10.2.0
		 *
		 * @type {Number}
		 */
		show_delay: 100,

		/**
		 * Initialize the tooltip and bind events.
		 *
		 * @since 10.2.0
		 *
		 * @return {Void}
		 */
		initialize: function() {

			this.anchor     = null;
			this.show_timer = null;

			this.$el.attr( {
				role: 'tooltip',
				'aria-hidden': 'true',
			} ).appendTo( document.body );

			$( '.wrap.lifterlms.llms-builder' ).addClass( 'llms-builder--floating-tips' );

			this.bind();

		},

		/**
		 * Bind show/hide/reposition events.
		 *
		 * @since 10.2.0
		 *
		 * @return {Void}
		 */
		bind: function() {

			var self = this;

			$( document )
				.on( 'mouseenter focusin', '.wrap.lifterlms.llms-builder [data-tip]', function() {
					self.queue_show( this );
				} )
				.on( 'mouseleave focusout', '.wrap.lifterlms.llms-builder [data-tip]', function( event ) {

					var next = event.relatedTarget;
					if ( next && this.contains( next ) ) {
						return;
					}

					self.hide();

				} );

			window.addEventListener( 'scroll', function() {
				if ( self.anchor ) {
					self.position();
				}
			}, true );

			$( window ).on( 'resize', function() {
				if ( self.anchor ) {
					self.position();
				}
			} );

		},

		/**
		 * Delay showing so rapid mouse movement doesn't flash tips.
		 *
		 * @since 10.2.0
		 *
		 * @param {Element} anchor Hovered or focused [data-tip] element.
		 * @return {Void}
		 */
		queue_show: function( anchor ) {

			var self = this;

			clearTimeout( this.show_timer );
			this.anchor = anchor;

			if ( this.$el.hasClass( 'is-visible' ) ) {
				this.show();
				return;
			}

			this.show_timer = setTimeout( function() {
				self.show();
			}, this.show_delay );

		},

		/**
		 * Show and position the tooltip for the current anchor.
		 *
		 * @since 10.2.0
		 *
		 * @return {Void}
		 */
		show: function() {

			var text = this.get_text();

			if ( ! this.anchor || ! text ) {
				this.hide();
				return;
			}

			this.$el.text( text );
			this.position();
			this.$el.addClass( 'is-visible' ).attr( 'aria-hidden', 'false' );

		},

		/**
		 * Hide the tooltip.
		 *
		 * @since 10.2.0
		 *
		 * @return {Void}
		 */
		hide: function() {

			clearTimeout( this.show_timer );
			this.anchor = null;
			this.$el.removeClass( 'is-visible' ).attr( 'aria-hidden', 'true' );

		},

		/**
		 * Read tooltip text from the anchor.
		 *
		 * @since 10.2.0
		 *
		 * @return {String}
		 */
		get_text: function() {

			var $el = $( this.anchor );

			if ( $el.hasClass( 'active' ) && $el.attr( 'data-tip-active' ) ) {
				return $el.attr( 'data-tip-active' );
			}

			return $el.attr( 'data-tip' ) || '';

		},

		/**
		 * Position the tooltip in the viewport, flipping when it would overflow.
		 *
		 * @since 10.2.0
		 *
		 * @return {Void}
		 */
		position: function() {

			if ( ! this.anchor || ! document.body.contains( this.anchor ) ) {
				this.hide();
				return;
			}

			var rect       = this.anchor.getBoundingClientRect(),
				gap        = 8,
				pad        = 8,
				pad_top    = 40, // WP admin bar.
				vw         = document.documentElement.clientWidth,
				vh         = document.documentElement.clientHeight,
				el         = this.el,
				tw,
				th,
				space_above,
				space_below,
				place_below,
				top,
				left,
				arrow_x;

			el.style.top  = '0px';
			el.style.left = '0px';
			this.$el.removeClass( 'is-below' );

			tw = el.getBoundingClientRect().width;
			th = el.getBoundingClientRect().height;

			space_above = rect.top - pad_top;
			space_below = vh - rect.bottom - pad;
			place_below = space_above < ( th + gap ) && space_below > space_above;

			top  = place_below ? rect.bottom + gap : rect.top - th - gap;
			left = rect.left;

			if ( left + tw > vw - pad ) {
				left = rect.right - tw;
			}

			if ( left < pad ) {
				left = pad;
			}

			if ( left + tw > vw - pad ) {
				left = Math.max( pad, vw - pad - tw );
			}

			arrow_x = rect.left + ( rect.width / 2 ) - left;
			arrow_x = Math.max( 12, Math.min( tw - 12, arrow_x ) );

			this.$el.toggleClass( 'is-below', place_below );
			el.style.top  = Math.round( top ) + 'px';
			el.style.left = Math.round( left ) + 'px';
			el.style.setProperty( '--llms-tip-arrow', Math.round( arrow_x ) + 'px' );

		},

	} );

} );
