/**
 * BLOCK: llms/lesson-progression
 *
 * @since 1.0.0
 * @since 1.5.0 Add supported post type settings.
 * @since 1.8.0 Use imports in favor of "wp." variables.
 *              Convert "edit" function from using ServerSideRender.
 * @version 2.5.0
 */

// WP Deps.
import { Button } from '@wordpress/components';
import { select } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { Fragment } from '@wordpress/element';
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
	 * @return {Fragment} Edit component fragment.
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

		return (
			<>
			<div className="llms-lesson-button-wrapper">
			<Fragment>
				{ !! quiz && (
					<Button className="llms-prog-btn--quiz llms-button-action auto button">
						{__('Take Quiz', 'lifterlms')}
					</Button>
				) }
				{ showMainBtn && (
					<Button className="llms-prog-btn--complete llms-field-button llms-button-primary auto button">
						{__('Mark Complete', 'lifterlms')}
					</Button>
				) }
			</Fragment>
			</div>
			</>
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
