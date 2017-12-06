( function( $ ) {

	Backbone.pubSub = _.extend( {}, Backbone.Events );

	require( [ 'Collections/loader', 'Models/loader', 'Views/loader' ], function( Collections, Models, Views ) {

		/**
		 * Main Application Object
		 * @type     {Object}
		 * @since    3.13.0
		 * @version  [version]
		 */
		var App = {

			$elements: {
				$main: $( '.llms-builder-main' ),
			},

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

			},

		};

		/**
		 * Main Instance
		 * @type     {Object}
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		var Instance = {
			Course: new Views.Course( {
				model: new Models.Course( window.llms_builder.course ),
			} ),
			Main: new Views.Main,
			Syllabus: new Views.SectionList,
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

		Instance.Tools = new Views.Tools;
		Instance.Tutorial = new Views.Tutorial;

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

	} );

} )( jQuery );
