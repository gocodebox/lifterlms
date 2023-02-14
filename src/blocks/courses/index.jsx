import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	SelectControl,
	ToggleControl,
	Disabled,
	FormTokenField,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
	BaseControl,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	// Get course categories.
	const { categories, courses } = useSelect( ( select ) => {
		return {
			categories: select( 'core' )?.getEntityRecords( 'taxonomy', 'course_cat' ),
			courses: select( 'core' )?.getEntityRecords( 'postType', 'course' ),
		};
	}, [] );

	const categoryOptions = categories?.map( ( category ) => {
		return {
			value: category.slug,
			label: category.name,
		};
	} );

	categoryOptions?.unshift( {
		value: '',
		label: __( 'All', 'lifterlms' ),
	} );

	const courseOptions = {};

	courses?.map( ( course ) => {
		courseOptions[ course.id ] = course.title.rendered;

		return course;
	} );

	const [ courseTitles, setCourseTitles ] = useState( [] );

	return <>

		<InspectorControls>
			<PanelBody title={ __( 'Courses Settings', 'lifterlms' ) }>
				<PanelRow>
					<SelectControl
						label={ __( 'Category', 'lifterlms' ) }
						value={ attributes.category }
						options={ categoryOptions }
						onChange={ ( value ) => setAttributes( { category: value } ) }
						help={ __( 'Display courses from a specific Course Category only. Use a category’s “slug”. If omitted, will display courses from all categories.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={ __( 'Show hidden courses?', 'lifterlms' ) }
						checked={ attributes.hidden }
						onChange={ ( value ) => setAttributes( { hidden: value } ) }
						help={ __( 'Whether or not courses with a “hidden” visibility should be included. Defaults to “yes” (hidden courses displayed). Switch to “no” to exclude hidden courses.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<BaseControl
						help={ __( 'Display only specific course(s). You can select multiple courses.', 'lifterlms' ) }
					>
						<FormTokenField
							label={ __( 'Courses', 'lifterlms' ) }
							placeholder={ __( 'Search available courses', 'lifterlms' ) }
							suggestions={ Object.values( courseOptions ) }
							value={ courseTitles }
							onChange={ ( value ) => {
								setCourseTitles( value );
								setAttributes( {
									id: value.map( ( title ) => {
										return Object.keys( courseOptions ).find( ( key ) => courseOptions[ key ] === title );
									} ).join( ',' ),
								} );
							} }
							__experimentalShowHowTo={ false }
						/>
					</BaseControl>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'Show only my courses', 'lifterlms' ) }
						options={ [
							{ value: 'no', label: __( 'No', 'lifterlms' ) },
							{ value: 'any', label: __( 'Any', 'lifterlms' ) },
							{ value: 'enrolled', label: __( 'Enrolled', 'lifterlms' ) },
							{ value: 'expired', label: __( 'Expired', 'lifterlms' ) },
							{ value: 'cancelled', label: __( 'Cancelled', 'lifterlms' ) },
						] }
						checked={ attributes.mine }
						onChange={ ( value ) => setAttributes( { mine: value } ) }
						help={ __( 'Show only courses the current student is enrolled in. By default (“no”) shows courses regardless of enrollment.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'Order', 'lifterlms' ) }
						value={ attributes.order }
						options={ [
							{ value: 'ASC', label: __( 'Ascending', 'lifterlms' ) },
							{ value: 'DESC', label: __( 'Descending', 'lifterlms' ) },
						] }
						onChange={ ( value ) => setAttributes( { order: value } ) }
						help={ __( 'Display courses in ascending or descending order.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'Order by', 'lifterlms' ) }
						value={ attributes.orderby }
						options={ [
							{ value: 'id', label: __( 'ID', 'lifterlms' ) },
							{ value: 'author', label: __( 'Author', 'lifterlms' ) },
							{ value: 'title', label: __( 'Title', 'lifterlms' ) },
							{ value: 'name', label: __( 'Name', 'lifterlms' ) },
							{ value: 'date', label: __( 'Date', 'lifterlms' ) },
							{ value: 'modified', label: __( 'Date modified', 'lifterlms' ) },
							{ value: 'rand', label: __( 'Random', 'lifterlms' ) },
							{ value: 'menu_order', label: __( 'Menu Order', 'lifterlms' ) },
						] }
						onChange={ ( value ) => setAttributes( { orderby: value } ) }
						help={ __( 'Determines which field is used to order courses in the courses list.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<NumberControl
						label={ __( 'Per Page', 'lifterlms' ) }
						value={ attributes.posts_per_page }
						min={ -1 }
						max={ 100 }
						onChange={ ( value ) => setAttributes( { posts_per_page: value ?? -1 } ) }
						help={ __( ' Determines the number of results to display. Default returns all available courses.', 'lifterlms' ) }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>

		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () => <p>Loading...</p> }
					ErrorResponsePlaceholder={ () => <p>Error</p> }
				/>
			</Disabled>
		</div>

	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
