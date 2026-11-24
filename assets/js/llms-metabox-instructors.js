/**
 * Instructors Metabox
 *
 * @since    3.13.0
 * @version  3.13.0
 */
( function( $ ) {

	window.llms = window.llms || {};

	window.llms.metabox_instructors = function() {

		/**
		 * Initialize
		 *
		 * @return  void
		 * @since   3.13.0
		 * @version 3.13.0
		 */
		this.init = function() {

			var self = this;

			// The primary instructor the Author control shows, set once the rows load.
			this.primary_id = null;

			// before saving, update the wp core hidden field for post_author
			// so that the first instructor is always set as the post author
			$( '._llms_instructors_data.repeater' ).on( 'llms-repeater-before-save', function( e, params ) {
				var author_id = self.get_primary_id( params.$el );
				$( '#post_author' ).val( author_id );

				// Only when the primary instructor changed, so a change made in the Author control is kept on submit.
				if ( author_id && author_id !== self.primary_id ) {
					self.primary_id = author_id;
					self.sync_author( author_id );
				}
			} );

			$( '._llms_instructors_data.repeater' ).on( 'llms-new-repeater-row', function( e, params ) {

				var $instructor = params.$row.find( 'select[name^="_llms_id"]' ),
					$target     = params.$row.find( '.llms-repeater-title' );

				if ( null === self.primary_id ) {
					self.primary_id = self.get_primary_id( $( this ) ) || null;
				}

				$instructor.on( 'select2:select', function( e ) {
					if ( ! e.params ) {
						$target.html( $instructor.find( 'option[selected="selected"]' ).html() );
					} else {
						$target.text( e.params.data.text );
					}
				} ).trigger( 'select2:select' );

			} );

			// Only courses have an Author control: memberships don't load the author with their instructors.
			if ( $( 'input[name="_llms_instructors_author"]' ).length ) {
				this.bind_author_control();
			}

		};

		/**
		 * Show changes made in the editor's Author control in the Instructors tab
		 *
		 * @since [version]
		 *
		 * @return {void}
		 */
		this.bind_author_control = function() {

			var self = this;

			// Block editor Author panel.
			if ( $( 'body' ).hasClass( 'block-editor-page' ) && window.wp && wp.data ) {
				var last_author;
				wp.data.subscribe( function() {
					var author = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'author' );

					if ( undefined === author || author === last_author ) {
						return;
					}

					// The first value is the author the post loaded with.
					if ( undefined !== last_author ) {
						self.follow_author( author );

						// The Author panel only gives the ID: show the name once the user is loaded.
						wp.data.resolveSelect( 'core' ).getEntityRecord( 'root', 'user', author ).then( function( user ) {
							if ( user ) {
								self.set_instructor_name( author, user.name );
							}
						} );
					}
					last_author = author;
				} );
			}

			// Classic editor Author meta box.
			$( '#post_author_override' ).on( 'change', function() {
				self.follow_author( $( this ).val(), $( this ).find( 'option:selected' ).text() );
			} );

		};

		/**
		 * Make the user picked in the editor's Author control the primary instructor in the Instructors tab
		 *
		 * Rows are only changed on screen: like the Author control, they are saved with the post.
		 *
		 * @since [version]
		 *
		 * @param {number|string} author_id WP_User ID of the new author.
		 * @param {string}        [name]    Display name of the new author, if known.
		 * @return {void}
		 */
		this.follow_author = function( author_id, name ) {

			var $repeater = $( '._llms_instructors_data.repeater' ),
				$rows     = $repeater.find( '.llms-repeater-rows' ),
				$row;

			author_id = String( author_id );

			// Also when the Instructors tab set the author itself.
			if ( ! $repeater.hasClass( 'is-loaded' ) || author_id === this.get_primary_id( $repeater ) ) {
				return;
			}

			// An instructor moves to the top and every instructor is kept, as when dragging their row.
			$row = $rows.find( '.llms-repeater-row' ).filter( function() {
				return author_id === $( this ).find( 'select[name^="_llms_id"]' ).val();
			} ).first();

			// Another user replaces the primary instructor, as saving a changed author does on the server.
			if ( ! $row.length ) {
				$rows.find( '.llms-repeater-row' ).first().remove();
				window.llms.metaboxes.repeaters.add_row( $repeater, {
					_llms_id: [ {
						key: author_id,
						title: $( '<div>' ).text( name || '#' + author_id ).html(),
					} ],
				}, false );
				$row = $rows.find( '.llms-repeater-row' ).last();
			}

			$rows.prepend( $row );

			// So the Author control isn't synced back before the post is saved.
			this.primary_id = author_id;

		};

		/**
		 * Show a name for an instructor added from the Author control
		 *
		 * @since [version]
		 *
		 * @param {number|string} author_id WP_User ID of the instructor.
		 * @param {string}        name      Display name of the instructor.
		 * @return {void}
		 */
		this.set_instructor_name = function( author_id, name ) {

			var $option = $( '._llms_instructors_data.repeater .llms-repeater-rows select[name^="_llms_id"] option[value="' + author_id + '"]' );

			$option.filter( function() {
				return '#' + author_id === $( this ).text();
			} ).each( function() {
				var $row = $( this ).closest( '.llms-repeater-row' );
				$( this ).text( name ).closest( 'select' ).trigger( 'change' );
				$row.find( '.llms-repeater-title' ).text( name );
			} );

		};

		/**
		 * Get the user ID of the first (primary) instructor in the repeater
		 *
		 * @since [version]
		 *
		 * @param {Object} $repeater jQuery selector for the instructors repeater.
		 * @return {string|undefined}
		 */
		this.get_primary_id = function( $repeater ) {
			return $repeater.find( '.llms-repeater-rows .llms-repeater-row' ).first().find( 'select[name^="_llms_id"]' ).val();
		};

		/**
		 * Show the new primary instructor in the editor's Author control
		 *
		 * @since [version]
		 *
		 * @param {string} author_id WP_User ID of the new primary instructor.
		 * @return {void}
		 */
		this.sync_author = function( author_id ) {

			// The loaded author the server compares against when the post is saved.
			$( 'input[name="_llms_instructors_author"]' ).val( author_id );

			// Block editor Author panel, for post types that support an author.
			if ( $( 'body' ).hasClass( 'block-editor-page' ) && window.wp && wp.data && undefined !== wp.data.select( 'core/editor' ).getEditedPostAttribute( 'author' ) ) {
				var editor = wp.data.select( 'core/editor' ),
					type   = editor.getCurrentPostType(),
					record = wp.data.select( 'core' ).getEntityRecord( 'postType', type, editor.getCurrentPostId() ),
					id     = parseInt( author_id, 10 );

				// The instructors already saved the new author, so update the loaded post instead of adding an unsaved
				// change. Otherwise picking the previous author again would look unchanged and not be saved.
				if ( record ) {
					wp.data.dispatch( 'core' ).receiveEntityRecords( 'postType', type, [ $.extend( {}, record, { author: id } ) ] );
				}

				// Replaces an unsaved Author panel change, if any (an edit equal to the loaded post is dropped).
				wp.data.dispatch( 'core/editor' ).editPost( { author: id } );
			}

			// Classic editor Author meta box, when the user is one of its options.
			var $override = $( '#post_author_override' );
			if ( $override.find( 'option[value="' + author_id + '"]' ).length ) {
				$override.val( author_id ).trigger( 'change' );
			}

		};

		// go
		this.init();

	};

	var a = new window.llms.metabox_instructors();

} )( jQuery );
