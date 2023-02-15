import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	TextControl,
	Disabled, Spinner,
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
					LoadingResponsePlaceholder={ () =>
						<Spinner />
					}
					ErrorResponsePlaceholder={ () =>
						<p className={ 'llms-block-error' }>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p className={ 'llms-block-empty' }>{ __( 'Account preview not available.', 'lifterlms' ) }</p>
					}
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
