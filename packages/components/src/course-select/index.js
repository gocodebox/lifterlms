import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelRow, SelectControl } from '@wordpress/components';

export const llmsPostTypes = [
	'course',
	'lesson',
	'llms_quiz',
];

export const useLlmsPostType = () => {
	const postType = useSelect( ( select ) => select( 'core/editor' )?.getCurrentPostType(), [] );

	return llmsPostTypes.includes( postType );
};

export const useCourseOptions = () => {
	const { postType, courses } = useSelect( ( select ) => {
		return {
			postType: select( 'core/editor' )?.getCurrentPostType(),
			courses: select( 'core' ).getEntityRecords( 'postType', 'course' ),
		};
	}, [] );

	const courseOptions = [];

	if ( ! llmsPostTypes.includes( postType ) ) {
		courseOptions.push( {
			label: __( 'Select course', 'lifterlms' ),
			value: 0,
		} );
	}

	if ( courses?.length ) {
		courses.forEach( ( course ) => {
			courseOptions.push( {
				label: course.title.rendered + ' (ID: ' + course.id + ')',
				value: course.id,
			} );
		} );
	}

	if ( llmsPostTypes.includes( postType ) ) {
		courseOptions.unshift( {
			label: __( 'Inherit from current ', 'lifterlms' ) + postType?.replace( 'llms_', '' ),
			value: 0,
		} );
	}

	if ( ! courseOptions?.length ) {
		courseOptions.push( {
			label: __( 'Loading', 'lifterlms' ),
			value: 0,
		} );
	}

	return courseOptions;
};

export const CourseSelect = ( { attributes, setAttributes } ) => {
	const courseOptions = useCourseOptions();

	return <PanelRow>
		<SelectControl
			label={ __( 'Course', 'lifterlms' ) }
			help={ __( 'Select the course to associate with this block.', 'lifterlms' ) }
			value={ attributes.course_id ?? courseOptions?.[ 0 ]?.value }
			options={ courseOptions }
			onChange={ ( value ) => {
				setAttributes( {
					course_id: parseInt( value, 10 ),
				} );
			} }
		/>
	</PanelRow>;
};
