/**
 * BLOCK: llms/lesson-progression
 *
 * @since 1.0.0
 * @since 1.5.0 Add supported post type settings.
 * @since 1.8.0 Use imports in favor of "wp." variables.
 *              Convert "edit" function from using ServerSideRender.
 * @since [version] Use native buttons with `wp-element-button` and an `extraButtons` filter so add-ons can render inside the wrapper.
 * @version [version]
 */

// WP Deps.
import { select } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

// Internal dependencies.
import icon from '../../icons/circle-check';

// CSS.
import './editor.scss';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/lesson-progression';

/**
 * Array of supported post types.
 *
 * @type {Array}
 */
export const postTypes = [ 'lesson' ];

/**
 * Register Block
 *
 * @since 2.5.0 Update icon color to `currentColor`.
 *
 * @type {Object}
 */
export const settings = {
	title: __( 'Lesson Progression (Mark Complete)', 'lifterlms' ),
	icon: icon,
	category: 'llms-blocks',
	keywords: [ __( 'LifterLMS', 'lifterlms' ) ],
	supports: {
		llms_visibility: false,
	},

	/**
	 * Edit block
	 *
	 * @since 1.0.0
	 *
	 * @return {JSX.Element} Edit component.
	 */
	edit() {
		const currentPost = select( 'core/editor' ).getCurrentPost(),
			quiz = currentPost.meta._llms_quiz * 1;

		let showMainBtn = quiz ? false : true;

		/**
		 * Determine whether or not to show the "Mark Complete" button in the lesson progression block editor "edit" view.
		 *
		 * @since 1.8.0
		 *
		 * @param {boolean} showMainBtn Determines whether or not to display the main button.
		 */
		showMainBtn = applyFilters(
			'llms.lessonProgressBlock.showMainBtn',
			showMainBtn
		);

		/**
		 * Additional buttons rendered inside the lesson progression wrapper in the editor.
		 *
		 * Return an array of React elements. Used by add-ons (e.g. Assignments) so their
		 * buttons sit in the same row as Take Quiz / Mark Complete.
		 *
		 * @since [version]
		 *
		 * @param {Array} buttons Extra button elements.
		 */
		const extraButtons = applyFilters(
			'llms.lessonProgressBlock.extraButtons',
			[]
		);

		return (
			<div className="llms-lesson-button-wrapper">
				{ extraButtons }
				{ !! quiz && (
					<button type="button" className="llms-prog-btn--quiz llms-button-action auto button wp-element-button">
						{ __( 'Take Quiz', 'lifterlms' ) }
					</button>
				) }
				{ showMainBtn && (
					<button type="button" className="llms-prog-btn--complete llms-field-button llms-button-primary auto button wp-element-button">
						{ __( 'Mark Complete', 'lifterlms' ) }
					</button>
				) }
			</div>
		);
	},

	/**
	 * Save Block
	 *
	 * @since 1.0.0
	 *
	 * @return {null} Save disabled for "dynamic" block.
	 */
	save() {
		return null;
	},
};
