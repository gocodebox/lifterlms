/**
 * Existing Lesson search popover.
 *
 * @since [version]
 * @version [version]
 */
define( [ 'Views/Popover', 'Views/PostSearch' ], function( Popover, LessonSearch ) {

	/**
	 * Show the popover used to attach or clone an existing lesson.
	 *
	 * webuiPopover keeps its instance on the trigger and ignores later inits, so
	 * destroy any previous instance first. Leave cache on so the select is moved
	 * (not cloned) into the popover — Select2 is initialized on that node via
	 * setTimeout after it is in the DOM.
	 *
	 * @since [version]
	 *
	 * @param {String|Element} el        Popover trigger selector or element.
	 * @param {String}         placement webuiPopover placement.
	 * @return {Void}
	 */
	return function( el, placement ) {

		var $el = $( el ), pop, onLessonSelect;

		if ( $el.data( 'plugin_webuiPopover' ) ) {
			$el.webuiPopover( 'destroy' );
		}

		pop = new Popover( {
			el: el,
			args: {
				backdrop: true,
				closeable: true,
				container: '.wrap.lifterlms.llms-builder',
				dismissible: true,
				placement: placement || 'left',
				width: 480,
				title: LLMS.l10n.translate( 'Add Existing Lesson' ),
				content: new LessonSearch( {
					post_type: 'lesson',
					searching_message: LLMS.l10n.translate( 'Search for existing lessons...' ),
				} ).render().$el,
				onHide: function() {
					Backbone.pubSub.off( 'lesson-search-select', onLessonSelect );
				},
			}
		} );

		onLessonSelect = function() {
			if ( $el.data( 'plugin_webuiPopover' ) ) {
				$el.webuiPopover( 'destroy' );
			}

			// Ref #3097 — hide/destroy leaves the popover and backdrop in the DOM.
			$( '.webui-popover' ).remove();
			$( '.webui-popover-backdrop' ).remove();
		};

		pop.show();
		Backbone.pubSub.once( 'lesson-search-select', onLessonSelect );

	};

} );
