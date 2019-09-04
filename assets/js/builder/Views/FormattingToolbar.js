/**
 * Sidebar Elements View
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [], function() {

	return Backbone.View.extend( {

		className: 'llms-input-formatting',

		events: {
			'mousedown a[href="#llms-formatting"]': 'on_click',
		},

		/**
		 * Wrapper Tag name
		 *
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Initialization callback func (renders the element on screen)
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data ) {

			var self = this;

			this.$input = data.$input;
			this.tags   = data.tags;

			this.$input.on( 'keyup focus click', function() {

				_.each( self.tags, function( tag ) {

					var name = self._get_formatting_name( tag );

					if ( document.queryCommandState( name ) ) {
						self.toggle_button_state( name, 'on' );
					} else {
						self.toggle_button_state( name, 'off' );
					}

				} );

			} );

		},

		/**
		 * Compiles the template and renders the view
		 *
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template() );
			return this;

		},

		template: function() {

			var self     = this,
				$toolbar = $( '<div />' );

			_.each( this.tags, function( tag ) {

				$toolbar.append( self._get_formatting_icon( tag ) );

			} );

			return $toolbar.html();

		},

		on_click: function( event ) {

			event.preventDefault();

			var $btn      = $( event.target ),
				selection = window.getSelection(),
				commands  = [ 'bold', 'italic', 'underline' ],
				range, cmd;

			if ( $btn.hasClass( 'fa' ) ) {
				$btn = $btn.closest( 'a' );
			}

			cmd = $btn.attr( 'data-cmd' );

			if ( -1 === commands.indexOf( cmd ) ) {
				return;
			}

			$btn.addClass( 'active' );
			document.execCommand( cmd );

		},

		toggle_button_state: function( tag_name, state ) {

			var $btn = this.$el.find( 'a[data-cmd="' + tag_name + '"]' ),
				del  = 'on' === state ? '' : 'active',
				add  = 'on' === state ? 'active' : '';

			$btn.removeClass( del ).addClass( add );

		},

		_get_formatting_name: function( tag ) {

			var tags = {
				b: 'bold',
				i: 'italic',
				u: 'underline',
			};

			return tags[ tag ];

		},

		_get_formatting_icon: function( tag ) {

			var name = this._get_formatting_name( tag );

			return '<a class="llms-action-icon" data-cmd="' + name + '" href="#llms-formatting"><i class="fa fa-' + name + '" aria-hidden="true"></i></a>';

		},

	} );

} );
