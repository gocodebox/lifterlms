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
	const courses = useSelect( ( select ) => select( 'core' )?.getEntityRecords( 'postType', 'course' ), [] );
	const postType = useSelect( ( select ) => select( 'core/editor' )?.getCurrentPostType(), [] );

	const courseOptions = courses?.map( ( course ) => {
		return {
			label: course.title.rendered,
			value: course.id,
		};
	} ) ?? [];

	if ( llmsPostTypes.includes( postType ) ) {
		courseOptions.unshift( {
			label: __( 'Inherit from current ', 'lifterlms' ) + postType?.replace( 'llms_', '' ),
			value: 0,
		} );
	}

	if ( ! courseOptions?.length ) {
		courseOptions.push( {
			label: __( 'No courses found', 'lifterlms' ),
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
			help={ __( 'The course to display the author for.', 'lifterlms' ) }
			value={ attributes.course_id ?? courseOptions?.[ 0 ]?.value }
			options={ courseOptions }
			onChange={ ( value ) => setAttributes( {
				course_id: value,
			} ) }
		/>
	</PanelRow>;
};
