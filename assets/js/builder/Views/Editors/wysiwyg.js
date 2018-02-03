define( [], function() {

	return Backbone.Form.editors.TextArea.extend({

		initialize: function(options) {

			// Call parent constructor
			Backbone.Form.editors.Base.prototype.initialize.call(this, options);

		},

		render: function() {

			this.setValue( this.value );

			return this;

		},

		/**
		 * Load Tiny MCE on the editor
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		load_tinymce: function() {

			// broadcast so other instances can unload the editor before loading a new one
			Backbone.pubSub.trigger( 'pre-load-wysiwyg-editor' );

			wp.editor.initialize( this._get_id(), $.extend( true, wp.editor.getDefaultSettings(), {
				mediaButtons: true,
				tinymce: {
					toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_fullscreen,wp_adv',
					toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
					setup: _.bind( this.on_ready, this ),
				}
			} ) );

		},

		unload_tinymce: function() {

			wp.editor.remove( this._get_id() );

		},

		getValue: function() {
			return wp.editor.getContent( this._get_id() );
		},

		setValue: function( value ) {
			this.$el.val( value );
		},

		focus: function() {
			if (this.hasFocus) return;

			// This method call should result in an input within this editor
			// becoming the `document.activeElement`.
			// This, in turn, should result in this editor's `focus` event
			// being triggered, setting `this.hasFocus` to `true`.
			// See above for more detail.
			this.$el.focus();
		},

		blur: function() {
			if (!this.hasFocus) return;

			this.$el.blur();
		},

		on_ready: function( editor ) {

			// unload this editor if another editor is going to be loaded
			Backbone.pubSub.on( 'pre-load-wysiwyg-editor', _.bind( this.unload_tinymce, this ) );
			// editor.on( 'change', function( event ) {
				// self.commit();
			// } );

		},

		_get_id: function() {

			return this.$el.attr( 'id' );

		},

	} );

} );
