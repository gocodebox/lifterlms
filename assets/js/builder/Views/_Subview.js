/**
 * Subview utility mixin
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return {

		subscriptions: {},

		/**
		 * Name of the current subview
		 * @type  {String}
		 */
		state: '',

		/**
		 * Object of subview data
		 * @type  {Object}
		 */
		views: {},

		/**
		 * Retrieve a subview by name from this.views
		 * @param    string   name   name of the subview
		 * @return   obl|false
		 * @since    [version]
		 * @version  [version]
		 */
		get_subview: function( name ) {

			if ( this.views[ name ] ) {
				return this.views[ name ];
			}

			return false;

		},

		events_subscribe: function( events ) {

			_.each( events, function( func, event ) {

				this.subscriptions[ event ] = func;
				Backbone.pubSub.on( event, func, this );

			}, this );

		},

		events_unsubscribe: function() {

			_.each( this.subscriptions, function( func, event ) {

				Backbone.pubSub.off( event, func, this );
				delete this.subscriptions[ event ];

			}, this );

		},

		/**
		 * Remove a single subview (and all it's subviews) by name
		 * @param    string   name   name of the subview
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		remove_subview: function( name ) {

			var view = this.get_subview( name );

			if ( ! view ) {
				return;
			}

			if ( view.instance ) {

				// remove the subviews if the view has subviews
				if ( ! _.isEmpty( view.instance.views ) ) {
					view.instance.events_unsubscribe();
					view.instance.remove_subviews();
				}

				view.instance.off();
				view.instance.off( null, null, null );
				view.instance.remove();
				view.instance.undelegateEvents();

				// _.each( view.instance, function( val, key ) {
				// 	delete view.instance[ key ];
				// } );

				view.instance = null;

			}

		},

		/**
		 * Remove all subviews (and all the subviews of those subviews)
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		remove_subviews: function() {

			_.each( this.views, function( data, name ) {

				this.remove_subview( name );

			}, this );

		},

		/**
		 * Render subviews based on current state
		 * @param    obj   view_data  additional data to pass to the subviews
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		render_subviews: function( view_data ) {

			view_data = view_data || {};

			_.each( this.views, function( data, name ) {

				if ( this.state === data.state ) {

					this.render_subview( name, view_data );

				} else {

					this.remove_subview( name );

				}

			}, this );

		},

		/**
		 * Render a single subview by name
		 * @param    string   name       name of the subview
		 * @param    obj      view_data  additional data to pass to the subview initializer
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		render_subview: function( name, view_data ) {

			var view = this.get_subview( name );

			if ( ! view ) {
				return;
			}

			this.remove_subview( name );

			if ( ! view.instance ) {
				view.instance = new view.class( view_data );
			}

			view.instance.render();

		},

		/**
		 * Set the current subview
		 * Must call render after!
		 * @param    string   state  name of the state [builder|editor]
		 * @return   obj             this for chaining
		 * @since    [version]
		 * @version  [version]
		 */
		set_state: function ( state ) {

			this.state = state;
			return this;

		},

	}

} );
