import { registerBlockType } from '@wordpress/blocks';
import {
	BaseControl, Button, ButtonGroup,
	Disabled, Flex, PanelBody, PanelRow, TextControl,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Login Form Settings', 'lifterlms' ) }>
				<PanelRow>
					<BaseControl
						help={ __( 'Controls the size of the button.', 'lifterlms' ) }
					>
						<Flex
							direction={ 'column' }
						>
							<BaseControl.VisualLabel>
								{ __( 'Size', 'lifterlms' ) }
							</BaseControl.VisualLabel>
							<ButtonGroup>
								{ [ 'Columns', 'Stacked' ].map( ( layout ) => {
									const value = layout?.toLowerCase();

									return <Button
										key={ value }
										isPrimary={ value === attributes.layout }
										onClick={ () => setAttributes( {
											layout: value,
										} ) }
									>
										{ layout }
									</Button>;
								} ) }
							</ButtonGroup>
						</Flex>
					</BaseControl>
				</PanelRow>
				<PanelRow>
					<TextControl
						label={ __( 'Login redirect URL', 'lifterlms' ) }
						value={ attributes.redirect }
						onChange={ ( value ) => setAttributes( {
							redirect: value,
						} ) }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
		<div
			{ ...blockProps }
		>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () =>
						<p>{ __( 'Loading…', 'lifterlms' ) }</p>
					}
					ErrorResponsePlaceholder={ () =>
						<p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p>{ __( 'Displays LifterLMS register form.', 'lifterlms' ) }</p>
					}

				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
