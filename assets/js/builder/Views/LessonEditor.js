/**
 * Lesson Editor (Sidebar) View
 *
 * @package LifterLMS/Scripts/Builder
 *
 * @since 3.17.0
 * @since 3.35.2 Added filter `llms_lesson_rerender_change_events` to view re-render change events.
 * @since 10.1.1 Only autofocus the title on the initial render.
 * @version 10.1.1
 */
define( [
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Trashable',
		'Views/_Subview',
		'Views/SettingsFields'
	], function(
		Detachable,
		Editable,
		Trashable,
		Subview,
		SettingsFields
	) {

		return Backbone.View.extend( _.defaults( {

			/**
			 * Current view state
			 *
			 * @type  {String}
			 */
			state: 'default',

			/**
			 * Current Subviews
			 *
			 * @type  {Object}
			 */
			views: {
				settings: {
					class: SettingsFields,
					instance: null,
					state: 'default',
				},
			},

			el: '#llms-editor-lesson',

			/**
			 * Events
			 *
			 * @type  {Object}
			 */
			events: _.defaults( {}, Detachable.events, Editable.events, Trashable.events ),

			/**
			 * Template function
			 *
			 * @type  {[type]}
			 */
			template: wp.template( 'llms-lesson-settings-template' ),

			/**
			 * Init
			 *
			 * @since 3.17.0
			 * @since 3.24.0 Unknown
			 * @since 3.35.2 Added filter to change events.
			 *
			 * @param {obj} data Parent template data.
			 * @return {void}
			 */
			initialize: function( data ) {

				this.model = data.lesson;

				var change_events = [
					'change:date_available',
					'change:drip_method',
					'change:free_lesson',
					'change:has_minimum_time',
					'change:permalink',
					'change:content_added_in_builder',
					'change:name',
					'change:time_available',
				];
				if ( ! window.llms_builder.av ) {
					change_events.push( 'change:video_embed' );
				}
				change_events = window.llms.hooks.applyFilters( 'llms_lesson_rerender_change_events', change_events );
				_.each( change_events, function( event ) {
					this.listenTo( this.model, event, this.render );
				}, this );

				// render only the tooltip for points percentage when points change
				this.listenTo( this.model, 'change:points', this.render_points_percentage );

				// when the "has_prerequisite" attr is toggled ON
				// trigger the prereq select object to set the default (first available) prereq for the lesson
				this.listenTo( this.model, 'change:has_prerequisite', function( lesson, val ) {
					if ( 'yes' === val ) {
						this.$el.find( 'select[name="prerequisite"]' ).trigger( 'change' );
					}
				} );

			},

		/**
		 * Render the view
		 *
		 * @since 3.17.0
		 * @since 3.24.0 Unknown.
		 * @since 10.1.1 Only autofocus the title on the initial render.
		 *                     Refocusing after permalink/`name` changes left `_has_focus` set and
		 *                     caused attached lessons to be skipped on save.
		 *
		 * @return {Object}
		 */
		render: function() {

			var is_initial = ! this._has_rendered;

			this.$el.html( this.template( this.model ) );

			this.remove_subview( 'settings' );

			this.render_subview( 'settings', {
				el: '#llms-lesson-settings-fields',
				model: this.model,
			} );

			this.init_datepickers();
			this.init_selects();

			this.render_points_percentage();

			this._has_rendered = true;

			// Only steal focus when the editor is first opened, not on subsequent re-renders
			// (e.g. after editing the permalink, which triggers change:name → render).
			if ( is_initial ) {
				this.$('.llms-editable-title').focus();
			}

			return this;

		},

			/**
			 * Render the portion of the template which displays the points percentage
			 *
			 * @return   void
			 * @since    3.24.0
			 * @version  3.24.0
			 */
			render_points_percentage: function() {
				this.$el.find( '#llms-model-settings-field--points .llms-editable-input' )
				.addClass( 'tip--top-left' )
				.attr( 'data-tip', this.model.get_points_percentage() );
			}

		}, Detachable, Editable, Trashable, Subview, SettingsFields ) );

} );
