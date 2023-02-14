import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';

import blockJson from './block.json';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

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
		value: null,
	} ];

	if ( ! attributes.course_id && courseOptions.length >= 1 ) {
		attributes.course_id = courseOptions[ 0 ].value;
	}

	const isLlmsPostType = [ 'course', 'lesson', 'llms_quiz' ].includes( postType );

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Course Outline Settings', 'lifterlms' ) }>
				<PanelRow>
					<ToggleControl
						label={ __( 'Collapse', 'lifterlms' ) }
						help={ __( 'If true, will make the outline sections collapsible via click events.', 'lifterlms' ) }
						checked={ attributes.collapse }
						onChange={ ( collapse ) => setAttributes( {
							collapse,
						} ) }
					/>
				</PanelRow>
				{ attributes.collapse &&
					<PanelRow>
						<ToggleControl
							label={ __( 'Toggles', 'lifterlms' ) }
							help={ __( 'If true, will display “Collapse All” and “Expand All” toggles at the bottom of the outline. Only functions if “collapse” is true.', 'lifterlms' ) }
							checked={ attributes.toggles }
							onChange={ ( toggles ) => setAttributes( {
								toggles,
							} ) }
						/>
					</PanelRow>
				}
				{ ! isLlmsPostType &&
					<PanelRow>
						<SelectControl
							label={ __( 'Course', 'lifterlms' ) }
							help={ __( 'Select a course to display the course information for.', 'lifterlms' ) }
							value={ attributes.course_id }
							options={ courseOptions }
							onChange={ ( value ) => setAttributes( {
								course_id: value,
							} ) }
						/>
					</PanelRow>
				}
				<PanelRow>
					<SelectControl
						label={ __( 'Outline Type', 'lifterlms' ) }
						help={ __( 'Select the type of outline to display.', 'lifterlms' ) }
						value={ attributes.outline_type }
						options={ [
							{
								label: __( 'Full', 'lifterlms' ),
								value: 'full',
								isDefault: true,
							},
							{
								label: __( 'Current Section', 'lifterlms' ),
								value: 'current_section',
							},
						] }
						onChange={ ( value ) => setAttributes( {
							outline_type: value,
						} ) }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () => <p>{ __( 'Loading…', 'lifterlms' ) }</p>
					}
					ErrorResponsePlaceholder={ () =>
						<p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p>{ __( 'No outline information available for this course.', 'lifterlms' ) }</p>
					}
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
