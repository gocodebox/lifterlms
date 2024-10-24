// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	TextControl,
	SelectControl,
	Disabled, Spinner,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	const memoizedServerSideRender = useMemo( () => {
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
				<p className={ 'llms-block-empty' }>{ __( 'Account preview not available. This block will not be displayed.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'My Account Settings', 'lifterlms' ) }>
				<PanelRow>
					<SelectControl
						label={ __( 'Layout', 'lifterlms' ) }
						options={ [
							{ label: __( 'Columns', 'lifterlms' ), value: 'columns' },
							{ label: __( 'Stacked', 'lifterlms' ), value: 'stacked' },
						] }
						value={ attributes.layout }
						onChange={ ( value ) => setAttributes( {
							layout: value,
						} ) }
					/>
				</PanelRow>
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
				{ memoizedServerSideRender }
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
} );
