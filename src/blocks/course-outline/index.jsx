// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	ToggleControl,
	SelectControl,
	Spinner,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';
import { usePostOptions, useLlmsPostType, PostSelect } from '../../../packages/components/src/post-select';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const isLlmsPostType = useLlmsPostType();
	const courseOptions = usePostOptions();

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
				<p className={ 'llms-block-empty' }>{ __( 'No outline information available for this course. This block will not be displayed.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

	if ( ! attributes.course_id && ! isLlmsPostType ) {
		setAttributes( {
			course_id: courseOptions?.[ 0 ]?.value,
		} );
	}

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Course Outline Settings', 'lifterlms' ) }>
				<PanelRow>
					<ToggleControl
						label={ __( 'Collapse', 'lifterlms' ) }
						help={ __( 'If true, will make the outline sections collapsible via click events.', 'lifterlms' ) }
						checked={ attributes.collapse }
						onChange={ ( collapse ) => setAttributes( {
							collapse,
						} ) }
					/>
				</PanelRow>
				{ attributes.collapse &&
					<PanelRow>
						<ToggleControl
							label={ __( 'Toggles', 'lifterlms' ) }
							help={ __( 'If true, will display "Collapse All" and "Expand All" toggles at the bottom of the outline. Only functions if "collapse" is true.', 'lifterlms' ) }
							checked={ attributes.toggles }
							onChange={ ( toggles ) => setAttributes( {
								toggles,
							} ) }
						/>
					</PanelRow>
				}
				<PostSelect { ...props } />
				<PanelRow>
					<SelectControl
						label={ __( 'Outline Type', 'lifterlms' ) }
						help={ __( 'Select the type of outline to display.', 'lifterlms' ) }
						value={ attributes.outline_type }
						options={ [
							{
								label: __( 'Full', 'lifterlms' ),
								value: 'full',
								isDefault: true,
							},
							{
								label: __( 'Current Section', 'lifterlms' ),
								value: 'current_section',
							},
						] }
						onChange={ ( value ) => setAttributes( {
							outline_type: value,
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
