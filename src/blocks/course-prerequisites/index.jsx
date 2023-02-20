// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	Disabled,
	Spinner,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

// Internal dependencies.
import { CourseSelect, useCourseOptions, useLlmsPostType } from '../../../packages/components/src/course-select';
import blockJson from './block.json';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const isLlmsPostType = useLlmsPostType();
	const courseOptions = useCourseOptions();

	if ( ! attributes.course_id && ! isLlmsPostType ) {
		setAttributes( {
			course_id: courseOptions?.[ 0 ]?.value,
		} );
	}

	return <>
		{ ! isLlmsPostType &&
			<InspectorControls>
				<PanelBody
					title={ __( 'Course Prerequisites Settings', 'lifterlms' ) }
				>
					<CourseSelect { ...props } />
				</PanelBody>
			</InspectorControls>
		}
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () =>
						<Spinner />
					}
					ErrorResponsePlaceholder={ () =>
						<p className={ 'llms-block-error' }>{ __( 'Error loading content. Please check block settings are valid. This block will not be displayed.', 'lifterlms' ) }</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p className={ 'llms-block-empty' }>{ __( 'No prerequisites available for this course. This block will not be displayed.', 'lifterlms' ) }</p>
					}
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
