/**
 * Sync builder data to the server
 *
 * @since    3.16.0
 * @version  3.19.4
 */
define( [], function() {

	return function( Course, settings ) {

		this.saving = false;

		var self              = this,
			autosave          = true,
			check_interval    = null,
			check_interval_ms = settings.check_interval_ms || 10000,
			detached          = new Backbone.Collection(),
			trashed           = new Backbone.Collection();

		/**
		 * init
		 *
		 * @return   void
		 * @since    3.16.7
		 * @version  3.16.7
		 */
		function init() {

			// determine if autosaving is possible
			if ( 'undefined' === typeof wp.heartbeat ) {

				window.llms_builder.debug.log( 'WordPress Heartbeat disabled. Autosaving is disabled!' );
				autosave = false;

			}

			// setup the check interval
			if ( check_interval_ms ) {
				self.set_check_interval( check_interval_ms );
			}

			// warn when users attempt to leave the page
			$( window ).on( 'beforeunload', function() {

				if ( self.has_unsaved_changes() ) {
					check_for_changes();
					return 'Are you sure you want to abandon your changes?';
				}

			} );

		};

		/*
			  /$$             /$$                                             /$$                           /$$
			 |__/            | $$                                            | $$                          |__/
			  /$$ /$$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$  /$$$$$$$   /$$$$$$ | $$        /$$$$$$   /$$$$$$  /$$
			 | $$| $$__  $$|_  $$_/   /$$__  $$ /$$__  $$| $$__  $$ |____  $$| $$       |____  $$ /$$__  $$| $$
			 | $$| $$  \ $$  | $$    | $$$$$$$$| $$  \__/| $$  \ $$  /$$$$$$$| $$        /$$$$$$$| $$  \ $$| $$
			 | $$| $$  | $$  | $$ /$$| $$_____/| $$      | $$  | $$ /$$__  $$| $$       /$$__  $$| $$  | $$| $$
			 | $$| $$  | $$  |  $$$$/|  $$$$$$$| $$      | $$  | $$|  $$$$$$$| $$      |  $$$$$$$| $$$$$$$/| $$
			 |__/|__/  |__/   \___/   \_______/|__/      |__/  |__/ \_______/|__/       \_______/| $$____/ |__/
																								 | $$
																								 | $$
																								 |__/
		 */

		/**
		 * Adds error message(s) to the data object returned by heartbeat-tick
		 *
		 * @param    obj            data  llms_builder data object from heartbeat-tick
		 * @param    string|array   err   error messages array or string
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function add_error_msg( data, err ) {

			if ( 'success' === data.status ) {
				data.message = [];
			}

			data.status = 'error';
			if ( 'string' === typeof err ) {
				err = [ err ];
			}
			data.message = data.message.concat( err );

			return data;

		};

		/**
		 * Publish sync status so other areas of the application can see what's happening here
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function check_for_changes() {

			var data                 = {};
			data.changes             = self.get_unsaved_changes();
			data.has_unsaved_changes = self.has_unsaved_changes( data.changes );
			data.saving              = self.saving;

			window.llms_builder.debug.log( '==== start changes check ====', data, '==== finish changes check ====' );

			Backbone.pubSub.trigger( 'current-save-status', data );

		};

		/**
		 * Manually Save data via Admin AJAX when the heartbeat API has been disabled
		 *
		 * @return   void
		 * @since    3.16.7
		 * @version  3.16.7
		 */
		function do_ajax_save() {

			// prevent simultaneous saves
			if ( self.saving ) {
				return;
			}

			var changes = self.get_unsaved_changes();

			// only send data if we have data to send
			if ( self.has_unsaved_changes( changes ) ) {

				changes.id = Course.get( 'id' );

				LLMS.Ajax.call( {
					data: {
						action: 'llms_builder',
						action_type: 'ajax_save',
						course_id: changes.id,
						llms_builder: JSON.stringify( changes ),
					},
					beforeSend: function() {

						window.llms_builder.debug.log( '==== start do_ajax_save before ====', changes, '==== finish do_ajax_save before ====' );

						self.saving = true;

						Backbone.pubSub.trigger( 'heartbeat-send', self );

					},
					error: function( xhr, status, error ) {

						window.llms_builder.debug.log( '==== start do_ajax_save error ====', data, '==== finish do_ajax_save error ====' );

						self.saving = false;

						Backbone.pubSub.trigger( 'heartbeat-tick', self, {
							status: 'error',
							message: xhr.responseText + ' (' + error + ' ' + status + ')',
						} );

					},
					success: function( res ) {

						if ( ! res.llms_builder ) {
							return;
						}

						window.llms_builder.debug.log( '==== start do_ajax_save success ====', res, '==== finish do_ajax_save success ====' );

						res.llms_builder = process_removals( res.llms_builder );
						res.llms_builder = process_updates( res.llms_builder );

						self.saving = false;

						Backbone.pubSub.trigger( 'heartbeat-tick', self, res.llms_builder );

					}

				} );

			}

		};

		/**
		 * Retrieve all the attributes changed on a model since the last sync
		 *
		 * For a new model (a model with a temp ID) or a model where _forceSync has been defined ALL atts will be returned
		 * For an existing model (without a temp ID) only retrieves changed attributes as tracked by Backbone.TrackIt
		 *
		 * This function excludes any attributes defined as child attributes via the models relationship settings
		 *
		 * @param    obj   model  instance of a Backbone.Model
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.6
		 */
		function get_changed_attributes( model ) {

			var atts = {},
				sync_type;

			// don't save mid editing
			if ( model.get( '_has_focus' ) ) {
				return atts;
			}

			// model hasn't been persisted to the database to get a real ID yet
			// send *all* of it's atts
			if ( has_temp_id( model ) || true === model.get( '_forceSync' ) ) {

				atts      = _.clone( model.attributes );
				sync_type = 'full';

				// only send the changed atts
			} else {

				atts      = model.unsavedAttributes();
				sync_type = 'partial';

			}

			var exclude = ( model.get_relationships ) ? model.get_child_props() : [];
			atts        = _.omit( atts, function( val, key ) {

				// exclude keys that start with an underscore which are used by the
				// application but don't need to be stored in the database
				if ( 0 === key.indexOf( '_' ) ) {
					return true;
				} else if ( -1 !== exclude.indexOf( key ) ) {
					return true;
				}
				return false;

			} );

			if ( model.before_save ) {
				atts = model.before_save( atts, sync_type );
			}

			return atts;

		};

		/**
		 * Get all the changes to an object (either a Model or a Collection of models)
		 * Returns only changes to models and the IDs of that model (should changes exist)
		 * Uses get_changed_attributes() to determine if all atts or only changed atts are needed
		 * Processes children intelligently to only return changed children rather than the entire collection of children
		 *
		 * @param    obj        object  instance of a Backbone.Model or Backbone.Collection
		 * @return   obj|array	  		if object is a model, returns an object
		 *                            	if object is a collection, returns an array of objects
		 * @since    3.16.0
		 * @version  3.16.11
		 */
		function get_changes_to_object( object ) {

			var changed_atts;

			if ( object instanceof Backbone.Model ) {

				changed_atts = get_changed_attributes( object );

				if ( object.get_relationships ) {

					_.each( object.get_child_props(), function( prop ) {

						var children = get_changes_to_object( object.get( prop ) );
						if ( ! _.isEmpty( children ) ) {
							changed_atts[ prop ] = children;
						}

					} );

				}

				// if we have any data, add the id to the model
				if ( ! _.isEmpty( changed_atts ) ) {
					changed_atts.id = object.get( 'id' );
				}

			} else if ( object instanceof Backbone.Collection ) {

				changed_atts = [];
				object.each( function( model ) {
					var model_changes = get_changes_to_object( model );
					if ( ! _.isEmpty( model_changes ) ) {
						changed_atts.push( model_changes );
					}
				} );

			}

			return changed_atts;

		};

		/**
		 * Determines if a model has a temporary ID or a real persisted ID
		 *
		 * @param    obj   model  instance of a model
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function has_temp_id( model ) {

			return ( ! _.isNumber( model.id ) && 0 === model.id.indexOf( 'temp_' ) );

		};

		/**
		 * Compares changes synced to the server against current model and restarts
		 * tracking on elements that haven't changed since the last sync
		 *
		 * @param    obj   model  instance of a Backbone.Model
		 * @param    obj   data   data set that was processed by the server
		 * @return   void
		 * @since    3.16.11
		 * @version  3.19.4
		 */
		function maybe_restart_tracking( model, data ) {

			Backbone.pubSub.trigger( model.get( 'type' ) + '-maybe-restart-tracking', model, data );

			var omit = [ 'id', 'orig_id' ];

			if ( model.get_relationships ) {
				omit.concat( model.get_child_props() );
			}

			_.each( _.omit( data, omit ), function( val, prop ) {

				if ( _.isEqual( model.get( prop ), val ) ) {
					delete model._unsavedChanges[ prop ];
					model._originalAttrs[ prop ] = val;
				}

			} );

			// if syncing was forced, allow tracking to move forward as normal moving forward
			model.unset( '_forceSync' );

		};

		/**
		 * Processes response data from heartbeat-tick related to trashing & detaching models
		 * On success, removes from local removal collection
		 * On error, appends error messages to the data object returned to UI for on-screen feedback
		 *
		 * @param    obj   data  data.llms_builder object from heartbeat-tick response
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		function process_removals( data ) {

			// check removals for errors
			var removals = {
				detach: detached,
				trash: trashed,
			};

			_.each( removals, function( coll, key ) {

				if ( data[ key ] ) {

					var errors = [];

					_.each( data[ key ] , function( info ) {

						// successfully detached, remove it from the detached collection
						if ( ! info.error ) {

							coll.remove( info.id );

						} else {

							errors.push( info.error );

						}

					} );

					if ( errors.length ) {
						_.extend( data, add_error_msg( data, errors ) );
					}

				}

			} );

			return data;
		}

		/**
		 * Processes response data from heartbeat-tick related to creating / updating a single object
		 * Handles both collections and models as a recursive function
		 *
		 * @param    {[type]}   data       [description]
		 * @param    {[type]}   type       [description]
		 * @param    {[type]}   parent     [description]
		 * @param    {[type]}   main_data  [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.11
		 */
		function process_object_updates( data, type, parent, main_data ) {

			if ( ! data[ type ] ) {
				return data;
			}

			if ( parent.get( type ) instanceof Backbone.Model ) {

				var info = data[ type ];

				if ( info.error ) {

					_.extend( main_data, add_error_msg( main_data, info.error ) );

				} else {

					var model = parent.get( type );

					// update temp ids with the real id
					if ( info.id != info.orig_id ) {
						model.set( 'id', info.id );
						delete model._unsavedChanges.id;
					}
					maybe_restart_tracking( model, info );

					// check children
					if ( model.get_relationships ) {

						_.each( model.get_child_props(), function( child_key ) {
							_.extend( data[ type ], process_object_updates( data[ type ], child_key, model, main_data ) );
						} );

					}

				}

			} else if ( parent.get( type ) instanceof Backbone.Collection ) {

				_.each( data[ type ], function( info, index ) {

					if ( info.error ) {

						_.extend( main_data, add_error_msg( main_data, info.error ) );

					} else {

						var model = parent.get( type ).get( info.orig_id );

						// update temp ids with the real id
						if ( info.id != info.orig_id ) {
							model.set( 'id', info.id );
							delete model._unsavedChanges.id;
						}
						maybe_restart_tracking( model, info );

						// check children
						if ( model.get_relationships ) {

							_.each( model.get_child_props(), function( child_key ) {
								_.extend( data[ type ], process_object_updates( data[ type ][ index ], child_key, model, main_data ) );
							} );

						}

					}

				} );

			}

			return main_data;

		};

		/**
		 * Processes response data from heartbeat-tick related to updating & creating new models
		 * On success, removes from local removal collection
		 * On error, appends error messages to the data object returned to UI for on-screen feedback
		 *
		 * @param    obj   data  data.llms_builder object from heartbeat-tick response
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function process_updates( data ) {

			// only mess with updates data
			if ( ! data.updates ) {
				return data;
			}

			if ( data.updates ) {
				data = process_object_updates( data.updates, 'sections', Course, data );
			}

			return data;

		};

		/*
								 /$$       /$$ /$$                                     /$$
								| $$      | $$|__/                                    |__/
			  /$$$$$$  /$$   /$$| $$$$$$$ | $$ /$$  /$$$$$$$        /$$$$$$   /$$$$$$  /$$
			 /$$__  $$| $$  | $$| $$__  $$| $$| $$ /$$_____/       |____  $$ /$$__  $$| $$
			| $$  \ $$| $$  | $$| $$  \ $$| $$| $$| $$              /$$$$$$$| $$  \ $$| $$
			| $$  | $$| $$  | $$| $$  | $$| $$| $$| $$             /$$__  $$| $$  | $$| $$
			| $$$$$$$/|  $$$$$$/| $$$$$$$/| $$| $$|  $$$$$$$      |  $$$$$$$| $$$$$$$/| $$
			| $$____/  \______/ |_______/ |__/|__/ \_______/       \_______/| $$____/ |__/
			| $$                                                            | $$
			| $$                                                            | $$
			|__/                                                            |__/
		*/

		/**
		 * Retrieve all unsaved changes for the builder instance
		 *
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		this.get_unsaved_changes = function() {

			return {
				detach: detached.pluck( 'id' ),
				trash: trashed.pluck( 'id' ),
				updates: get_changes_to_object( Course ),

			}
		};

		/**
		 * Check if the builder instance has unsaved changes
		 *
		 * @param    obj      changes    optionally pass in an object from the return of this.get_unsaved_changes()
		 *                               save some resources by not running the check twice during heartbeats
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.has_unsaved_changes = function( changes ) {

			if ( 'undefined' === typeof changes ) {
				changes = self.get_unsaved_changes();
			}

			// check all possible keys, once we find one with content we have some changes to persist
			var found = _.find( changes, function( data ) {

				return ( false === _.isEmpty( data ) );

			} );

			return found ? true : false;

		};

		/**
		 * Save changes right now.
		 *
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.7
		 */
		this.save_now = function() {
			if ( autosave ) {
				wp.heartbeat.connectNow();
			} else {
				do_ajax_save();
			}
		};

		/**
		 * Update the interval that checks for changes to the builder instance
		 *
		 * @param    int        ms   time (in milliseconds) to run the check on
		 *                           pass 0 to disable the check
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.set_check_interval = function( ms ) {
			check_interval_ms = ms;
			if ( check_interval ) {
				clearInterval( check_interval );
			}
			if ( check_interval_ms ) {
				check_interval = setInterval( check_for_changes, check_interval_ms );
			}
		};

		/*
			 /$$ /$$             /$$
			| $$|__/            | $$
			| $$ /$$  /$$$$$$$ /$$$$$$    /$$$$$$  /$$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$
			| $$| $$ /$$_____/|_  $$_/   /$$__  $$| $$__  $$ /$$__  $$ /$$__  $$ /$$_____/
			| $$| $$|  $$$$$$   | $$    | $$$$$$$$| $$  \ $$| $$$$$$$$| $$  \__/|  $$$$$$
			| $$| $$ \____  $$  | $$ /$$| $$_____/| $$  | $$| $$_____/| $$       \____  $$
			| $$| $$ /$$$$$$$/  |  $$$$/|  $$$$$$$| $$  | $$|  $$$$$$$| $$       /$$$$$$$/
			|__/|__/|_______/    \___/   \_______/|__/  |__/ \_______/|__/      |_______/
		*/

		/**
		 * Listen for detached models and send them to the server for persistence
		 *
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		Backbone.pubSub.on( 'model-detached', function( model ) {

			// detached models with temp ids haven't been persisted so we don't care
			if ( has_temp_id( model ) ) {
				return;
			}

			detached.add( _.clone( model.attributes ) );

		} );

		/**
		 * Listen for trashed models and send them to the server for deletion
		 *
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		Backbone.pubSub.on( 'model-trashed', function( model ) {

			// if the model has a temp ID we don't have to persist the deletion
			if ( has_temp_id( model ) ) {
				return;
			}

			var data = _.clone( model.attributes );

			if ( model.get_trash_id ) {
				data.id = model.get_trash_id();
			}

			trashed.add( data );

		} );

		/*
			 /$$                                       /$$     /$$                             /$$
			| $$                                      | $$    | $$                            | $$
			| $$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$  /$$$$$$  | $$$$$$$   /$$$$$$   /$$$$$$  /$$$$$$
			| $$__  $$ /$$__  $$ |____  $$ /$$__  $$|_  $$_/  | $$__  $$ /$$__  $$ |____  $$|_  $$_/
			| $$  \ $$| $$$$$$$$  /$$$$$$$| $$  \__/  | $$    | $$  \ $$| $$$$$$$$  /$$$$$$$  | $$
			| $$  | $$| $$_____/ /$$__  $$| $$        | $$ /$$| $$  | $$| $$_____/ /$$__  $$  | $$ /$$
			| $$  | $$|  $$$$$$$|  $$$$$$$| $$        |  $$$$/| $$$$$$$/|  $$$$$$$|  $$$$$$$  |  $$$$/
			|__/  |__/ \_______/ \_______/|__/         \___/  |_______/  \_______/ \_______/   \___/
		*/

		/**
		 * Add data to the WP heartbeat to persist new models, changes, and deletions to the DB
		 *
		 * @since    3.16.0
		 * @version  3.16.7
		 */
		$( document ).on( 'heartbeat-send', function( event, data ) {

			// prevent simultaneous saves
			if ( self.saving ) {
				return;
			}

			var changes = self.get_unsaved_changes();

			// only send data if we have data to send
			if ( self.has_unsaved_changes( changes ) ) {

				changes.id        = Course.get( 'id' );
				self.saving       = true;
				data.llms_builder = JSON.stringify( changes );

			}

			window.llms_builder.debug.log( '==== start heartbeat-send ====', data, '==== finish heartbeat-send ====' );

			Backbone.pubSub.trigger( 'heartbeat-send', self );

		} );

		/**
		 * Confirm detachments & deletions and replace temp IDs with new persisted IDs
		 *
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		$( document ).on( 'heartbeat-tick', function( event, data ) {

			if ( ! data.llms_builder ) {
				return;
			}

			window.llms_builder.debug.log( '==== start heartbeat-tick ====', data, '==== finish heartbeat-tick ====' );

			data.llms_builder = process_removals( data.llms_builder );
			data.llms_builder = process_updates( data.llms_builder );

			self.saving = false;

			Backbone.pubSub.trigger( 'heartbeat-tick', self, data.llms_builder );

		} );

		/**
		 * On heartbeat errors publish an error to the main builder application
		 *
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		$( document ).on( 'heartbeat-error', function( event, data ) {

			window.llms_builder.debug.log( '==== start heartbeat-error ====', data, '==== finish heartbeat-error ====' );

			self.saving = false;

			Backbone.pubSub.trigger( 'heartbeat-tick', self, {
				status: 'error',
				message: data.responseText + ' (' + data.status + ' ' + data.statusText + ')',
			} );

		} );

		/*
			 /$$           /$$   /$$
			|__/          |__/  | $$
			 /$$ /$$$$$$$  /$$ /$$$$$$
			| $$| $$__  $$| $$|_  $$_/
			| $$| $$  \ $$| $$  | $$
			| $$| $$  | $$| $$  | $$ /$$
			| $$| $$  | $$| $$  |  $$$$/
			|__/|__/  |__/|__/   \___/
		*/
		init();

		return this;

	};

} );
