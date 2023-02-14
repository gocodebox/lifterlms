import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	TextControl,
	Disabled,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'My Account Settings', 'lifterlms' ) }>
				<PanelRow>
					<TextControl
						label={ __( 'Login redirect URL', 'lifterlms' ) }
						value={ attributes.login_redirect }
						onChange={ ( value ) => setAttributes( {
							login_redirect: value,
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
					LoadingResponsePlaceholder={ () => <p>{ __( 'Loading…', 'lifterlms' ) }</p> }
					ErrorResponsePlaceholder={ () => <p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p> }
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	Edit,
} );
