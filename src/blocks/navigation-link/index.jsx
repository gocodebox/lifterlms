// WordPress dependencies.
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, PanelRow, TextControl, SelectControl } from '@wordpress/components';
import { customLink } from '@wordpress/icons';

// Internal dependencies.
import blockJson from './block.json';

const links = window?.llmsNavMenuItems || [];

const linkOptions = Object.keys( links ).map( ( key ) => ( {
	label: links[ key ],
	value: key,
} ) );

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return <>
		<InspectorControls>
			<PanelBody
				title={ __( 'LifterLMS Link Settings', 'lifterlms' ) }
				className={ 'llms-navigation-link-settings' }
			>
				<PanelRow>
					<TextControl
						label={ __( 'Label', 'lifterlms' ) }
						value={ attributes.label ?? links?.dashboard ?? '' }
						onChange={ ( label ) => setAttributes( { label } ) }
						placeholder={ links?.[ attributes?.page ] ?? links?.dashboard ?? 'LifterLMS' }
					/>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'URL', 'lifterlms' ) }
						value={ attributes.page }
						options={ linkOptions }
						onChange={ ( value ) => setAttributes( {
							page: value,
							label: links[ value ],
						} ) }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
		<div
			{ ...blockProps }
		>
			<RichText
				tagName={ 'div' }
				value={ attributes.label }
				onChange={ ( label ) => setAttributes( { label } ) }
				placeholder={ links?.[ attributes?.page ] ?? links?.dashboard ?? 'LifterLMS' }
			/>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
	icon: customLink,
} );
