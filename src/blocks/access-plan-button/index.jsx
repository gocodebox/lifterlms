// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	ButtonGroup,
	Flex,
	Disabled,
	SelectControl,
	Button,
	BaseControl,
	Spinner,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

// Internal dependencies.
import blockJson from './block.json';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	const accessPlans = window?.llmsShortcodeBlocks?.accessPlans || {
		label: __( 'No Access Plans Found', 'lifterlms' ),
		value: '',
	};

	if ( ! attributes?.id ) {
		attributes.id = accessPlans?.[ 0 ]?.value;
	}

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Access Plan Button Settings', 'lifterlms' ) }>
				<PanelRow>
					<SelectControl
						label={ __( 'Access Plan', 'lifterlms' ) }
						help={ __( 'Select the access plan to display a button for.', 'lifterlms' ) }
						value={ attributes.id ?? '' }
						options={ accessPlans }
						onChange={ ( id ) => setAttributes( { id } ) }
					/>
				</PanelRow>
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
								{ [ 'Default', 'Large', 'Small' ].map( ( size ) => {
									const value = size.toLowerCase();

									return <Button
										key={ value }
										isPrimary={ value === attributes.size }
										onClick={ () => setAttributes( {
											size: value,
										} ) }
									>
										{ size }
									</Button>;
								} ) }
							</ButtonGroup>
						</Flex>
					</BaseControl>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'Type', 'lifterlms' ) }
						help={ __( 'Controls the style of the button. Your theme and/or custom CSS may alter the colors defined by these styles.', 'lifterlms' ) }
						value={ attributes.type }
						options={ [
							{
								label: __( 'Primary', 'lifterlms' ),
								value: 'primary',
							},
							{
								label: __( 'Secondary', 'lifterlms' ),
								value: 'secondary',
							},
							{
								label: __( 'Action', 'lifterlms' ),
								value: 'action',
							},
							{
								label: __( 'Danger', 'lifterlms' ),
								value: 'danger',
							},
						] }
						onChange={ ( type ) => setAttributes( { type } ) }
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
						<p className={ 'llms-block-error' }>
							{ __( 'Error loading content. Please check block settings are valid. This block will not be displayed.', 'lifterlms' ) }
						</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p className={ 'llms-block-empty' }>
							{ __( 'No Access Plans found matching your selection. This block will not be displayed.', 'lifterlms' ) }
						</p>
					}
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	edit: Edit,
} );
