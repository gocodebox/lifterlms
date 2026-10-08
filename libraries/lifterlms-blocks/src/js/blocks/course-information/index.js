/**
 * Course Information Block.
 *
 * @since 1.0.0
 * @version 2.5.0
 */

// Import CSS.
import './editor.scss';

// Import Inspector.
import Inspector from './inspect';

// Import Previews.
import PreviewTerms from './preview-terms';

// External Deps.
import { RichText } from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

// Internal dependencies.
import icon from '../../icons/table-list';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/course-information';

/**
 * Array of supported post types.
 *
 * @type {Array}
 */
export const postTypes = [ 'course' ];

/**
 * Register: Course Information Block
 *
 * @since 2.5.0 Update icon color to `currentColor`.
 *
 * @type {Object}
 */
export const settings = {
	title: __( 'Course Information', 'lifterlms' ),
	icon: icon,
	category: 'llms-blocks',
	keywords: [ __( 'LifterLMS', 'lifterlms' ) ],

	attributes: {
		title: {
			type: 'string',
			default: __( 'Course Information', 'lifterlms' ),
		},
		title_size: {
			type: 'string',
			default: 'h2',
		},
		length: {
			type: 'string',
			default: '',
			source: 'meta',
			meta: '_llms_length',
		},
		show_cats: {
			type: 'boolean',
			default: true,
		},
		show_difficulty: {
			type: 'boolean',
			default: true,
		},
		show_length: {
			type: 'boolean',
			default: true,
		},
		show_tags: {
			type: 'boolean',
			default: true,
		},
		show_tracks: {
			type: 'boolean',
			default: true,
		},
	},

	supports: {
		multiple: false,
	},

	/**
	 * Block Editor
	 *
	 * @since 1.0.0
	 * @since 1.8.0 Always show the block "title" in the editor.
	 *
	 * @param {Object} props Block properties.
	 * @return {Fragment} Component HTML Fragment.
	 */
	edit: ( props ) => {
		const [ meta ] = useEntityProp( 'postType', 'course', 'meta' );
		const length = meta?._llms_length || '';

		const { attributes, setAttributes } = props;
		const {
			show_cats,
			show_difficulty,
			show_length,
			show_tags,
			show_tracks,
			title,
			title_size,
		} = attributes;

		const currentPost = wp.data.select( 'core/editor' ).getCurrentPost();

		const hasContent =
			show_length ||
			show_difficulty ||
			show_tracks ||
			show_cats ||
			show_tags;

		return (
			<Fragment>
				<Inspector { ...{ attributes, setAttributes } } />
				<div className={ props.className }>
					<RichText
						tagName={ title_size }
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
					{ hasContent && (
						<Fragment>
							<ul>
								{ show_length && length && (
									<li>
										<strong>
											{ __(
												'Estimated Time',
												'lifterlms'
											) }
										</strong>
										: { length }
									</li>
								) }
								{ show_difficulty && (
									<PreviewTerms
										{ ...{
											currentPost,
											taxonomy: 'course_difficulty',
											taxonomyName: __(
												'Difficulty',
												'lifterlms'
											),
										} }
									/>
								) }
								{ show_tracks && (
									<PreviewTerms
										{ ...{
											currentPost,
											taxonomy: 'course_track',
											taxonomyName: __(
												'Tracks',
												'lifterlms'
											),
										} }
									/>
								) }
								{ show_cats && (
									<PreviewTerms
										{ ...{
											currentPost,
											taxonomy: 'course_cat',
											taxonomyName: __(
												'Categories',
												'lifterlms'
											),
										} }
									/>
								) }
								{ show_tags && (
									<PreviewTerms
										{ ...{
											currentPost,
											taxonomy: 'course_tag',
											taxonomyName: __(
												'Tags',
												'lifterlms'
											),
										} }
									/>
								) }
							</ul>
						</Fragment>
					) }
				</div>
			</Fragment>
		);
	},

	/**
	 * Block Editor Save
	 *
	 * Does nothing since it's a dynamic block.
	 *
	 * @since 1.0.0
	 *
	 * @return {null} Saving disabled for "dynamic" block.
	 */
	save: () => {
		return null;
	},
};
