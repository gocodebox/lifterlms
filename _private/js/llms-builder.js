( function( $ ) {

	/**
	 * Main Application Object
	 * @type     {Object}
	 * @since    [version]
	 * @version  [version]
	 */
	var App = {
		Collections: {},
		Models: {},
		Views: {},

		/**
		 * Various Application Methods
		 * @type  {Object}
		 */
		Methods: {

			/**
			 * Retrieve the last section in the current instance
			 * @return   obj     App.Models.Section
			 * @since    [version]
			 * @version  [version]
			 */
			get_last_section: function() {
				return Instance.Syllabus.collection.at( Instance.Syllabus.collection.length - 1 );
			},

			/**
			 * Bind Draggable Events
			 * Powers draggable items in the tools sidebar
			 * @return   void
			 * @since    [version]
			 * @version  [version]
			 */
			draggable: function() {
				$( '#llms-new-section' ).draggable( {
					cancel: false,
					connectToSortable: '.llms-sections',
					helper: function() {
						var section = new App.Models.Section( {
							id: _.uniqueId( 'section_temp_' ),
						} );
						return new App.Views.Section( { model: section } ).render().$el;
					},
					start: function() {
						$( '.llms-sections' ).addClass( 'dragging' );
					},
					stop: function() {
						$( '.llms-sections' ).removeClass( 'dragging' );
					},
				} );
				$( '#llms-new-lesson' ).draggable( {
					cancel: false,
					connectToSortable: '.llms-lessons',
					helper: function() {
						var lesson = new App.Models.Lesson( {
							id: _.uniqueId( 'lesson_temp_' ),
						} );
						return new App.Views.Lesson( { model: lesson } ).render().$el;
					},
					start: function() {
						$( '.llms-lessons' ).addClass( 'dragging' );
					},
					stop: function() {
						$( '.llms-lessons' ).removeClass( 'dragging' );
					},
				} );
			},

			/**
			 * Bind jQuery UI Sortable events
			 * Powers draggable course elements in the syllabus area
			 * @return   void
			 * @since    [version]
			 * @version  [version]
			 */
			sortable: function() {
				$( '.llms-sections' ).sortable( {
					cursor: 'move',
					cursorAt: {
						top: 10,
						left: 10,
					},
					handle: '.drag-section',
					items: '.llms-section',
					placeholder: 'llms-section llms-sortable-placeholder',
					tolerance: 'pointer',
					start: function( event, ui ) {
						ui.item.css( 'height', 'auto' );
						$( '.llms-sections' ).addClass( 'dragging' );
					},
					stop: function( event, ui ) {
						ui.item.trigger( 'drop-section', ui.item.index() );
						$( '.llms-sections' ).removeClass( 'dragging' );
					},
				} );

				$( '.llms-lessons' ).sortable( {
					cursor: 'move',
					cursorAt: {
						top: 10,
						left: 10,
					},
					connectWith: '.llms-lessons',
					handle: '.drag-lesson',
					items: '.llms-lesson',
					placeholder: 'llms-lesson llms-sortable-placeholder',
					tolerance: 'pointer',
					start: function( event, ui ) {
						console.log( ui );
						$( '.llms-lessons' ).addClass( 'dragging' );
					},
					stop: function( event, ui ) {

						event.stopPropagation();

						var from_section = ui.item.attr( 'data-section-id' ),
							curr_section = ui.item.closest( '.llms-section' ).attr( 'data-id' );

						console.log( from_section, curr_section );

						ui.item.trigger( 'drop-lesson', [ ui.item, curr_section, from_section ] );

						ui.item.removeAttr( 'style' )
							.closest( '.llms-section' ).addClass( 'opened' );

						$( '.llms-lessons' ).removeClass( 'dragging' );

					},
					receive: function( event, ui ) {
						ui.item.trigger( 'update-parent', ui.item );
						ui.item.removeAttr( 'style' )
							.closest( '.llms-section' ).addClass( 'opened' );
					},
					over: function( event, ui ) {
						$( '#' + event.target.offsetParent.id ).addClass( 'hover-opened' );
					},
					out: function( event, ui ) {
						$( '#' + event.target.offsetParent.id ).removeClass( 'hover-opened' );
					}
				} );
			}
		},

		/**
		 * Reusable parts and snippets mixed into Backbone Models, Collections, and Views
		 * Lets us be a bit more DRY
		 * @type  {Object}
		 */
		Mixins: {

			/**
			 * Handles UX and Events for inline editing of views
			 * Use with a Model's View
			 * Allows editing model.title field via .llms-editable-title elements
			 * @type     {Object}
			 * @since    [version]
			 * @version  [version]
			 */
			EditableView: {

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    [version]
				 * @version  [version]
				 */
				events: {
					'keydown .llms-editable-title': 'on_keydown',
				},

				/**
				 * Determine if changes have been made to the element
				 * @param    {[obj]}   event  js event object
				 * @return   {Boolean}        true when changes have been made, false otherwise
				 * @since    [version]
				 * @version  [version]
				 */
				has_changed: function( event ) {
					var $el = $( event.target );
					return ( $el.attr( 'data-original-content' ) !== $el.text() );
				},

				/**
				 * Keydown function for .llms-editable-title elements
				 * @param    {obj]}   event  js event object
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				on_keydown: function( event ) {

					event.stopPropagation();

					var self = this,
						changed = this.has_changed( event ),
						key = event.which || event.keyCode;

					switch ( key ) {

						case 9: // tab
							if ( changed ) {
								this.save_edits( event );
							}
						break;

						case 13: // enter
							event.preventDefault();
							if ( changed ) {
								this.save_edits( event );
							}
							event.target.blur();
						break;

						case 27: // escape
							event.preventDefault();
							this.revert_edits( event );
							event.target.blur();
						break;
					}

				},

				/**
				 * Helper to undo changes
				 * Bound to "escape" key via on_keydwon function
				 * @param    {[type]}   event  js event object
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				revert_edits: function( event ) {
					var $el = $( event.target ),
						val = $el.attr( 'data-original-content' );
					$el.text( val );
				},

				/**
				 * Sync chages to the model and DB
				 * @param    {obj}   event  js event object
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				save_edits: function( event ) {

					var $el = $( event.target ),
						val = $el.text(),
						save_id = 'edit_' + this.model.id;

					this.model.set( 'title', val ).save( null, {
						beforeSend: function() {
							Instance.Status.add( save_id );
						},
						success: function( res ) {
							Instance.Status.remove( save_id );
						},
					} );

				},

			},

			/**
			 * Handles UX and Events for shifting views up and down
			 * Use with a Model's View
			 * Used with Section and Lesson views
			 * @type     {Object}
			 * @since    [version]
			 * @version  [version]
			 */
			ShiftableView: {

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    [version]
				 * @version  [version]
				 */
				events: {
					'click .llms-action-icon.shift-down': 'shift_down',
					'click .llms-action-icon.shift-up': 'shift_up',
				},

				/**
				 * Shift model one space down (to the right or +1) in it's collection
				 * automatically resorts other items in collection and syncs collection to db
				 * @param    obj   e  js event object
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				shift_down: function( e ) {
					e.stopPropagation();
					e.preventDefault();
					this.$el.trigger( 'update-sort', [ this.model, this.model.get( 'order' ) + 1, this.model.collection ] );
				},

				/**
				 * Shift model one space up (to the right or -1) in it's collection
				 * automatically resorts other items in collection and syncs collection to db
				 * @param    obj   e  js event object
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				shift_up: function( e ) {
					e.stopPropagation();
					e.preventDefault();
					this.$el.trigger( 'update-sort', [ this.model, this.model.get( 'order' ) - 1, this.model.collection ] );
				},

			},

			/**
			 * Handles UX and Events for sorting models via jQuery UI Sortable
			 * Use with a Collection's View
			 * Used with Section and Lesson List (collection) views
			 * @type     {Object}
			 * @since    [version]
			 * @version  [version]
			 */
			SortableView: {

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    [version]
				 * @version  [version]
				 */
				events: {
					'update-sort': 'update_sort',
				},

				/**
				 * Resorst an out-of-order collection by the order property on its models
				 * Rerenders the view when completed
				 * @param    {obj}   collection  a backbone collection with models that have an "order" prop
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				sort_collection: function( collection ) {

					collection.each( function( model, index ) {
						model.set( 'order', index + 1 );
					} );
					collection.trigger( 'rerender' );

				},

				/**
				 * Triggered by element dropping event
				 * Moves models into the new collection, resorts collections, and optionally syncs data to DB
				 * @param    {obj}    event            js event object
				 * @param    {obj}    model            model being moved
				 * @param    {obj}    order            new order (not index) of the model
				 * @param    {obj}    to_collection    collection the model is to be added to
				 * @param    {obj}    from_collection  collection the model is coming from
				 *                                       	new items don't have a from collection
				 *                                        	this will be the same if it's a reorder and not moving to a new collection
				 * @param    {bool}   auto_save        when true, saves to the database
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				update_sort: function ( event, model, order, to_collection, from_collection, auto_save ) {

					event.stopPropagation();

					auto_save = undefined === auto_save ? true : auto_save;

					var to_self = ( ! from_collection || to_collection === from_collection )
						remove_from_collection = to_self ? to_collection : from_collection;

					// dropped items won't have a collection yet...
					if ( remove_from_collection ) {
						remove_from_collection.remove( model );
					}

					// when moving lessons to a new section we need to update the old collection
					if ( remove_from_collection && ! to_self ) {
						this.sort_collection( from_collection );
						from_collection.sync_order();
					}

					to_collection.add( model, { at: order - 1 } );
					this.sort_collection( to_collection );

					if ( auto_save ) {
						to_collection.sync_order();
					}

				},

			},

			/**
			 * Handles syncing of a sortable collections order
			 * Use with a Collection
			 * Used with Section and Lesson Collections
			 * @type     {Object}
			 * @since    [version]
			 * @version  [version]
			 */
			SortableCollection: {

				/**
				 * Define the collections compartor
				 * @type  {String}
				 */
				comparator: 'order',

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    [version]
				 * @version  [version]
				 */
				events: {
					'change:order': 'sort',
				},

				/**
				 * Retrieve the next order in the collection
				 * @return   int
				 * @since    [version]
				 * @version  [version]
				 */
				next_order: function() {
					if ( ! this.length ) {
						return 1;
					}
					return this.last().get( 'order' ) + 1;
				},

				/**
				 * Save collection order to the DB
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				sync_order: function() {
					var id = _.uniqueId( 'saving_' );
					this.sync( 'update', this, {
						beforeSend: function() {
							Instance.Status.add( id );
						},
						success: function( res ) {
							Instance.Status.remove( id );
						},
					} );
				}
			},

			/**
			 * Main Syncing
			 * Used with models and collections for all CRUD operations
			 * @type  {Object}
			 * @since    [version]
			 * @version  [version]
			 */
			Syncable: {
				url: ajaxurl,
				action: 'llms_builder',

				/**
				 * triggers AJAX call to CRUD
				 * @param    {string}   method   request method [read,create,update,delete]
				 * @param    {obj}      object   model or collection being synced
				 * @param    {obj}      options  optional AJAX options
				 * @return   void
				 * @since    [version]
				 * @version  [version]
				 */
				sync: function( method, object, options ) {

					if ( typeof options.data === 'undefined' ) {
						options.data = {};
					}

					if ( object instanceof Backbone.Model ) {
						object_type = 'model';
					} else if ( object instanceof Backbone.Collection ) {
						object_type = 'collection';
					}

					options.data.course_id = window.llms_builder.id;
					options.data.action_type = method;
					options.data.object_type = object_type; // eg collection or model
					options.data.data_type = object.type_id; // eg section or lesson
					options.data._ajax_nonce = wp_ajax_data.nonce;

					if ( undefined === options.data.action && undefined !== this.action ) {
						options.data.action = this.action;
					}

					if ( 'read' === method ) {
						return Backbone.sync( method, object, options );
					}

					var json = this.toJSON();
					var formattedJSON = {};

					if ( json instanceof Array ) {
						formattedJSON.models = json;
					} else {
						formattedJSON.model = json;
					}

					_.extend( options.data, formattedJSON );

					options.emulateJSON = true;

					return Backbone.sync.call( this, 'create', object, options );

				}
			},
		}

	};



	/*
		 /$$      /$$                 /$$           /$$
		| $$$    /$$$                | $$          | $$
		| $$$$  /$$$$  /$$$$$$   /$$$$$$$  /$$$$$$ | $$  /$$$$$$$
		| $$ $$/$$ $$ /$$__  $$ /$$__  $$ /$$__  $$| $$ /$$_____/
		| $$  $$$| $$| $$  \ $$| $$  | $$| $$$$$$$$| $$|  $$$$$$
		| $$\  $ | $$| $$  | $$| $$  | $$| $$_____/| $$ \____  $$
		| $$ \/  | $$|  $$$$$$/|  $$$$$$$|  $$$$$$$| $$ /$$$$$$$/
		|__/     |__/ \______/  \_______/ \_______/|__/|_______/
	*/

	/**
	 * Course Model
	 * @since    [version]
	 * @version  [version]
	 */
	App.Models.Course = Backbone.Model.extend( _.defaults( {

		type_id: 'course',

		/**
		 * New Course Defaults
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		defaults: function() {
			return {
				title: 'New Course',
				edit_url: '',
				view_url: '',
			}
		},

	}, App.Mixins.Syncable ) );

	/**
	 * Lesson Model
	 * @since    [version]
	 * @version  [version]
	 */
	App.Models.Lesson = Backbone.Model.extend( _.defaults( {

		type_id: 'lesson',

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1,
				section_id = App.Methods.get_last_section().id;
			return {
				title: 'New Lesson',
				type: 'lesson',
				order: order,
				section_id: section_id,

				// urls
				edit_url: '',
				view_url: '',

				// icon info
				date_available: '',
				days_before_available: '',
				drip_method: '',
				has_content: false,
				is_free: false,
				prerequisite: false,
				quiz: false,

			}
		},

		/**
		 * Retrieve the parent section of the lesson
		 * @return   {obj}   App.Models.Section
		 * @since    [version]
		 * @version  [version]
		 */
		get_section: function() {
			return Instance.Syllabus.collection.get( this.get( 'section_id' ) );
		},

	}, App.Mixins.Syncable ) );

	/**
	 * Section Model
	 * @since    [version]
	 * @version  [version]
	 */
	App.Models.Section = Backbone.Model.extend( _.defaults( {

		type_id: 'section',

		/**
		 * New section defaults
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1;
			return {
				title: 'New Section',
				type: 'section',
				order: order,
			};
		},

		/**
		 * Retrieve the next section in the section's collection
		 * @return   obj     App.Models.Section
		 * @since    [version]
		 * @version  [version]
		 */
		get_next: function() {
			return this.collection.at( this.collection.indexOf( this ) + 1 );
		},

		/**
		 * Retrieve the prev section in the section's collection
		 * @return   obj     App.Models.Section
		 * @since    [version]
		 * @version  [version]
		 */
		get_prev: function() {
			return this.collection.at( this.collection.indexOf( this ) - 1 );
		},

		/**
		 * Determines if the section is the last section in the collection
		 * @return   {Boolean}
		 * @since    [version]
		 * @version  [version]
		 */
		is_last: function() {
			return ( this.get( 'order') === this.collection.length );
		},

	}, App.Mixins.Syncable ) );



	/*
		  /$$$$$$            /$$ /$$                       /$$     /$$
		 /$$__  $$          | $$| $$                      | $$    |__/
		| $$  \__/  /$$$$$$ | $$| $$  /$$$$$$   /$$$$$$$ /$$$$$$   /$$  /$$$$$$  /$$$$$$$   /$$$$$$$
		| $$       /$$__  $$| $$| $$ /$$__  $$ /$$_____/|_  $$_/  | $$ /$$__  $$| $$__  $$ /$$_____/
		| $$      | $$  \ $$| $$| $$| $$$$$$$$| $$        | $$    | $$| $$  \ $$| $$  \ $$|  $$$$$$
		| $$    $$| $$  | $$| $$| $$| $$_____/| $$        | $$ /$$| $$| $$  | $$| $$  | $$ \____  $$
		|  $$$$$$/|  $$$$$$/| $$| $$|  $$$$$$$|  $$$$$$$  |  $$$$/| $$|  $$$$$$/| $$  | $$ /$$$$$$$/
		 \______/  \______/ |__/|__/ \_______/ \_______/   \___/  |__/ \______/ |__/  |__/|_______/
	*/

	/**
	 * Lessons Collection
	 * @since    [version]
	 * @version  [version]
	 */
	App.Collections.Lessons = Backbone.Collection.extend( _.defaults( {

		model: App.Models.Lesson,
		type_id: 'lesson',

	}, App.Mixins.Syncable, App.Mixins.SortableCollection ) );

	/**
	 * Sections Collection
	 * @since    [version]
	 * @version  [version]
	 */
	App.Collections.Sections = Backbone.Collection.extend( _.defaults( {

		model: App.Models.Section,
		type_id: 'section',

		/**
		 * Parse AJAX response
		 * @param    obj   response  JSON from the server
		 * @return   obj             relevant data from the server
		 * @since    [version]
		 * @version  [version]
		 */
		parse: function( response ) {
			return response.data;
		},

	}, App.Mixins.Syncable, App.Mixins.SortableCollection ) );



	/*
		 /$$    /$$ /$$
		| $$   | $$|__/
		| $$   | $$ /$$  /$$$$$$  /$$  /$$  /$$  /$$$$$$$
		|  $$ / $$/| $$ /$$__  $$| $$ | $$ | $$ /$$_____/
		 \  $$ $$/ | $$| $$$$$$$$| $$ | $$ | $$|  $$$$$$
		  \  $$$/  | $$| $$_____/| $$ | $$ | $$ \____  $$
		   \  $/   | $$|  $$$$$$$|  $$$$$/$$$$/ /$$$$$$$/
		    \_/    |__/ \_______/ \_____/\___/ |_______/
	*/

	/**
	 * Single Course View
	 * @since    [version]
	 * @version  [version]
	 */
	App.Views.Course = Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-lesson',

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-course-info',

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		id: function() {
			return 'llms-course-' + this.model.id;
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
		template: _.template( $( '#llms-course-template' ).html() ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {
			this.render();
		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

	}, App.Mixins.EditableView ) );

	/**
	 * Single Lesson View
	 * @since    [version]
	 * @version  [version]
	 */
	App.Views.Lesson = Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
				'data-section-id': this.model.get( 'section_id' ),
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-lesson',

		/**
		 * DOM Events
		 * @type    obj
		 * @since   [version]
		 * @versio  [version]
		 */
		events: _.defaults( {
			'drop-lesson': 'drop',
			'update-parent': 'update_parent',
			'click .llms-action-icon.section-prev': 'section_prev',
			'click .llms-action-icon.section-next': 'section_next',
			'click .llms-action-icon.trash': 'delete_lesson',
		}, App.Mixins.EditableView.events, App.Mixins.ShiftableView.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		id: function() {
			return 'llms-lesson-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: _.template( $( '#llms-lesson-template' ).html() ),

		/**
		 * Event handler for lesson deletion
		 * requires a confirmation before removing from collection & syncing
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		delete_lesson: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			var msg = LLMS.l10n.translate( 'Are you sure you want to permanently delete this lesson?' );

			if ( ! window.confirm( msg ) ) {
				return;
			}

			var del_id = 'delete_' + this.model.id;

			this.model.destroy( {
				beforeSend: function() {
					Instance.Status.add( del_id );
				},
				success: function( res ) {
					Instance.Status.remove( del_id );
				},
			} );

		},

		/**
		 * Draggable/Sortable DROP event
		 * @param    {obj}   event            js event obj
		 * @param    {obj}   $item            jQuery obj of the dropped item
		 * @param    {int}   to_section_id    id of the section the lesson was dropped into
		 * @param    {int}   from_section_id  id of the section the lesson came from (may be undefined)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		drop: function( event, $item, to_section_id, from_section_id ) {

			var self = this,
				to_collection = Instance.Syllabus.collection.get( to_section_id ).Lessons.collection,
				from_collection = ! this.model.collection ? null : Instance.Syllabus.collection.get( from_section_id ).Lessons.collection,
				auto_save = true;

			// create if the model doesn't have a collection
			if ( ! this.model.collection ) {
				var id = self.model.id;
				auto_save = false;
				self.model.set( 'section_id', to_section_id );
				to_collection.create( self.model, {
					beforeSend: function() {
						Instance.Status.add( id );
					},
					success: function( res ) {
						Instance.Status.remove( id );
						self.model.collection.sync_order();
					},
				} );
			}

			this.$el.trigger( 'update-sort', [ this.model, $item.index() + 1, to_collection, from_collection, auto_save ] );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

		/**
		 * Event handler for moving a lesson to the next section in the sections collection
		 * this moves lesson DOWN a section
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		section_next: function() {

			var from_section = this.model.get_section(),
				to_section = from_section.get_next(),
				from_collection = from_section.Lessons.collection
				to_collection = to_section.Lessons.collection,

			$( '#llms-section-' + to_section.id ).addClass( 'opened' );

			// update the parent
			this.model.set( 'section_id', to_section.id );

			// trigger resorts on the collections
			this.$el.trigger( 'update-sort', [ this.model, 1, to_collection, from_collection ] );

		},

		/**
		 * Event handler for moving a lesson to the previous section in the sections collection
		 * this moves lesson UP a section
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		section_prev: function() {

			var from_section = this.model.get_section(),
				to_section = from_section.get_prev(),
				from_collection = from_section.Lessons.collection
				to_collection = to_section.Lessons.collection,

			$( '#llms-section-' + to_section.id ).addClass( 'opened' );

			// update the parent
			this.model.set( 'section_id', to_section.id );

			// trigger resorts on the collections
			this.$el.trigger( 'update-sort', [ this.model, to_collection.next_order(), to_collection, from_collection ] );

		},

		/**
		 * jQuery UI sortable "receieve" callback
		 * when a lesson is moved to a new section the drop event handles most stuff
		 * but this updates the section_id attribute on the lesson
		 * @param    {obj}   event  js event object
		 * @param    {obj}   item   jQuery ui sortable item object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		update_parent: function( event, item ) {
			this.model.set( 'section_id', $( item ).closest( '.llms-section' ).attr( 'data-id' ) );
		},

	}, App.Mixins.EditableView, App.Mixins.ShiftableView ) );

	/**
	 * Single Section View
	 * @since    [version]
	 * @version  [version]
	 */
	App.Views.Section = Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-section',

		/**
		 * DOM Events
		 * @type    obj
		 * @since   [version]
		 * @versio  [version]
		 */
		events: _.defaults( {
			'drop-section': 'drop',
			'click .llms-action-icon.expand': 'lessons_show',
			'click .llms-action-icon.collapse': 'lessons_hide',
			'click .llms-action-icon.trash': 'delete_section',
		}, App.Mixins.EditableView.events, App.Mixins.ShiftableView.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    [version]
		 * @version  [version]
		 */
		id: function() {
			return 'llms-section-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: _.template( $( '#llms-section-template' ).html() ),

		/**
		 * Handles deletion of a section
		 * Will only delete empty sections
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		delete_section: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			// can't delete sections with lessons
			if ( this.model.Lessons.collection.length ) {
				alert( LLMS.l10n.translate( 'You must remove all lessons before deleting a section.' ) );
				return;
			}

			var del_id = 'delete_' + this.model.id;

			this.model.destroy( {
				beforeSend: function() {
					Instance.Status.add( del_id );
				},
				success: function( res ) {
					Instance.Status.remove( del_id );
				},
			} );

		},

		/**
		 * jQuery UI sortable drop event handler
		 * @param    {obj}   event  js event object
		 * @param    {int}   index  new index of the dropped element
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		drop: function( event, index ) {

			var self = this,
				auto_save = true;

			// create if the model doesn't have a collection
			if ( ! this.model.collection ) {
				var id = self.model.id;
				auto_save = false;
				Instance.Syllabus.collection.create( self.model, {
					beforeSend: function() {
						Instance.Status.add( id );
					},
					success: function( res ) {
						Instance.Status.remove( id );
						self.model.collection.sync_order();
					},
				} );
			}

			self.$el.trigger( 'update-sort', [ self.model, index + 1, self.model.collection, null, auto_save ] );

		},

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {
			this.listenTo( this.model, 'sync', this.render );
		},

		/**
		 * Hide lessons in the section
		 * @param    {obj}   e  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		lessons_hide: function( e ) {
			e.preventDefault();
			this.$el.removeClass( 'opened' );
		},

		/**
		 * Show lessons in the section
		 * @param    {obj}   e  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		lessons_show: function( e ) {
			e.preventDefault();
			this.$el.addClass( 'opened' );
		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {

			// render inside
			this.$el.html( this.template( this.model.toJSON() ) );

			// setup lessons child view & collection
			this.model.Lessons = new App.Views.LessonList( {
				el: this.$el.find( '.llms-lessons' ),
				collection: new App.Collections.Lessons,
			} );
			this.model.Lessons.collection.add( this.model.get( 'lessons' ) );

			// if the id has changed (when creating a new section for example) update the attributes and id
			if ( this.$el.attr( 'id' ) != this.model.id ) {
				this.$el.attr( 'id', this.id() );
				this.$el.attr( this.attributes() );
			}

			return this;
		},

	}, App.Mixins.EditableView, App.Mixins.ShiftableView ) );

	/**
	 * Lesson List (collection) view
	 * @since    [version]
	 * @version  [version]
	 */
	App.Views.LessonList = Backbone.View.extend( _.defaults( {

		/**
		 * Add a lesson to the collection
		 * @param    {obj}   lesson  model of the lesson
		 * @since    [version]
		 * @version  [version]
		 */
		add_one: function( lesson ) {
			var view = new App.Views.Lesson( { model: lesson } );
			this.$el.append( view.render().el );
		},

		/**
		 * Remove a lesson from a collection
		 * @param    {obj}      lesson      model of the lesson to remove
		 * @param    {obj}      collection  collection to remove the lesson from
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		destroy_one: function( lesson, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},

		/**
		 * Initializer
		 * Bind collection events
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.destroy_one );
			this.listenTo( this.collection, 'rerender', this.render );
			App.Methods.sortable();

		},

		/**
		 * Render the view
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {
			this.$el.children().remove();
			this.collection.each( this.add_one, this );
			return this;
		},

	}, App.Mixins.SortableView ) );

	/**
	 * Section list (colletion) view
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	App.Views.SectionList = Backbone.View.extend( _.defaults( {

		/**
		 * DOM element for the view to be added within
		 * @type  {obj}
		 */
		el: $( '#llms-sections' ),

		/**
		 * collection association
		 * @type  {obj}
		 */
		collection: new App.Collections.Sections,

		/**
		 * Add a section to the collection
		 * @param    {obj}   section  a section model
		 * @since    [version]
		 * @version  [version]
		 */
		add_one: function( section ) {
			var view = new App.Views.Section( { model: section } );
			this.$el.append( view.render().el );
		},

		/**
		 * Delete a section from the collection
		 * @param    {obj}   section     a model of the section
		 * @param    {obj}   collection  the collection to remove it from
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		destroy_one: function( section, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},

		/**
		 * Initializer
		 * Setup dom event
		 * & fetch the starting data for the collection
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		initialize: function() {

			var self = this;

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.destroy_one );
			this.listenTo( this.collection, 'rerender', this.render );

			this.collection.fetch( {
				beforeSend: function() {
					LLMS.Spinner.start( self.$el );
					// start the mini spinner that never stops
					LLMS.Spinner.start( $( '#llms-spinner-el' ), 'small' );
				},
				success: function( res ) {
					LLMS.Spinner.stop( self.$el );
					App.Methods.draggable();
					App.Methods.sortable();
				},
			} );


		},

		/**
		 * Render the view
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		render: function() {
			this.$el.children().remove();
			this.collection.each( this.add_one, this );
			return this;
		},

	}, App.Mixins.SortableView ) );

	/**
	 * "Tools" sidebar view
	 * @since    [version]
	 * @version  [version]
	 */
	App.Views.Tools = Backbone.View.extend( {

		/**
		 * Dom Element
		 * @type  {obj}
		 */
		el: $( '#llms-builder-tools' ),

		/**
		 * Dom Events
		 * @type     {Object}
		 * @since    [version]
		 * @version  [version]
		 */
		events: {
			'click button.llms-add-item': 'add_item',
			'click a.bulk-toggle': 'bulk_toggle',
		},

		/**
		 * Add a new item to the syllabus (section or lesson)
		 * @param    {obj}   event  js event object
		 * @since    [version]
		 * @version  [version]
		 */
		add_item: function( event ) {

			event.preventDefault();

			var $btn = $( event.target ),
				model = $btn.attr( 'data-model' ),
				collection = 'section' === model ? Instance.Syllabus.collection : App.Methods.get_last_section().Lessons.collection;

			var temp_id = _.uniqueId( model + '_temp_' );

			collection.create( { id: temp_id }, {
				beforeSend: function() {
					Instance.Status.add( temp_id );
				},
				success: function( res ) {
					Instance.Status.remove( temp_id );
				},
			} );

			var $el = $( '#llms-' + model + '-' + temp_id );
			$el.addClass( 'brand-new' );

			setTimeout( function() {
				$el.removeClass( 'brand-new' );
			}, 10 );

			// open section
			if ( 'lesson' === model ) {
				$el.closest( '.llms-section' ).addClass( 'opened' );
			}

			// scroll to bottom
			var $wrap = $( '#llms-course-syllabus' );
			$wrap.animate( {
				scrollTop: $wrap[0].scrollHeight - $wrap[0].clientHeight,
			}, 200 );

			App.Methods.sortable();

		},

		/**
		 * Bulk expan and collapse the syllabus via click events
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		bulk_toggle: function( event ) {

			event.preventDefault();
			var $btn = $( event.target ),
				which = $btn.attr( 'data-action' );
			$( '.llms-section .llms-action-icon.' + which ).trigger( 'click' );

		}

	} );

	/**
	 * Main Instance
	 * @type     {Object}
	 * @since    [version]
	 * @version  [version]
	 */
	var Instance = {
		Course: new App.Views.Course( {
			model: new App.Models.Course( window.llms_builder ),
		} ),
		Syllabus: new App.Views.SectionList,
		Tools: new App.Views.Tools,
		Status: {
			saving: [],
			add: function( id ) {
				this.saving.push( id );
				this.update_dom();
			},
			remove: function( id ) {
				this.saving = _.without( this.saving, id );
				this.update_dom();
			},
			update_dom: function() {
				console.log( this.saving );
				var status = this.saving.length ? 'saving' : 'complete';
				$( '#save-status' ).attr( 'data-status', status );
			},
		},
	};

	// set the height of the fixed builder area
	$( '.llms-course-builder' ).height( $( window ).height() - 62 ); // @shame magic numbers...

	// warn during unloads while we're still processing saves
	$( window ).on( 'beforeunload', function( e ) {
		if ( Instance.Status.saving.length ) {
			return LLMS.l10n.translate( 'If you leave now your changes may not be saved!' );
		}
	} );

} )( jQuery );
