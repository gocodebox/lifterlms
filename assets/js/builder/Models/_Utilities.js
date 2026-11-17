/**
 * Utility functions for Models.
 *
 * @since 3.16.0
 * @version 7.4.0
 */
define( [], function() {

	return {

		fields: [],

		/**
		 * Override Backbone `set` method.
		 *
		 * Takes into account attributes of the form object[prop].
		 *
		 * @since 7.4.0
		 *
		 * @param {Mixed} attr The attribute to be set.
		 * @param {Mixed} val  The value to set.
		 */
		set: function ( attr, val ) {

			if ( 'string' === typeof attr ) {

				const matches = attr.match( /(.*?)\[(.*?)\]/ );
				if ( matches && 3 === matches.length ) {

					const
						realAttr   = matches[1],
						currentVal = Backbone.Model.prototype.get.call( this, realAttr );

					var newVal = undefined !== currentVal ? currentVal : {};

					newVal[ matches[2] ] = val;

					arguments[0] = realAttr;
					arguments[1] = newVal;

				}
			}

			// Continue with Backbone default `set` behavior.
			Backbone.Model.prototype.set.apply( this, arguments );

		},

		/**
		 * Override Backbone `get` method.
		 *
		 * Takes into account attributes of the form object[prop].
		 *
		 * @since 7.4.0
		 *
		 * @param {Mixed} attr The attribute name.
		 */
		get: function( attr ) {

			const matches = attr.match( /(.*?)\[(.*?)\]/ );
			if ( matches && 3 === matches.length ) {
				const val = Backbone.Model.prototype.get.call( this, matches[1] );
				if ( val && undefined !== val[ matches[2] ] ) {
					return val[ matches[2] ];
				}
			}

			// Continue with Backbone default `get` behavior.
			return Backbone.Model.prototype.get.call( this, attr );

		},

		/**
		 * Retrieve the edit post link for the current model.
		 *
		 * @since 3.16.0
		 *
		 * @return string
		 */
		get_edit_post_link: function() {

			if ( this.has_temp_id() ) {
				return '';
			}

			return window.llms_builder.admin_url + 'post.php?post=' + this.get( 'id' ) + '&action=edit';

		},

		get_view_post_link: function() {
			if ( this.has_temp_id() ) {
				return '';
			}

			if ( this.get( 'permalink' ) ) {
				return this.get( 'permalink' );
			}

			if ( this.get( 'status' ) === 'publish' ) {
				return window.llms_builder.home_url + '?p=' + this.get( 'id' );
			}

			return window.llms_builder.home_url + '?p=' + this.get( 'id' ) + '&preview=true&post_type=' + this.get( 'type' );

		},

		/**
		 * Retrieve schema fields defined for the model
		 *
		 * @return   object
		 * @since    3.17.0
		 * @version  3.17.1
		 */
		get_settings_fields: function() {

			var schema = this.schema || {};
			return window.llms_builder.schemas.get( schema, this.get( 'type' ).replace( 'llms_', '' ), this );

		},

		/**
		 * Determine if the model has a temporary ID
		 *
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		has_temp_id: function() {

			return ( ! _.isNumber( this.get( 'id' ) ) && 0 === this.get( 'id' ).indexOf( 'temp_' ) );

		},

		/**
		 * Slugify a title the way `sanitize_title()` would for basic (ASCII) input.
		 *
		 * A conflict suffix (-2, -3) isn't predicted; the real slug is confirmed on save.
		 *
		 * @since 10.3.0
		 *
		 * @param {String} text Text to slugify.
		 * @return {String}
		 */
		slugify: function( text ) {

			return ( text || '' ).toString().toLowerCase().trim()
				.replace( /[\s_]+/g, '-' )
				.replace( /[^a-z0-9\-]/g, '' )
				.replace( /-+/g, '-' )
				.replace( /^-+|-+$/g, '' );

		},

		/**
		 * Preview the permalink an unsaved lesson or quiz would get on save.
		 *
		 * Computed client-side from the title and the localized permalink template.
		 * Follows title edits until the slug is edited by hand. Sets silently so the
		 * settings panel isn't re-rendered mid-edit (which would steal focus); the
		 * on-screen preview is updated directly by the view.
		 *
		 * @since 10.3.0
		 *
		 * @param {String} title Optional title override, used for live previews while typing
		 *                       before the title is committed to the model on blur.
		 * @return {String} The previewed permalink or an empty string.
		 */
		preview_permalink: function( title ) {

			if ( ! this.has_temp_id() || 'yes' === this.get( 'slug_edited' ) ) {
				return '';
			}

			var structs  = ( window.llms_builder && window.llms_builder.sample_permalinks ) || {},
				template = structs[ this.get( 'type' ) ],
				slug     = this.slugify( _.isString( title ) ? title : this.get( 'title' ) );

			if ( ! template || ! slug ) {
				return '';
			}

			var permalink = template.replace( '%pagename%', slug );

			this.set( {
				permalink: permalink,
				name: slug,
			}, { silent: true } );

			return permalink;

		},

		/**
		 * Initializes 3rd party custom schema (field) data for a model
		 *
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		init_custom_schema: function() {

			var groups = _.filter( this.get_settings_fields(), function( group ) {
				return ( group.custom );
			} );

			_.each( groups, function( group ) {
				_.each( _.flatten( group.fields ), function( field ) {

					var keys    = [ field.attribute ],
						customs = this.get( 'custom' );

					if ( field.switch_attribute ) {
						keys.push( field.switch_attribute );
					}

					_.each( keys, function( key ) {
						var attr = field.attribute_prefix ? field.attribute_prefix + key : key;
						if ( customs && customs[ attr ] ) {
							this.set( key, customs[ attr ][0] );
						}
					}, this );

				}, this );
			}, this );

		},

	};

} );
