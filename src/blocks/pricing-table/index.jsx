// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	SelectControl,
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
import Icon from './icon.jsx';
import {
	usePostOptions,
	PostSelect,
} from '../../../packages/components/src/post-select';

const postTypeOptions = [
	{ label: __( 'Course', 'lifterlms' ), value: 'course' },
	{ label: __( 'Membership', 'lifterlms' ), value: 'llms_membership' },
];

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const courseOptions = usePostOptions();

	const memoizedServerSideRender = useMemo( () => {
		let emptyPlaceholder = __( 'Author not found. This block will not be displayed.', 'lifterlms' );

		if ( ! attributes.product && courseOptions.length > 0 ) {
			emptyPlaceholder = __( 'No course selected. Please choose a Course from the block sidebar panel.', 'lifterlms' );
		}

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
				<p className={ 'llms-block-empty' }>{ emptyPlaceholder }</p>
			}
		/>;
	}, [ attributes ] );

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Pricing Table Settings', 'lifterlms' ) }>
				<PanelRow>
					<SelectControl
						label={ __( 'Post Type', 'lifterlms' ) }
						value={ attributes.postType }
						options={ postTypeOptions }
						onChange={ ( postType ) => setAttributes( {
							postType,
							product: '',
						} ) }
					/>
				</PanelRow>
				<PostSelect
					{ ...{
						...props,
						postType: attributes?.postType ?? 'course',
						attribute: 'product',
					} }
				/>
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
	icon: Icon,
	edit: Edit,
} );
