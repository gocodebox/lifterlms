/**
 * Model settings fields view
 *
 * @since    3.17.0
 * @version  3.24.0
 */
define( [], function() {

	return Backbone.View.extend( _.defaults( {

		/**
		 * DOM events
		 *
		 * @type  {Object}
		 */
		events: {
			'click .llms-settings-group-toggle': 'toggle_group',
		},

		/**
		 * Processed fields data
		 * Allows access by ID without traversing the schema
		 *
		 * @type  {Object}
		 */
		fields: {},

		/**
		 * Wrapper Tag name
		 *
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 *
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-settings-fields-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 *
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		// initialize: function() {},

		/**
		 * Retrieve an array of all editor fields in all groups
		 *
		 * @return   array
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		get_editor_fields: function() {
			return _.filter( this.fields, function( field ) {
				return this.is_editor_field( field.type );
			}, this );
		},

		/**
		 * Get settings group data from a model
		 *
		 * @return   {[type]}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_groups: function() {

			return this.model.get_settings_fields();

		},

		/**
		 * Determine if a settings group is hidden in localStorage
		 *
		 * @param    string   group_id  id of the group
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_group_hidden: function( group_id ) {

			var id = 'llms-' + this.model.get( 'type' ) + '-settings-group--' + group_id;

			if ( 'undefined' !== window.localStorage ) {
				return ( 'hidden' === window.localStorage.getItem( id ) );
			}

			return false;

		},

		/**
		 * Get the switch attribute for a field with switches
		 *
		 * @param    obj   field  field data obj
		 * @return   string
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_switch_attribute: function( field ) {

			return field.switch_attribute ? field.switch_attribute : field.attribute;

		},

		/**
		 * Determine if a field has a switch
		 *
		 * @param    string   type  field type string
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		has_switch: function( type ) {
			return ( -1 !== type.indexOf( 'switch' ) );
		},

		/**
		 * Determine if a field is a default (text) field
		 *
		 * @param    string   type  field type string
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_default_field: function( type ) {

			var types = [ 'audio_embed', 'datepicker', 'number', 'text', 'video_embed' ];
			return ( -1 !== types.indexOf( type.replace( 'switch-', '' ) ) );

		},

		/**
		 * Determine if a field is a WYSIWYG editor field
		 *
		 * @param    string   type  field type string
		 * @return   {Boolean}
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		is_editor_field: function( type ) {

			var types = [ 'editor', 'switch-editor' ];
			return ( -1 !== types.indexOf( type.replace( 'switch-', '' ) ) );

		},

		/**
		 * Determine if a switch is enabled for a field
		 *
		 * @param    obj   field  field data object
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.6
		 */
		is_switch_condition_met: function( field ) {

			return ( field.switch_on === this.model.get( field.switch_attribute ) );

		},

		/**
		 * Compiles the template and renders the view
		 *
		 * @return   self (for chaining)
		 * @since    3.17.0
		 * @version  3.17.1
		 */
		render: function() {

			this.$el.html( this.template( this ) );

			// if editors exist, render them
			_.each( this.get_editor_fields(), function( field ) {
				this.render_editor( field );
			}, this );

			return this;

		},

		/**
		 * Renders an editor field
		 *
		 * @param    obj   field  field data object
		 * @return   void
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		render_editor: function( field ) {

			var self = this;

			wp.editor.remove( field.id );
			field.settings.tinymce.setup = function( editor ) {

				var $ed     = $( '#' + editor.id ),
					$parent = $ed.closest( '.llms-editable-editor' ),
					$label  = $parent.find( '.llms-label' ),
					prop    = $ed.attr( 'data-attribute' )

				if ( $label.length ) {
					$label.prependTo( $parent.find( '.wp-editor-tools' ) );
				}

				// save changes to the model via Visual ed
				editor.on( 'change', function( event ) {
					self.model.set( prop, wp.editor.getContent( editor.id ) );
				} );

				// save changes via Text ed
				$ed.on( 'input', function( event ) {
					self.model.set( prop, $ed.val() );
				} );

				// trigger an input on the Text ed when quicktags buttons are clicked
				$parent.on( 'click', '.quicktags-toolbar .ed_button', function() {
					setTimeout( function() {
						$ed.trigger( 'input' );
					}, 10 );
				} );
			};

			wp.editor.initialize( field.id, field.settings );

		},

		/**
		 * Get the HTML for a select field
		 *
		 * @param    obj      options    flat or multi-dimensional options object
		 * @param    string   attribute  name of the select field's attribute
		 * @return   string
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		render_select_options: function( options, attribute ) {

			var html     = '',
				selected = this.model.get( attribute );

			function option_html( label, val ) {

				return '<option value="' + val + '"' + _.selected( val, selected ) + '>' + label + '</option>';

			}

			_.each( options, function( option, index ) {

				// this will be an key:val object
				if ( 'string' === typeof option ) {
					html += option_html( option, index );
					// either option group or array of key,val objects
				} else if ( 'object' === typeof option ) {
					// option group
					if ( option.label && option.options ) {
						html += '<optgroup label="' + option.label + '">';
						html += this.render_select_options( option.options, attribute );
					} else {
						html += option_html( option.val, option.key );
					}
				}

			}, this );

			return html;

		},

		/**
		 * Setup and fill fields with default data based on field type
		 *
		 * @param    obj   orig_field   original field as defined in the settings
		 * @param    int   field_index  index of the field in the current row
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.24.0
		 */
		setup_field: function( orig_field, field_index ) {

			var defaults = {
				classes: [],
				id: _.uniqueId( orig_field.attribute + '_' ),
				input_type: 'text',
				label: '',
				options: {},
				placeholder: '',
				tip: '',
				tip_position: 'top-right',
				settings: {},
			};

			// check the field condition if set
			if ( orig_field.condition && false === _.bind( orig_field.condition, this.model )() ) {
				return false;
			}

			switch ( orig_field.type ) {

				case 'audio_embed':
					defaults.classes.push( 'llms-editable-audio' );
					defaults.placeholder = 'https://';
					defaults.tip         = LLMS.l10n.translate( 'Use SoundCloud or Spotify audio URLS.' );
					defaults.input_type  = 'url';
				break;

				case 'datepicker':
					defaults.classes.push( 'llms-editable-date' );
				break;

				case 'editor':
				case 'switch-editor':
					var orig_settings = orig_field.settings || {};
					defaults.settings = $.extend( true, wp.editor.getDefaultSettings(), {
						mediaButtons: true,
						tinymce: {
							toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_adv',
							toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
						}
					}, orig_settings );
				break;

				case 'number':
					defaults.input_type = 'number';
				break;

				case 'permalink':
					defaults.label = LLMS.l10n.translate( 'Permalink' );
				break;

				case 'video_embed':
					defaults.classes.push( 'llms-editable-video' );
					defaults.placeholder = 'https://';
					defaults.tip         = LLMS.l10n.translate( 'Use YouTube, Vimeo, or Wistia video URLS.' );
					defaults.input_type  = 'url';
				break;

			}

			if ( this.has_switch( orig_field.type ) ) {
				defaults.switch_on  = 'yes';
				defaults.switch_off = 'no';
			}

			var field = _.defaults( _.deepClone( orig_field ), defaults );

			// if options is a function run it
			if ( _.isFunction( field.options ) ) {
				field.options = _.bind( field.options, this.model )();
			}

			// if it's a radio field options values can be submitted as images
			// this will transform those images into <img> html
			if ( -1 !== [ 'radio', 'switch-radio' ].indexOf( orig_field.type ) ) {

				var has_images = false;
				_.each( orig_field.options, function( val, key ) {
					if ( -1 !== val.indexOf( '.png' ) || -1 !== val.indexOf( '.jpg' ) ) {
						field.options[key] = '<span><img src="' + val + '"></span>';
						has_images         = true;
					}
				} );
				if ( has_images ) {
					field.classes.push( 'has-images' );
				}

			}

			// transform classes array to a css class string
			if ( field.classes.length ) {
				field.classes = ' ' + field.classes.join( ' ' );
			}

			this.fields[ field.id ] = field;

			return field;

		},

		/**
		 * Determine if toggling a switch select should rerender the view
		 *
		 * @param    string   field_type  field type string
		 * @return   boolean
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		should_rerender_on_toggle: function( field_type ) {

			return ( -1 !== field_type.indexOf( 'switch-' ) ) ? 'yes' : 'no';

		},

		/**
		 * Click event for toggling visibility of settings groups
		 * If localStorage is available, persist state
		 *
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		toggle_group: function( event ) {

			event.preventDefault();

			var $el    = $( event.currentTarget ),
				$group = $el.closest( '.llms-model-settings' );

			$group.toggleClass( 'hidden' );

			if ( 'undefined' !== window.localStorage ) {

				var id = $group.attr( 'id' );
				if ( $group.hasClass( 'hidden' ) ) {
					window.localStorage.setItem( id, 'hidden' );
				} else {
					window.localStorage.removeItem( id );
				}

			}

		},

	} ) );

} );
