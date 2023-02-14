import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	RangeControl,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
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
			<PanelBody title={ __( 'Course Author Settings', 'lifterlms' ) }>
				<PanelRow>
					<RangeControl
						label={ __( 'Avatar Size', 'lifterlms' ) }
						help={ __( 'The size of the avatar in pixels.', 'lifterlms' ) }
						value={ attributes.avatar_size }
						onChange={ ( size ) => setAttributes( {
							avatar_size: size,
						} ) }
						min={ 0 }
						max={ 300 }
						allowReset={ true }
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={ __( 'Display Bio', 'lifterlms' ) }
						help={ attributes.bio ? __( 'Bio is displayed.', 'lifterlms' ) : __( 'Bio is hidden.', 'lifterlms' ) }
						checked={ attributes.bio }
						onChange={ ( bio ) => setAttributes( {
							bio,
						} ) }
					/>
				</PanelRow>
				{ ! isLlmsPostType &&
				<PanelRow>
					<SelectControl
						label={ __( 'Course', 'lifterlms' ) }
						help={ __( 'The course to display the author for.', 'lifterlms' ) }
						value={ attributes.course_id }
						options={ courseOptions }
						onChange={ ( value ) => setAttributes( {
							course_id: value,
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
					LoadingResponsePlaceholder={ () =>
						<p>{ __( 'Loading…', 'lifterlms' ) }</p>
					}
					ErrorResponsePlaceholder={ () =>
						<p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p>{ __( 'Author not found.', 'lifterlms' ) }</p>
					}
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
