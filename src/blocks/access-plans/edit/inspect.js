// import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { RangeControl, PanelBody } from '@wordpress/components';
import { useSetting, InspectorControls, PanelColorSettings, withColors } from '@wordpress/block-editor';


export default function Inspect( {
	attributes,
	setAttributes,
	accentColor,
	setAccentColor,
	accentTextColor,
	setAccentTextColor,
} ) {

	const { plansPerRow } = attributes;

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Layout & Display', 'lifterlms' ) } initialOpen={ true }>
				<RangeControl
					label={ __( 'Plans per row' ) }
					help={ __( 'The maximum number of plans displayed on each row.' ) }
					value={ plansPerRow }
					onChange={ ( value ) => setAttributes( { plansPerRow: value } ) }
					min={ 1 }
					max={ 6 }
				/>
			</PanelBody>

			<PanelColorSettings
				__experimentalHasMultipleOrigins
				__experimentalIsRenderedInSidebar
				title={ __( 'Color' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: accentColor.color,
						label: __( 'Accent Color' ),
						onChange: setAccentColor,
					},
					{
						value: accentTextColor.color,
						label: __( 'Accent Text Color' ),
						onChange: setAccentTextColor,
					},
				] }
			>
			</PanelColorSettings>
		</InspectorControls>
	);


}
