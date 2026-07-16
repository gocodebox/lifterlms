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
import Icon from './icon.jsx';
import { PostSelect } from '../../../packages/components/src/post-select';

const Edit = ( props ) => {
	const { attributes } = props;
	const blockProps = useBlockProps();

	const memoizedServerSideRender = useMemo( () => {
		let emptyPlaceholder = __( 'Product not found. This block will not be displayed.', 'lifterlms' );

		if ( ! attributes.product ) {
			emptyPlaceholder = __( 'No product selected. Please choose a Course or Membership from the block sidebar panel.', 'lifterlms' );
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
				<PostSelect
					{ ...{
						...props,
						postType: [ 'course', 'llms_membership' ],
						attribute: 'product',
						postTypeAttribute: 'postType',
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
