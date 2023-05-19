// WordPress dependencies.
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
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';

const VisualLabel = BaseControl.VisualLabel ?? <></>;

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	const columns = {
		1: __( 'One', 'lifterlms' ),
		2: __( 'Two', 'lifterlms' ),
	};

	const memoizedServerSideRender = useMemo( () => {
		return <ServerSideRender
			block={ blockJson.name }
			attributes={ attributes }
			LoadingResponsePlaceholder={ () =>
				<Spinner />
			}
			ErrorResponsePlaceholder={ () =>
				<p className={ 'llms-block-error' }>
					{ __( 'There was an error loading the content. This block will not be displayed.', 'lifterlms' ) }
				</p>
			}
			EmptyResponsePlaceholder={ () =>
				<p className={ 'llms-block-empty' }>
					{ __( 'Checkout not available. This block will not be displayed.', 'lifterlms' ) }
				</p>
			}
		/>;
	}, [ attributes ] );

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
				{ memoizedServerSideRender }
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
} );
