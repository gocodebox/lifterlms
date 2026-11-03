/**
 * BLOCKS: llms/lesson-video and llms/lesson-audio
 *
 * Place the lesson's featured video or audio anywhere in the lesson content.
 *
 * @since [version]
 * @version [version]
 */

// WP Deps.
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Build the block definition for a featured media type.
 *
 * @since [version]
 *
 * @param {string} type        Media type, "video" or "audio".
 * @param {string} title       Block title.
 * @param {string} description Block description.
 * @param {string} icon        Dashicon slug.
 * @return {Object} Block definition with `name`, `postTypes`, and `settings`.
 */
const createLessonMediaBlock = ( type, title, description, icon ) => {
	const name = `llms/lesson-${ type }`;

	/**
	 * Render the media in the editor through the block's PHP render callback.
	 *
	 * @since [version]
	 *
	 * @param {Object} props            Block properties.
	 * @param {Object} props.attributes Block attributes.
	 * @return {Object} Block edit element.
	 */
	const Edit = ( { attributes } ) => {
		const postId = useSelect(
			( select ) => select( 'core/editor' ).getCurrentPostId(),
			[]
		);

		return (
			<div { ...useBlockProps() }>
				<ServerSideRender
					block={ name }
					attributes={ attributes }
					urlQueryArgs={ { post_id: postId } }
				/>
			</div>
		);
	};

	return {
		name,
		postTypes: [ 'lesson' ],
		settings: {
			apiVersion: 3,
			title,
			description,
			icon,
			category: 'llms-blocks',
			keywords: [ __( 'LifterLMS', 'lifterlms' ), type ],
			supports: {
				// Only one featured video/audio placement per lesson.
				multiple: false,
				html: false,
			},
			edit: Edit,
			save() {
				return null;
			},
		},
	};
};

export const video = createLessonMediaBlock(
	'video',
	__( 'Lesson Video', 'lifterlms' ),
	__(
		"Displays the lesson's featured video here instead of at the top of the lesson.",
		'lifterlms'
	),
	'video-alt3'
);

export const audio = createLessonMediaBlock(
	'audio',
	__( 'Lesson Audio', 'lifterlms' ),
	__(
		"Displays the lesson's featured audio here instead of at the top of the lesson.",
		'lifterlms'
	),
	'format-audio'
);
