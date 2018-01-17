/**
 * Sidebar Elements View
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return Backbone.View.extend( {

		el: '#llms-editor-lesson',

		initialize: function( data ) {

			this.model = data.lesson;

		},

		render: function() {

			this.$el.html( this.model.get( 'title' ) );

		},

	} );

	// return Backbone.Form.extend( {

	// 	/**
	// 	 * HTML element selector
	// 	 * @type  {String}
	// 	 */
	// 	el: '#llms-editor-lesson',

	// 	attach: function() {
	// 		$( '#llms-editor-lesson' ).html( this.el );
	// 		this.bind_events();
	// 	},

	// 	bind_events: function() {

	// 		var self = this;

	// 		_.each( this.schema, function( field, name ) {

	// 			if ( 'Wysiwyg' === field.type ) {
	// 				self.fields[ name ].editor.load_tinymce();
	// 			}

	// 			self.on( name + ':blur', function( form, editor ) {

	// 				form.commit();

	// 			} );

	// 		} );

	// 	},

	// } );

} );
