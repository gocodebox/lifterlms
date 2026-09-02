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
	 * @since [version]
	 *
	 * @param {String|Element} el        Popover trigger selector or element.
	 * @param {String}         placement webuiPopover placement.
	 * @return {Void}
	 */
	return function( el, placement ) {

		var pop, onLessonSelect;

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
			pop.hide();

			// Ref #3097 — pop.hide() doesn't always remove the DOM elements.
			$( '.webui-popover' ).remove();
			$( '.webui-popover-backdrop' ).remove();
		};

		pop.show();
		Backbone.pubSub.once( 'lesson-search-select', onLessonSelect );

	};

} );
