// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	SelectControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
	TextControl,
	Disabled,
	Spinner,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	const { categories, memberships } = useSelect( ( select ) => {
		return {
			categories: select( 'core' )?.getEntityRecords( 'taxonomy', 'membership_cat' ),
			memberships: select( 'core' )?.getEntityRecords( 'postType', 'membership' ),
		};
	}, [] );

	const categoryOptions = categories?.map( ( category ) => {
		return {
			value: category.slug,
			label: category.name,
		};
	} );

	const membershipOptions = {};

	const memoizedServerSideRender = useMemo( () => {
		return <ServerSideRender
			block={ blockJson.name }
			attributes={ attributes }
			LoadingResponsePlaceholder={ () =>
				<Spinner />
			}
			ErrorResponsePlaceholder={ () =>
				<p className={ 'llms-block-error' }>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
			}
			EmptyResponsePlaceholder={ () =>
				<p className={ 'llms-block-empty' }>{ __( 'No memberships found matching this criteria.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

	categoryOptions?.unshift( {
		value: '',
		label: __( 'All', 'lifterlms' ),
	} );

	memberships?.map( ( membership ) => {
		membershipOptions[ membership.id ] = membership.title.rendered;

		return membership;
	} );

	return <>

		<InspectorControls>
			<PanelBody title={ __( 'Memberships Settings', 'lifterlms' ) }>
				<PanelRow>
					<SelectControl
						label={ __( 'Category', 'lifterlms' ) }
						value={ attributes.category }
						options={ categoryOptions }
						onChange={ ( value ) => setAttributes( { category: value } ) }
						help={ __( 'Display courses from a specific Membership Category only.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<TextControl
						label={ __( 'Membership ID', 'lifterlms' ) }
						value={ attributes.id }
						onChange={ ( value ) => setAttributes( { id: value } ) }
						help={ __( 'Display only a specific membership. Use the memberships’s post ID. If using this option, all other options are rendered irrelevant.', 'lifterlms' ) }
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
						help={ __( 'Display memberships in ascending or descending order.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'Order by', 'lifterlms' ) }
						value={ attributes?.orderby }
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
						onChange={ ( value ) => setAttributes( {
							orderby: value,
						} ) }
						help={ __( 'Determines which field is used to order memberships in the memberships list. This block will not be displayed.', 'lifterlms' ) }
					/>
				</PanelRow>
				<PanelRow>
					<NumberControl
						label={ __( 'Per Page', 'lifterlms' ) }
						value={ attributes.posts_per_page }
						min={ -1 }
						max={ 100 }
						onChange={ ( value ) => setAttributes( { posts_per_page: value ?? -1 } ) }
						help={ __( ' Determines the number of results to display. Default returns all available memberships. This block will not be displayed.', 'lifterlms' ) }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>

		<div { ...blockProps }>
			<Disabled>
				{ memoizedServerSideRender }
			</Disabled>
		</div>

	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
