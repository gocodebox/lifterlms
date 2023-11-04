// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	Disabled,
	PanelBody,
	Spinner,
	ToggleControl
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';
import {
	PostSelect,
	usePostOptions,
} from '../../../packages/components/src/post-select';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const courseOptions = usePostOptions();
	const { postId, postType } = useSelect( ( select ) => {
		return {
			postId: select( 'core/editor' ).getCurrentPostId(),
			postType: select( 'core/editor' ).getCurrentPostType(),
		};
	} );

	const memoizedServerSideRender = useMemo( () => {
		let emptyPlaceholder = __( 'No progress data found for this course. This block will not be displayed.', 'lifterlms' );

		if ( postType !== 'course' && ! attributes.course_id && courseOptions.length > 0 ) {
			emptyPlaceholder = __( 'No course selected. Please choose a Course from the block sidebar panel.', 'lifterlms' );
		}

		return <ServerSideRender
			block={ blockJson.name }
			attributes={ {
				...attributes,
				course_id: attributes.course_id || postId,
			} }
			LoadingResponsePlaceholder={ () =>
				<Spinner />
			}
			ErrorResponsePlaceholder={ () =>
				<p className={ 'llms-block-error' }>{ __( 'Error loading content. Please check block settings are valid. This block will not be displayed.', 'lifterlms' ) }</p>
			}
			EmptyResponsePlaceholder={ () =>
				<p className={ 'llms-block-empty' }>{ emptyPlaceholder }</p>
			}
		/>;
	}, [ attributes ] );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Course Progress Settings', 'lifterlms' ) }
				>
					<PostSelect { ...props } />
				</PanelBody>
				<PanelBody
					title={ __( 'Check Enrollment', 'lifterlms' ) }
				>
					<ToggleControl
						label={ __( 'Show progress bar to non-enrolled students', 'lifterlms' ) }
						checked={ attributes?.check_enrollment }
						onChange={ ( value ) => {
							setAttributes( { value } );
						} }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					{ memoizedServerSideRender }
				</Disabled>
			</div>
		</>
	);
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
	save: () => null, // <!-- wp:llms/course-progress /-->.
	deprecated: [
		{
			/**
			 * Block Editor Save.
			 *
			 * @since 1.0.0
			 * @deprecated 1.8.0
			 *
			 * @param {Object} props Component properties object.
			 * @return {Object} Component HTML Fragment.
			 */
			save( props ) {
				return (
					<div className={ props.className }>
						[lifterlms_course_progress]
					</div>
				);
			},
		},
	],
} );
