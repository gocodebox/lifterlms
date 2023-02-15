import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	Flex,
	BaseControl,
	ButtonGroup,
	Button,
	Spinner,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';

const VisualLabel = BaseControl.VisualLabel ?? <></>;

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	const columns = {
		1: __( 'One', 'lifterlms' ),
		2: __( 'Two', 'lifterlms' ),
	};

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Checkout Settings', 'lifterlms' ) }>
				<PanelRow>
					<BaseControl
						help={ __( 'Determines the number of columns on the checkout screen. 1 or 2 are the only acceptable values.', 'lifterlms' ) }
					>
						<Flex
							direction={ 'column' }
						>
							<VisualLabel>
								{ __( 'Number of Columns', 'lifterlms' ) }
							</VisualLabel>
							<ButtonGroup>
								{ Object.keys( columns ).map( ( column ) => {
									return <Button
										key={ column }
										isPrimary={ column === attributes.cols }
										onClick={ () => setAttributes( {
											cols: column,
										} ) }
									>
										{ columns[ column ] }
									</Button>;
								} ) }
							</ButtonGroup>
						</Flex>
					</BaseControl>
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
							{ __( 'There was an error loading the content.', 'lifterlms' ) }
						</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p className={ 'llms-block-empty' }>
							{ __( 'There is no content to display.', 'lifterlms' ) }
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
