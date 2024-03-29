// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	BaseControl, Button, ButtonGroup,
	Disabled, Flex, PanelBody, PanelRow, Spinner, TextControl,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';

const Edit = ( { attributes, setAttributes } ) => {
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
				<p className={ 'llms-block-empty' }>{ __( 'Displays LifterLMS register form. This block will not be displayed.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

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
				{ memoizedServerSideRender }
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
} );
