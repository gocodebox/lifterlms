/**
 * Model relationships mixin
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return {

		/**
		 * Default relationship settings object
		 * @type  {Object}
		 */
		relationship_defaults: {
			parent: {},
			children: {},
		},

		/**
		 * Relationship settings object
		 * Should be overriden in the model
		 * @type  {Object}
		 */
		relationships: {},

		/**
		 * Initialize all parent and child relationships
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		init_relationships: function( options ) {

			var rels = this.get_relationships();

			// initialize parent relaxtionships
			// useful when adding a model to ensure parent is initialized
			if ( rels.parent && options && options.parent ) {
				this.set_parent( options.parent );
			}

			// initialize all children relationships
			_.each( rels.children, function( child_data, child_key ) {

				if ( ! child_data.conditional || true === child_data.conditional( this ) ) {

					var child_val = this.get( child_key ),
						child;

					if ( child_data.lookup ) {
						child = child_data.lookup( child_val );
					} else if ( 'model' === child_data.type ) {
						child = window.llms_builder.construct.get_model( child_data.class, child_val );
					} else if ( 'collection' === child_data.type ) {
						child = window.llms_builder.construct.get_collection( child_data.class, child_val );
					}

					this.set( child_key, child );

					// if the child defines a parent, save a reference to the parent on the child
					if ( 'model' === child_data.type ) {

						this._maybe_set_parent_reference( child );

					// save directly to each model in the collection
					} else if ( 'collection' === child_data.type ) {

						child.parent = this;
						child.each( function( child_model ) {

							this._maybe_set_parent_reference( child_model );

						}, this );

					}

				}

			}, this );

		},

		/**
		 * Retrieve the model's parent (if set)
		 * @return   obj|false
		 * @since    [version]
		 * @version  [version]
		 */
		get_parent: function() {

			var rels = this.get_relationships();

			if ( rels.parent ) {
				return rels.parent.reference;
			}

			return false;

		},

		/**
		 * Retrieve relationships for the model
		 * Extends with defaults
		 * @return   obj
		 * @since    [version]
		 * @version  [version]
		 */
		get_relationships: function() {

			return $.extend( true, this.relationships, this.relationship_defaults );

		},

		/**
		 * Set the parent reference for the given model
		 * @param    obj   obj   parent model obj
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		set_parent: function( obj ) {
			this.relationships.parent.reference = obj;
		},

		/**
		 * Set up the parent relationships for qualifying children during relationship initialization
		 * @param    obj   model  child model
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		_maybe_set_parent_reference: function( model ) {

			if ( ! model.get_relationships ) {
				return;
			}

			var rels = model.get_relationships();
			if ( rels.parent && rels.parent.model === this.get( 'type' ) ) {
				model.set_parent( this );
			}

		},

	};

} );
