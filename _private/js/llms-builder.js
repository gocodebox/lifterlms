( function( $ ) {

	var App = {
		Collections: {},
		Models: {},
		Views: {},
		Methods: {
			get_last_section: function() {
				return Instance.Syllabus.collection.at( Instance.Syllabus.collection.length - 1 );
			},
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

		Mixins: {

			EditableView: {
				events: {
					'keydown .llms-editable-title': 'on_keydown',
				},

				on_keydown: function( event ) {

					event.stopPropagation();

					var key = event.which || event.keyCode;

					switch ( key ) {

						case 9: // tab
							this.save_edits( event );
						break;

						case 13: // enter
							event.preventDefault();
							this.save_edits( event );
							event.target.blur();
						break;

						case 27: // escape
							event.preventDefault();
							this.revert_edits( event );
							event.target.blur();
						break;
					}

				},

				revert_edits: function( event ) {
					var $el = $( event.target ),
						val = $el.attr( 'data-original-content' );
					$el.text( val );
				},

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

			ShiftableView: {

				events: {
					'click .llms-action-icon.shift-down': 'shift_down',
					'click .llms-action-icon.shift-up': 'shift_up',
				},

				shift_down: function( e ) {
					e.stopPropagation();
					e.preventDefault();
					this.$el.trigger( 'update-sort', [ this.model, this.model.get( 'order' ) + 1, this.model.collection ] );
				},

				shift_up: function( e ) {
					e.stopPropagation();
					e.preventDefault();
					this.$el.trigger( 'update-sort', [ this.model, this.model.get( 'order' ) - 1, this.model.collection ] );
				},

			},

			SortableView: {

				events: {
					'update-sort': 'update_sort',
				},

				sort_collection: function( collection ) {

					collection.each( function( model, index ) {
						model.set( 'order', index + 1 );
					} );
					collection.trigger( 'rerender' );

				},

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

			SortableCollection: {

				comparator: 'order',

				events: {
					'change:order': 'sort',
				},

				next_order: function() {
					if ( ! this.length ) {
						return 1;
					}
					return this.last().get( 'order' ) + 1;
				},

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

			Syncable: {
				url: ajaxurl,
				action: 'llms_builder',

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


	App.Models.Course = Backbone.Model.extend( _.defaults( {

		type_id: 'course',

		defaults: function() {
			return {
				title: 'New Course',
				edit_url: '',
				view_url: '',
			}
		},

	}, App.Mixins.Syncable ) );

	App.Models.Lesson = Backbone.Model.extend( _.defaults( {

		type_id: 'lesson',

		get_section: function() {
			return Instance.Syllabus.collection.get( this.get( 'section_id' ) );
		},

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

	}, App.Mixins.Syncable ) );

	App.Models.Section = Backbone.Model.extend( _.defaults( {

		type_id: 'section',

		get_next: function() {
			return this.collection.at( this.collection.indexOf( this ) + 1 );
		},

		get_prev: function() {
			return this.collection.at( this.collection.indexOf( this ) - 1 );
		},

		is_last: function() {
			return ( this.get( 'order') === this.collection.length );
		},

		defaults: function() {
			var order = this.collection ? this.collection.next_order() : 1;
			return {
				title: 'New Section',
				type: 'section',
				order: order,
			};
		},

	}, App.Mixins.Syncable ) );

	App.Collections.Lessons = Backbone.Collection.extend( _.defaults( {

		model: App.Models.Lesson,
		type_id: 'lesson',

	}, App.Mixins.Syncable, App.Mixins.SortableCollection ) );

	App.Collections.Sections = Backbone.Collection.extend( _.defaults( {

		model: App.Models.Section,
		type_id: 'section',

		parse: function( response ) {
			return response.data;
		},

	}, App.Mixins.Syncable, App.Mixins.SortableCollection ) );


	App.Views.Course = Backbone.View.extend( _.defaults( {

		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},
		className: 'llms-builder-item llms-lesson',
		el: '#llms-course-info',
		id: function() {
			return 'llms-course-' + this.model.id;
		},
		tagName: 'div',
		template: _.template( $( '#llms-course-template' ).html() ),

		initialize: function() {
			this.render();
		},

		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

	}, App.Mixins.EditableView ) );

	App.Views.Lesson = Backbone.View.extend( _.defaults( {

		attributes: function() {
			return {
				'data-id': this.model.id,
				'data-section-id': this.model.get( 'section_id' ),
			};
		},
		className: 'llms-builder-item llms-lesson',
		events: _.defaults( {
			'drop-lesson': 'drop',
			'update-parent': 'update_parent',
			'click .llms-action-icon.section-prev': 'section_prev',
			'click .llms-action-icon.section-next': 'section_next',
			'click .llms-action-icon.trash': 'delete_lesson',
		}, App.Mixins.EditableView.events, App.Mixins.ShiftableView.events ),
		id: function() {
			return 'llms-lesson-' + this.model.id;
		},
		tagName: 'li',
		template: _.template( $( '#llms-lesson-template' ).html() ),

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

		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

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

		update_parent: function( event, item ) {
			this.model.set( 'section_id', $( item ).closest( '.llms-section' ).attr( 'data-id' ) );
		},

	}, App.Mixins.EditableView, App.Mixins.ShiftableView ) );

	App.Views.Section = Backbone.View.extend( _.defaults( {

		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},
		className: 'llms-builder-item llms-section',
		events: _.defaults( {
			'drop-section': 'drop',
			'click .llms-action-icon.expand': 'lessons_show',
			'click .llms-action-icon.collapse': 'lessons_hide',
			'click .llms-action-icon.trash': 'delete_section',
		}, App.Mixins.EditableView.events, App.Mixins.ShiftableView.events ),
		id: function() {
			return 'llms-section-' + this.model.id;
		},
		tagName: 'li',
		template: _.template( $( '#llms-section-template' ).html() ),

		initialize: function() {
			this.listenTo( this.model, 'sync', this.render );
		},

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

		lessons_hide: function( e ) {
			e.preventDefault();
			this.$el.removeClass( 'opened' );
		},

		lessons_show: function( e ) {
			e.preventDefault();
			this.$el.addClass( 'opened' );
		},

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

	App.Views.LessonList = Backbone.View.extend( _.defaults( {

		initialize: function() {

			this.listenTo( this.collection, 'add', this.add_one );
			this.listenTo( this.collection, 'destroy', this.destroy_one );
			this.listenTo( this.collection, 'rerender', this.render );
			App.Methods.sortable();

		},

		add_one: function( lesson ) {
			var view = new App.Views.Lesson( { model: lesson } );
			this.$el.append( view.render().el );
		},

		destroy_one: function( lesson, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},

		render: function() {
			this.$el.children().remove();
			this.collection.each( this.add_one, this );
			return this;
		},

	}, App.Mixins.SortableView ) );


	App.Views.SectionList = Backbone.View.extend( _.defaults( {

		el: $( '#llms-sections' ),
		collection: new App.Collections.Sections,

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
				},
			} );

		},

		add_one: function( section ) {
			var view = new App.Views.Section( { model: section } );
			this.$el.append( view.render().el );
		},

		destroy_one: function( section, collection ) {
			this.sort_collection( collection );
			collection.sync_order();
		},

		render: function() {
			this.$el.children().remove();
			this.collection.each( this.add_one, this );
			return this;
		},

	}, App.Mixins.SortableView ) );

	App.Views.Tools = Backbone.View.extend( {

		el: $( '#llms-builder-tools' ),

		events: {
			'click button.llms-add-item': 'add_item',
			'click a.bulk-toggle': 'bulk_toggle',
		},

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

		bulk_toggle: function( event ) {

			event.preventDefault();
			var $btn = $( event.target ),
				which = $btn.attr( 'data-action' );
			$( '.llms-section .llms-action-icon.' + which ).trigger( 'click' );

		}

	} );

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

	App.Methods.draggable();
	App.Methods.sortable();

	$( '.llms-course-builder' ).height( $( window ).height() - 62 ); // @shame magic numbers...

	// warn during unloads while we're still processing saves
	$( window ).on( 'beforeunload', function( e ) {
		if ( Instance.Status.saving.length ) {
			return LLMS.l10n.translate( 'If you leave now your changes may not be saved!' );
		}
	} );


} )( jQuery );
