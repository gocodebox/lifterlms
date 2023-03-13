// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	Disabled,
	Spinner,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';
import { useCourseOptions, useLlmsPostType, CourseSelect } from '../../../packages/components/src/course-select';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const isLlmsPostType = useLlmsPostType();
	const courseOptions = useCourseOptions();

	const defaultCourseId = useMemo(
		() => courseOptions?.[ 0 ]?.value,
		[ courseOptions ]
	);

	const memoizedServerSideRender = useMemo( () => {
		return <ServerSideRender
			block={ blockJson.name }
			attributes={ attributes }
			LoadingResponsePlaceholder={ () =>
				<Spinner />
			}
			ErrorResponsePlaceholder={ () =>
				<p className={ 'llms-block-error' }>{ __( 'Error loading content. Please check block settings are valid. This block will not be displayed.', 'lifterlms' ) }</p>
			}
			EmptyResponsePlaceholder={ () =>
				<p className={ 'llms-block-empty' }>{ __( 'No progress data found for this course. This block will not be displayed.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

	if ( ! attributes.course_id && ! isLlmsPostType ) {
		setAttributes( {
			course_id: defaultCourseId,
		} );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Course Continue Settings', 'lifterlms' ) }
				>
					<CourseSelect { ...props } />
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
	edit: Edit,
} );
