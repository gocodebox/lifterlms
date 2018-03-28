/**
 * Model settings fields view
 * @since    3.17.0
 * @version  3.17.0
 */
define( [], function() {

	return Backbone.View.extend( _.defaults( {

		/**
		 * DOM events
		 * @type  {Object}
		 */
		events: {
			'click .llms-settings-group-toggle': 'toggle_group',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-settings-fields-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		initialize: function() {

			// this.render();

		},

		/**
		 * Get settings group data from a model
		 * @return   {[type]}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_groups: function() {

			return this.model.get_settings_fields();

		},

		/**
		 * Determine if a settings group is hidden in localStorage
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
		 * Determine if a switch is enabled for a field
		 * @param    obj   field  field data object
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_switch_condition_met: function( field ) {

			return ( 'yes' === this.model.get( field.switch_attribute ) );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		render: function() {

			this.$el.html( this.template( this ) );

			return this;

		},

		/**
		 * Get the HTML for a select field
		 * @param    obj      options    flat or multi-dimensional options object
		 * @param    string   attribute  name of the select field's attribute
		 * @return   string
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		render_select_options: function( options, attribute ) {

			var html = '',
				selected = this.model.get( attribute )

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
		 * @param    obj   orig_field   original field as defined in the settings
		 * @param    int   field_index  index of the field in the current row
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		setup_field: function( orig_field, field_index ) {

			var defaults = {
				classes: [],
				id: _.uniqueId( orig_field.attribute ),
				input_type: 'text',
				label: '',
				options: {},
				placeholder: '',
				tip: '',
				tip_position: 'top-right',
			};

			// check the field condition if set
			if ( orig_field.condition && false === _.bind( orig_field.condition, this.model )() ) {
				return false;
			}

			switch ( orig_field.type ) {

				case 'audio_embed':
					defaults.classes.push( 'llms-editable-audio' );
					defaults.placeholder = 'https://';
					defaults.tip = LLMS.l10n.translate( 'Use SoundCloud or Spotify audio URLS.' );
					defaults.input_type = 'url';
				break;

				case 'datepicker':
					defaults.classes.push( 'llms-editable-date' );
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
					defaults.tip = LLMS.l10n.translate( 'Use YouTube, Vimeo, or Wistia video URLS.' );
					defaults.input_type = 'url';
				break;

			}

			var field = _.defaults( _.clone( orig_field ), defaults );

			if ( field.tip ) {
				field.classes.push( 'tip--' + field.tip_position );
			}

			if ( field.classes.length ) {
				field.classes = ' ' + field.classes.join( ' ' );
			}

			if ( 'function' === typeof field.options ) {
				field.options = _.bind( field.options, this.model )();
			}

			return field;

		},

		/**
		 * Determine if toggling a switch select should rerender the view
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
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		toggle_group: function( event ) {

			event.preventDefault();

			var $el = $( event.currentTarget ),
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
