// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	Disabled,
	PanelBody,
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

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const sectionOptions = [
		{ value: 'navigation', label: 'Navigation' },
		{ value: 'content', label: 'Content' },
	];

	const memoizedServerSideRender = useMemo( () => {
		return <ServerSideRender
			block={ blockJson.name }
			attributes={ {
				section: attributes.section ?? '',
			} }
			LoadingResponsePlaceholder={ () =>
				<Spinner />
			}
			ErrorResponsePlaceholder={ () =>
				<p className={ 'llms-block-error' }>{ __( 'Error loading section.', 'lifterlms' ) }</p>
			}
			EmptyResponsePlaceholder={ () =>
				<p className={ 'llms-block-empty' }>{ __( 'No section found.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

	return <>
		<InspectorControls>
			<PanelBody
				title={ __( 'Section Settings', 'lifterlms' ) }
			>
				<SelectControl
					label={ __( 'Section', 'lifterlms' ) }
					value={ attributes.section }
					options={ sectionOptions }
					onChange={ ( value ) => setAttributes( { section: value } ) }
				/>
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
