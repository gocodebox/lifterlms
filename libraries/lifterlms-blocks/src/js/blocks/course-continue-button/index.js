/**
 * BLOCK: llms/course-continue-button
 *
 * @since 1.0.0
 * @since 1.5.0 Add supported post type settings.
 * @since 1.8.0 Use imports in favor of "wp." variables.
 * @version 2.5.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

// Internal dependencies.
import icon from '../../icons/forward';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/course-continue-button';

/**
 * Array of supported post types.
 *
 * @type {Array}
 */
export const postTypes = [ 'course' ];

/**
 * Register: Course Continue Button Block
 *
 * @since 1.0.0
 * @since 2.5.0 Update icon color to `currentColor`.
 *
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully, registered; otherwise `undefined`.
 */
export const settings = {
	title: __( 'Course Continue Button', 'lifterlms' ),
	icon: icon,
	category: 'llms-blocks', // common, formatting, layout widgets, embed. see https://wordpress.org/gutenberg/handbook/block-api/#category.
	keywords: [ __( 'LifterLMS', 'lifterlms' ) ],

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} props Block properties.
	 * @return {Function} Component HTML fragment.
	 */
	edit( props ) {
		return (
			<div className={ props.className }>
				<p style={ { textAlign: 'center' } }>
					<Button isPrimary isLarge>
						{ __( 'Continue', 'lifterlms' ) }
					</Button>
				</p>
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} props Block properties.
	 * @return {Function} Component HTML fragment.
	 */
	save( props ) {
		return (
			<div
				className={ props.className }
				style={ { textAlign: 'center' } }
			>
				[lifterlms_course_continue_button]
			</div>
		);
	},
};
