/**
 * Handles UX and Events for inline editing of views
 * Use with a Model's View
 * Allows editing model.title field via .llms-editable-title elements
 * @type     {Object}
 * @since    3.13.0
 * @version  [version]
 */
define( [ 'Views/FormattingToolbar' ], function( FormattingToolbarView ) {

	return {

		media_lib: null,

		tag_whitelist: [ 'b', 'i', 'u' ],

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		events: {
			'click .llms-add-image': 'open_media_lib',
			'click a[href="#llms-remove-image"]': 'remove_image',
			'change .llms-switch input[type="checkbox"]': 'toggle_switch',
			'focus .llms-input[data-formatting]': 'show_formatting_toolbar',
			'focusout .llms-input': 'on_blur',
			'keydown .llms-input': 'on_keydown',
		},

		get_allowed_tags: function( $el ) {

			return _.intersection( this.tag_whitelist, $el.attr( 'data-formatting' ).split( ',' ) );

		},

		get_content: function( $el ) {

			if ( ! $el.attr( 'data-formatting' ) ) {
				return $el.text();
			}

			var $html = $( '<div>' + $el.html() + '</div>' );

			$html.find( '*' ).not( this.get_allowed_tags( $el ).join( ',' ) ).each( function( ) {

				$( this ).replaceWith( this.innerHTML );

			} );

			return $html.html();

		},

		// get_video_embed: function( content, attr ) {

		// 	var self = this;

		// 	$.post( ajaxurl, {
		// 		action: 'parse-embed',
		// 		maxwidth: '240',
		// 		post_ID: 0,
		// 		type: 'embed',
		// 		shortcode: '[embed]' + content + '[/embed]',
		// 	}, function( res ) {
		// 		self.$el.find( '.llms-video-embed[data-attribute="' + attr + '"]' ).html( res.data.body );
		// 	} );

		// },

		/**
		 * Determine if changes have been made to the element
		 * @param    {[obj]}   event  js event object
		 * @return   {Boolean}        true when changes have been made, false otherwise
		 * @since    3.13.0
		 * @version  [version]
		 */
		has_changed: function( event ) {
			var $el = $( event.target );
			return ( $el.attr( 'data-original-content' ) !== this.get_content( $el ) );
		},

		/**
		 * Ensure that new content is at least 1 character long
		 * @param    obj   event  js event object
		 * @return   boolean
		 * @since    [version]
		 * @version  [version]
		 */
		is_valid: function( event ) {

			var self = this,
				$el = $( event.target ),
				content = this.get_content( $el ),
				type = $el.attr( 'data-type' );

			if ( content.length < 1 ) {
				return false;
			}

			if ( 'url' === type || 'video' === type ) {
				if ( ! this._validate_url( this.get_content( $el ) ) ) {
					return false;
				}

				// if ( 'video' === type ) {

				// 	this.get_video_embed( content, $el.attr( 'data-attribute' ) );

				// }

			}

			return true;

		},

		/**
		 * Blur/focusout function for .llms-editable-title elements
		 * Automatically saves changes if changes have been made
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		on_blur: function( event ) {

			event.stopPropagation();

			var self = this,
				$el = $( event.target ),
				changed = this.has_changed( event );

			this.hide_formatting_toolbar( $el );

			if ( changed ) {

				if ( ! self.is_valid( event ) ) {
					self.revert_edits( event );
				} else {
					this.save_edits( event );
				}

			}

		},

		/**
		 * Keydown function for .llms-editable-title elements
		 * Blurs
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  [version]
		 */
		on_keydown: function( event ) {

			event.stopPropagation();

			var self = this,
				key = event.which || event.keyCode,
				ctrl = event.metaKey || event.ctrlKey;

			switch ( key ) {

				case 13: // enter
					event.preventDefault();
					event.target.blur();
				break;

				case 27: // escape
					event.preventDefault();
					this.revert_edits( event );
					event.target.blur();
				break;

			}

		},

		open_media_lib: function( event ) {

			event.stopPropagation();

			var self = this,
				$el = $( event.currentTarget );

			if ( self.media_lib ) {

				self.media_lib.uploader.uploader.param( 'post_id', self.model.get( 'id' ) );

			} else {

				wp.media.model.settings.post.id = self.model.get( 'id' );

				self.media_lib = wp.media.frames.file_frame = wp.media( {
					title: LLMS.l10n.translate( 'Select an image' ),
					button: {
						text: LLMS.l10n.translate( 'Use this image' ),
					},
					multiple: false	// Set to true to allow multiple files to be selected
				} );

				self.media_lib.on( 'select', function() {

					var size = $el.attr( 'data-image-size' ),
						attachment = self.media_lib.state().get( 'selection' ).first().toJSON(),
						image = self.model.get( $el.attr( 'data-attribute' ) ),
						url;

					if ( size && attachment.sizes[ size ] ) {
						url = attachment.sizes[ size ].url;
					} else {
						url = attachment.url;
					}

					image.set( {
						id: attachment.id,
						src: url,
					} );

				} );

			}

			self.media_lib.open();


		},

		remove_image: function( event ) {

			event.preventDefault();

			this.model.get( $( event.currentTarget ).attr( 'data-attribute' ) ).set( {
				id: '',
				src: '',
			} );

		},

		/**
		 * Helper to undo changes
		 * Bound to "escape" key via on_keydwon function
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  [version]
		 */
		revert_edits: function( event ) {
			var $el = $( event.target ),
				val = $el.attr( 'data-original-content' );
			$el.html( val );
		},

		/**
		 * Sync chages to the model and DB
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  [version]
		 */
		save_edits: function( event ) {

			var $el = $( event.target ),
				val = this.get_content( $el );

			this.model.set( $el.attr( 'data-attribute' ), val );

		},

		/**
		 * Change event for a switch element
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		toggle_switch: function( event ) {

			event.stopPropagation();
			var $el = $( event.target ),
				attr = $el.attr( 'name' ),
				val;

			if ( $el.is( ':checked' ) ) {
				val = $el.attr( 'data-on' ) ? $el.attr( 'data-on' ) : 'yes';
			} else {
				val = $el.attr( 'data-off' ) ? $el.attr( 'data-off' ) : 'no';
			}

			if ( -1 !== attr.indexOf( '.' ) ) {

				var split = attr.split( '.' );

				if ( 'parent' === split[0] ) {
					this.model.get_parent().set( split[1], val );
				} else {
					this.model.get( split[0] ).set( split[1], val );
				}


			} else {

				this.model.set( attr, val );

			}

			this.trigger( attr.replace( '.', '-' ) + '_toggle', val );

		},

		hide_formatting_toolbar: function( $el ) {

			$el.parent().find( '.llms-input-formatting' ).remove();

		},

		show_formatting_toolbar: function( event ) {

			var $el = $( event.target ),
				Toolbar = new FormattingToolbarView( {
					$input: $el,
					tags: this.get_allowed_tags( $el ),
				} );

			Toolbar.render();

			$el.parent().append( Toolbar.$el );

		},

		init_editor: function( id ) {

			wp.editor.remove( id );

			wp.editor.initialize( id, $.extend( true, wp.editor.getDefaultSettings(), {
				mediaButtons: true,
				tinymce: {
					toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_fullscreen,wp_adv',
					toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
					setup: _.bind( this.on_editor_ready, this ),
				}
			} ) );

		},

		on_editor_ready: function( editor ) {

			var self = this;

			// save changes to the model
			editor.on( 'change', function( event ) {
				self.model.set( $( '#' + editor.id ).attr( 'data-attribute' ), wp.editor.getContent( editor.id ) );
			} );

		},

		_validate_url: function( str ) {

			var a = document.createElement( 'a' );
			a.href = str;
			return ( a.host && a.host !== window.location.host );

		}

	};

} );
