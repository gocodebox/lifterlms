import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
	RangeControl,
	SelectControl,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';
import { useState } from '@wordpress/element';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();

	const [ users, setUsers ] = useState( [] );

	apiFetch( { path: '/wp/v2/users' } )
		.then( ( userData ) => {
			setUsers( userData );
		} );

	const userOptions = [
		{
			label: __( 'Current user', 'lifterlms' ),
			value: 0,
		},
	];

	users.forEach( ( user ) => {
		userOptions.push( {
			label: user.name,
			value: user.id,
		} );
	} );

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'My Account Settings', 'lifterlms' ) }>
				<PanelRow>
					<NumberControl
						label={ __( 'Count', 'lifterlms' ) }
						help={ __( 'Number of achievements to display. Leave empty to display all achievements for user.', 'lifterlms' ) }
						value={ attributes.count }
						onChange={ ( value ) => setAttributes( {
							count: value,
						} ) }
					/>
				</PanelRow>
				<PanelRow>
					<RangeControl
						label={ __( 'Columns', 'lifterlms' ) }
						help={ __( 'Number of columns to display.', 'lifterlms' ) }
						value={ attributes.columns }
						onChange={ ( value ) => setAttributes( {
							columns: value,
						} ) }
						min={ 1 }
						max={ 12 }
					/>
				</PanelRow>
				<PanelRow>
					<SelectControl
						label={ __( 'User', 'lifterlms' ) }
						help={ __( 'Select a user to display achievements for. Leave empty to display achievements for the current user.', 'lifterlms' ) }
						value={ attributes.user_id }
						options={ userOptions }
						onChange={ ( value ) => setAttributes( {
							user_id: value,
						} ) }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () => <p>{ __( 'Loading…', 'lifterlms' ) }</p> }
					ErrorResponsePlaceholder={ () =>
						<p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p> }
				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	Edit,
} );
