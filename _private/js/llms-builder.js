( function( $ ) {

	/**
	 * Main Application Object
	 * @type     {Object}
	 * @since    3.13.0
	 * @version  3.14.8
	 */
	var App = {

		$elements: {
			$main: $( '.llms-builder-main' ),
		},

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
			 * @since    3.13.0
			 * @version  3.13.0
			 */
			get_last_section: function() {
				return Instance.Syllabus.collection.at( Instance.Syllabus.collection.length - 1 );
			},

			/**
			 * Bind Draggable Events
			 * Powers draggable items in the tools sidebar
			 * @return   void
			 * @since    3.13.0
			 * @version  3.13.0
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
			 * @since    3.13.0
			 * @version  3.13.0
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
						$( '.llms-lessons' ).addClass( 'dragging' );
					},
					stop: function( event, ui ) {

						event.stopPropagation();

						var from_section = ui.item.attr( 'data-section-id' ),
							curr_section = ui.item.closest( '.llms-section' ).attr( 'data-id' );

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
			 * @since    3.13.0
			 * @version  3.14.1
			 */
			EditableView: {

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    3.13.0
				 * @version  3.13.0
				 */
				events: {
					'focusout .llms-editable-title': 'on_blur',
					'keydown .llms-editable-title': 'on_keydown',
				},

				/**
				 * Determine if changes have been made to the element
				 * @param    {[obj]}   event  js event object
				 * @return   {Boolean}        true when changes have been made, false otherwise
				 * @since    3.13.0
				 * @version  3.13.0
				 */
				has_changed: function( event ) {
					var $el = $( event.target );
					return ( $el.attr( 'data-original-content' ) !== $el.text() );
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
						changed = this.has_changed( event );

					if ( changed ) {
						this.save_edits( event );
					}

				},

				/**
				 * Keydown function for .llms-editable-title elements
				 * Blurs
				 * @param    {obj]}   event  js event object
				 * @return   void
				 * @since    3.13.0
				 * @version  3.14.1
				 */
				on_keydown: function( event ) {

					event.stopPropagation();

					var self = this,
						key = event.which || event.keyCode;

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

				/**
				 * Helper to undo changes
				 * Bound to "escape" key via on_keydwon function
				 * @param    {[type]}   event  js event object
				 * @return   void
				 * @since    3.13.0
				 * @version  3.13.0
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
				 * @since    3.13.0
				 * @version  3.13.0
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
			 * @since    3.13.0
			 * @version  3.13.0
			 */
			ShiftableView: {

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    3.13.0
				 * @version  3.13.0
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
				 * @since    3.13.0
				 * @version  3.13.0
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
				 * @since    3.13.0
				 * @version  3.13.0
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
			 * @since    3.13.0
			 * @version  3.13.0
			 */
			SortableView: {

				/**
				 * DOM Events
				 * @type  {Object}
				 * @since    3.13.0
				 * @version  3.13.0
				 */
				events: {
					'update-sort': 'update_sort',
				},

				/**
				 * Resorst an out-of-order collection by the order property on its models
				 * Rerenders the view when completed
				 * @param    {obj}   collection  a backbone collection with models that have an "order" prop
				 * @return   void
				 * @since    3.13.0
				 * @version  3.13.0
				 */
				sort_collection: function( collection ) {

					if ( collection.length ) {

						collection.each( function( model, index ) {
							model.set( 'order', index + 1 );
						} );

					}

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
				 * @since    3.13.0
				 * @version  3.13.0
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
			 * @since    3.13.0
			 * @version  3.13.0
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
				 * @since    3.13.0
				 * @version  3.13.0
				 */
				events: {
					'change:order': 'sort',
				},

				/**
				 * Retrieve the next order in the collection
				 * @return   int
				 * @since    3.13.0
				 * @version  3.13.0
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
				 * @since    3.13.0
				 * @version  3.13.0
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
			 * @since    3.13.0
			 * @version  3.13.0
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
				 * @since    3.13.0
				 * @version  3.13.0
				 */
				sync: function( method, object, options ) {

					if ( typeof options.data === 'undefined' ) {
						options.data = {};
					}

					if ( object instanceof Backbone.Model ) {
						object_type = 'model';
						// console.log( object.hasChanged(), object.changed );
					} else if ( object instanceof Backbone.Collection ) {
						object_type = 'collection';
						// console.log( object );
						// object.each( function( model ) {
						// 	console.log( model.hasChanged(), model.changed );
						// } );
					}

					options.data.course_id = window.llms_builder.course.id;
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
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	App.Models.Course = Backbone.Model.extend( _.defaults( {

		type_id: 'course',

		/**
		 * New Course Defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
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
	 * @since    3.13.0
	 * @version  3.14.8
	 */
	App.Models.Lesson = Backbone.Model.extend( _.defaults( {

		type_id: 'lesson',

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.14.8
		 */
		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1,
				section_id = App.Methods.get_last_section().id;
			return {
				title: LLMS.l10n.translate( 'New Lesson' ),
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
			};
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.14.0
		 * @version  3.14.4
		 */
		initialize: function() {

			this.listenTo( this, 'detach', this.detach );

		},

		/**
		 * Detach lesson from section
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		detach: function() {

			var id = 'detach_' + this.id;

			this.set( 'section_id', '' );
			this.collection.remove( this.id );
			this.save( null, {
				beforeSend: function() {
					Instance.Status.add( id );
				},
				success: function( res ) {
					Instance.Status.remove( id );
				},
			} );
		},

		/**
		 * Retrieve the parent section of the lesson
		 * @return   {obj}   App.Models.Section
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_section: function() {
			return Instance.Syllabus.collection.get( this.get( 'section_id' ) );
		},

	}, App.Mixins.Syncable ) );

	/**
	 * Section Model
	 * @since    3.13.0
	 * @version  3.14.4
	 */
	App.Models.Section = Backbone.Model.extend( _.defaults( {

		type_id: 'section',

		/**
		 * New section defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1;
			return {
				active: false,
				title: 'New Section',
				type: 'section',
				lessons: [],
				order: order,
			};
		},

		/**
		 * Retrieve the next section in the section's collection
		 * @return   obj     App.Models.Section
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_next: function() {
			return this.collection.at( this.collection.indexOf( this ) + 1 );
		},

		/**
		 * Retrieve the prev section in the section's collection
		 * @return   obj     App.Models.Section
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_prev: function() {
			return this.collection.at( this.collection.indexOf( this ) - 1 );
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
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	App.Collections.Lessons = Backbone.Collection.extend( _.defaults( {

		model: App.Models.Lesson,
		type_id: 'lesson',


	}, App.Mixins.Syncable, App.Mixins.SortableCollection ) );

	/**
	 * Sections Collection
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	App.Collections.Sections = Backbone.Collection.extend( _.defaults( {

		model: App.Models.Section,
		type_id: 'section',

		/**
		 * Parse AJAX response
		 * @param    obj   response  JSON from the server
		 * @return   obj             relevant data from the server
		 * @since    3.13.0
		 * @version  3.13.0
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
	 * @since    3.13.0
	 * @version  3.14.0
	 */
	App.Views.Course = Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
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
		template: wp.template( 'llms-course-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {
			this.render();
			this.listenTo( this.model, 'sync', this.render );
		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

	}, App.Mixins.EditableView ) );

	/**
	 * Single Lesson View
	 * @since    3.13.0
	 * @version  3.14.4
	 */
	App.Views.Lesson = Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @type     obj
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		events: _.defaults( {
			'drop-lesson': 'drop',
			'update-parent': 'update_parent',
			'click .llms-action-icon.section-prev': 'section_prev',
			'click .llms-action-icon.section-next': 'section_next',
			'click .llms-action-icon.trash': 'delete_lesson',
			'click .llms-action-icon.detach': 'detach_lesson',
		}, App.Mixins.EditableView.events, App.Mixins.ShiftableView.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
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
		template: wp.template( 'llms-lesson-template' ),

		/**
		 * Event handler for lesson deletion
		 * requires a confirmation before removing from collection & syncing
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * Removes a lesson from the course (turns it into an orphan)
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		detach_lesson: function( event ) {

			event.stopPropagation();
			event.preventDefault();
			this.model.trigger( 'detach' );

		},

		/**
		 * Draggable/Sortable DROP event
		 * @param    {obj}   event            js event obj
		 * @param    {obj}   $item            jQuery obj of the dropped item
		 * @param    {int}   to_section_id    id of the section the lesson was dropped into
		 * @param    {int}   from_section_id  id of the section the lesson came from (may be undefined)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function() {
			this.listenTo( this.model, 'sync', this.render );
		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			if ( ! this.model.get( 'section_id' ) ) {
				return this;
			}
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

		/**
		 * Event handler for moving a lesson to the next section in the sections collection
		 * this moves lesson DOWN a section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		update_parent: function( event, item ) {
			this.model.set( 'section_id', $( item ).closest( '.llms-section' ).attr( 'data-id' ) );
		},

	}, App.Mixins.EditableView, App.Mixins.ShiftableView ) );

	/**
	 * Existing Lesson Popover content View
	 * @since    3.13.0
	 * @version  3.14.0
	 */
	App.Views.LessonSearchPopover = Backbone.View.extend( {

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		events: {
			'select2:select': 'add_lesson',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'select',

		/**
		 * Select event, adds the existing lesson to the course
		 * @param    obj   event  select2:select event object
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		add_lesson: function( event ) {

			WebuiPopovers.hide( $( '#llms-existing-lesson' ) );

			Instance.Tools.create_item( 'lesson', {
				id: event.params.data.id,
				title: event.params.data.title
			} );

			this.$el.val( null ).trigger( 'change' );

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		render: function() {
			var self = this;
			setTimeout( function () {
				self.$el.llmsSelect2( {
					ajax: {
						dataType: 'JSON',
						delay: 250,
						method: 'POST',
						url: window.ajaxurl,
						data: function( params ) {
							return {
								action: 'llms_builder',
								action_type: 'search',
								course_id: window.llms_builder.course.id,
								term: params.term,
								page: params.page,
								_ajax_nonce: wp_ajax_data.nonce,
							};
						},
						// error: function( xhr, status, error ) {
						// 	console.log( status, error );
						// },
					},
					placeholder: LLMS.l10n.translate( 'Search for existing lessons...' ),
					dropdownParent: $( '.webui-popover-inner' ),
					width: '100%',
				} );
			}, 0 );
			return this;

		},

	} );

	/**
	 * Single Section View
	 * @since    3.13.0
	 * @version  3.14.0
	 */
	App.Views.Section = Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * Return CSS classes for the html wrapper element
		 * @return   string
		 * @since    3.14.5
		 * @version  3.14.5
		 */
		className: function() {
			var classes = [ 'llms-builder-item', 'llms-section' ];
			if ( this.model.get( 'opened' ) ) {
				classes.push( 'opened' );
			}
			return classes.join( ' ' );
		},

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.13.0
		 * @version  3.14.0
		 */
		events: _.defaults( {
			'click .llms-action-icon.expand': 'lessons_show',
			'click .llms-action-icon.collapse': 'lessons_hide',
			'click .llms-action-icon.trash': 'delete_section',
			'drop-section': 'drop',
		}, App.Mixins.EditableView.events, App.Mixins.ShiftableView.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
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
		template: wp.template( 'llms-section-template' ),

		/**
		 * Handles deletion of a section
		 * Will only delete empty sections
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		initialize: function() {

			// this.listenTo( this.model, 'sync', this.render );

			if ( ! this.model.Lessons ) {

				// setup lessons child view & collection
				this.model.Lessons = new App.Views.LessonList( {
					collection: new App.Collections.Lessons,
				} );
				this.model.Lessons.collection.add( this.model.get( 'lessons' ) );

			}


		},

		/**
		 * Hide lessons in the section
		 * @param    {obj}   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		lessons_hide: function( e ) {
			e.preventDefault();
			this.$el.removeClass( 'opened' );
			this.model.set( 'opened', false );
		},

		/**
		 * Show lessons in the section
		 * @param    {obj}   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		lessons_show: function( e ) {
			e.preventDefault();
			this.$el.addClass( 'opened' );
			this.model.set( 'opened', true );
		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		render: function() {

			// render inside
			this.$el.html( this.template( this.model.toJSON() ) );

			this.model.Lessons.setElement( this.$el.find( '.llms-lessons' ) ).render();

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
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	App.Views.LessonList = Backbone.View.extend( _.defaults( {

		/**
		 * Add a lesson to the collection
		 * @param    {obj}   lesson  model of the lesson
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		add_one: function( lesson ) {
			var view = new App.Views.Lesson( { model: lesson } );
			this.$el.append( view.render().el );
		},

		/**
		 * Initializer
		 * Bind collection events
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		initialize: function() {

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.remove_one );
			this.listenTo( this.collection, 'remove', this.remove_one );
			this.listenTo( this.collection, 'rerender', this.render );

		},

		/**
		 * Remove a lesson from a collection
		 * @param    {obj}      lesson      model of the lesson to remove
		 * @param    {obj}      collection  collection to remove the lesson from
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		remove_one: function( lesson, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},


		/**
		 * Render the view
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		render: function() {
			this.$el.children().remove();
			this.collection.each( this.add_one, this );
			App.Methods.sortable();
			return this;
		},

	}, App.Mixins.SortableView ) );

	/**
	 * Section list (colletion) view
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
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
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			var self = this;

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.destroy_one );
			this.listenTo( this.collection, 'rerender', this.render );

			this.collection.fetch( {
				beforeSend: function() {
					App.$elements.$main.addClass( 'loading' );
					LLMS.Spinner.start( App.$elements.$main );

				},
				success: function( res ) {

					App.$elements.$main.removeClass( 'loading' );
					LLMS.Spinner.stop( App.$elements.$main );
					App.Methods.draggable();
					App.Methods.sortable();
					// start the mini spinner that never stops
					LLMS.Spinner.start( $( '#llms-spinner-el' ), 'small' );

				},
			} );

		},

		/**
		 * Render the view
		 * @return   void
		 * @since    3.13.0
		 * @version  3.14.5
		 */
		render: function() {

			this.$el.children().remove();

			if ( this.collection.length ) {
				this.collection.each( this.add_one, this );
			}

			App.Methods.sortable();

			return this;

		},

	}, App.Mixins.SortableView ) );

	/**
	 * "Tools" sidebar view
	 * @since    3.13.0
	 * @version  3.14.4
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
		 * @since    3.13.0
		 * @version  3.14.4
		 */
		events: {
			'click button.llms-add-item': 'add_item',
			'click button#llms-existing-lesson': 'show_search_popover',
			'click a.bulk-toggle': 'bulk_toggle',
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			this.listenTo( Instance.Syllabus.collection, 'add', this.maybe_disable );
			this.listenTo( Instance.Syllabus.collection, 'remove', this.maybe_disable );
			this.listenTo( Instance.Syllabus.collection, 'sync', this.maybe_disable );

		},

		/**
		 * Add a new item to the syllabus (section or lesson)
		 * @param    {obj}   event  js event object
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		add_item: function( event ) {

			event.preventDefault();

			var $btn = $( event.target ),
				type = $btn.attr( 'data-model' );

			this.create_item( type, {
				id: _.uniqueId( type + '_temp_' )
			} );

		},

		/**
		 * Bulk expan and collapse the syllabus via click events
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		bulk_toggle: function( event ) {

			event.preventDefault();
			var $btn = $( event.target ),
				which = $btn.attr( 'data-action' );
			$( '.llms-section .llms-action-icon.' + which ).trigger( 'click' );

		},

		/**
		 * Creates a model (section or lesson)
		 * @param    string   type   model type [section or lesson]
		 * @param    obj      model  model object
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		create_item: function( type, model ) {

			var collection = 'section' === type ? Instance.Syllabus.collection : App.Methods.get_last_section().Lessons.collection;

			collection.create( model, {
				beforeSend: function() {
					Instance.Status.add( model.id );
				},
				success: function( res ) {
					Instance.Status.remove( model.id );
				},
			} );

			var $el = $( '#llms-' + type + '-' + model.id );
			$el.addClass( 'brand-new' );

			setTimeout( function() {
				$el.removeClass( 'brand-new' );
			}, 10 );

			// open section
			if ( 'lesson' === type ) {
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
		 * Disable the lesson tools when there's no section in the course
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		maybe_disable: function() {

			var $btns = $( '#llms-new-lesson, #llms-existing-lesson' );

			if ( ! Instance.Syllabus.collection.length ) {
				$btns.attr( 'disabled', 'disabled' );
			} else {
				$btns.removeAttr( 'disabled' );
			}

		},

		/**
		 * Show a popover to select an existing lesson
		 * @param    obj   e  js click event obj
		 * @return   void
		 * @since    3.14.4
		 * @version  3.14.4
		 */
		show_search_popover: function( e ) {

			WebuiPopovers.show( e.target, {
				animation: 'pop',
				backdrop: true,
				content: new App.Views.LessonSearchPopover().render().$el,
				closeable: true,
				dismissible: true,
				multi: false,
				placement: 'left',
				title: LLMS.l10n.translate( 'Add an Existing Lesson' ),
				trigger: 'manual',
				width: 340,
				onShow: function( $popover ) {
					$( '.webui-popover-backdrop' ).one( 'click', function() {
						WebuiPopovers.hide( e.target );
					} );
				},
			} );

		},

	} );

	/**
	 * "Tools" sidebar view
	 * @since    3.13.0
	 * @version  3.14.0
	 */
	App.Views.Tutorial = Backbone.View.extend( {

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-builder-tutorial',

		/**
		 * Dom Events
		 * @type     {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		events: {
			'click #llms-start-tut': 'start',
		},

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-builder-tutorial-template' ),

		is_active: false,
		current_step: 0,
		steps: [],

		/**
		 * Get object of current step data
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		get_current_step: function() {
			return this.steps[ this.current_step ];
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			this.listenTo( Instance.Syllabus.collection, 'add', this.maybe_render );
			this.listenTo( Instance.Syllabus.collection, 'remove', this.maybe_render );
			this.listenTo( Instance.Syllabus.collection, 'sync', this.maybe_render );

			this.steps = window.llms_builder.tutorial;

		},

		/**
		 * Enables / Disables the tutorial box
		 * Shows the tutorial when there's no sections in the course
		 * Hides when there are sectiosn in the course
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		maybe_render: function() {

			if ( ! Instance.Syllabus.collection.length ) {
				this.render();
			} else {
				this.$el.fadeOut( 200 );
			}

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			this.$el.html( this.template() );
			this.$el.fadeIn( 200 );
			return this;

		},

		/**
		 * Start the popover tutorial walkthrough
		 * @param    obj   e  js event object
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		start: function( e ) {
			e.preventDefault();
			this.show_next_step( 0 );
		},

		/**
		 * Show a popover for the next step and bind necessary one-time events
		 * @param    int   next_step  step index
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		show_next_step: function( next_step ) {

			this.current_step = next_step;

			var self = this,
				step = this.get_current_step(),
				$content = $( '<div class="llms-tutorial-content" />' );

			$content.append( '<p>' + step.content_main + '</p>' );
			if ( step.content_action ) {
				$content.append( '<p><strong>' + step.content_action + '</strong></p>' );
			}

			if ( step.buttons ) {
				$.each( step.buttons, function( type, text ) {
					var $btn = $( '<button class="llms-button-primary small" type="button">' + text + '</button>' );
					$content.append( $btn );
				} );
			}

			WebuiPopovers.show( step.el, {
				animation: 'pop',
				// backdrop: true,
				content: $content,
				closeable: true,
				// container: '.wrap.llms-course-builder',
				// multi: true,
				placement: step.placement || 'auto',
				title: ( this.current_step + 1 ) + '. ' + step.title,
				trigger: 'manual',
				width: 340,
				onShow: function( $el ) {

					self.is_active = true;

					$( step.el ).add( $el.find( 'button' ) ).one( 'click', function() {
						$el.remove();
						if ( self.current_step < self.steps.length - 1 ) {
							self.show_next_step( self.current_step + 1 );
						} else {
							self.is_active = false;
						}
					} );

				},
				onHide: function() {

					self.is_active = false;

				},
			} );

		},

	} );

	/**
	 * Main Instance
	 * @type     {Object}
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	var Instance = {
		Course: new App.Views.Course( {
			model: new App.Models.Course( window.llms_builder.course ),
		} ),
		Syllabus: new App.Views.SectionList,
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
				var status = this.saving.length ? 'saving' : 'complete';
				$( '#save-status' ).attr( 'data-status', status );
			},
		},
	};

	Instance.Tools = new App.Views.Tools;
	Instance.Tutorial = new App.Views.Tutorial;

	// prevent actions outside the intended tutorial action (when the tutorial is active)
	$( '.wrap.llms-course-builder' ).on( 'click', 'a, button', function( event ) {
		var $el = $( this );
		if ( Instance.Tutorial.is_active ) {
			var step = Instance.Tutorial.get_current_step();
			if ( $( step.el ) !== $el ) {
				event.preventDefault();
				$( step.el ).fadeOut( 100 ).fadeIn( 300 );
			}
		}
	} );

	/**
	 * Set the fixed height of the builder area
	 * @return   void
	 * @since    3.14.2
	 * @version  3.14.2
	 */
	function resize_builder() {
		$( '.llms-course-builder' ).height( $( window ).height() - 62 ); // @shame magic numbers...
	}

	var resize_timeout;
	$( window ).on( 'resize', function() {

		clearTimeout( resize_timeout );
		resize_timeout = setTimeout( function() {
			resize_builder();
		}, 250 );

	} );

	// resize on page load
	resize_builder();

	// warn during unloads while we're still processing saves
	$( window ).on( 'beforeunload', function( e ) {
		if ( Instance.Status.saving.length ) {
			return LLMS.l10n.translate( 'If you leave now your changes may not be saved!' );
		}
	} );

	// expose the instance to the window
	window.llms_builder.Instance = Instance;

} )( jQuery );
