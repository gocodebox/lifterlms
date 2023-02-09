import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	SelectControl
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';

import blockJson from './block.json';

registerBlockType( blockJson, {
	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const blockProps                    = useBlockProps();

		const { courses, postType } = useSelect( ( select ) => {
			return {
				courses: select( 'core' )?.getEntityRecords( 'postType', 'course' ),
				postType: select( 'core/editor' )?.getCurrentPostType(),
			};
		}, [] );

		const courseOptions = courses?.map( ( course ) => {
			return {
				label: course.title.rendered,
				value: course.id,
			};
		} ) || [ {
			label: __( 'No courses found', 'lifterlms' ),
			value: 0,
		} ];

		if ( ! attributes.course_id && courseOptions.length >= 1 ) {
			attributes.course_id = courseOptions[ 0 ].value;
		}

		const isLlmsPostType = [ 'course', 'lesson', 'llms_quiz' ].includes( postType );

		return <>
			<InspectorControls>
				<PanelBody title={ __( 'Course Continue Settings', 'lifterlms' ) }>
					{ ! isLlmsPostType &&
					  <PanelRow>
						  <SelectControl
							  label={ __( 'Course', 'lifterlms' ) }
							  help={ __( 'Select a course to display the course information for.', 'lifterlms' ) }
							  value={ attributes.course_id }
							  options={ courseOptions }
							  onChange={ ( course_id ) => setAttributes( {
								  course_id: course_id
							  } ) }
						  />
					  </PanelRow>
					}
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ blockJson.name }
						attributes={ attributes }
						LoadingResponsePlaceholder={ () => <p>{ __( 'Loading...', 'lifterlms' ) }</p> }
						ErrorResponsePlaceholder={ () =>
							<p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p> }
						EmptyResponsePlaceholder={ () =>
							<p>{ __( 'No progress data found for this course.', 'lifterlms' ) }</p>
						}
					/>
				</Disabled>
			</div>
		</>;
	}
} );
