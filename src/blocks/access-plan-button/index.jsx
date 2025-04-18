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
	TextControl,
	ComboboxControl,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { debounce } from 'lodash';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState, useMemo, useRef } from '@wordpress/element';

import blockJson from './block.json';
import Icon from './icon.jsx';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const [ accessPlans, setAccessPlans ] = useState( [
		{
			label: __( 'No Access Plans Found', 'lifterlms' ),
			value: '',
		},
	] );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const lastFetchRef = useRef( null );

	// Fetch plans from API based on search term.
	const fetchPlans = ( term, initialLoad = false, value = '' ) => { //debounce( ( term ) => {
		setIsLoading( true );

		// Cancel previous fetch if exists.
		// if ( lastFetchRef.current ) {
		// 	lastFetchRef.current.abort();
		// }
		//
		// const controller = new AbortController();
		// lastFetchRef.current = controller;

		apiFetch( {
			path: `/llms/v1/access-plans?per_page=100&search=${ encodeURIComponent( term ) }`,
			//signal: controller.signal,
		} )
			.then( ( plans ) => {
				const planOptions = plans.map( ( plan ) => {
					return {
						label: plan.title.rendered,
						value: plan.id.toString(),
					};
				} );

				setAccessPlans( [
					{
						label: __( 'Select access plan', 'lifterlms' ),
						value: '',
					},
					...planOptions,
				] );

				if ( initialLoad && value && !planOptions.some( o => o.value === value ) ) {
					apiFetch( { path: `/llms/v1/access-plans/include=${ value }` } )
						.then( ( plans ) => {
							setAccessPlans( [
								{
									label: __( 'Select access plan', 'lifterlms' ),
									value: '',
								},
								...plans.map( ( plan ) => {
								return {
									label: plan.title.rendered,
									value: plan.id.toString(),
								};
							} ) ] );
						})
						.catch(() => {});
				}
			} )
			.catch( ( err ) => {
				if ( err.name !== 'AbortError' ) {
					setAccessPlans( [] );
				}
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	}; //, 300 ); // 300ms debounce delay

	useEffect( () => {
		fetchPlans( '', true, attributes.id );
	}, [ attributes ] );

	useEffect( () => {

		const timeout = setTimeout( () => fetchPlans( searchTerm ), 300 );
		return () => clearTimeout( timeout );

	}, [ searchTerm ] );

	//
	//
	// useEffect( () => {
	// 	apiFetch( {
	// 		path: '/llms/v1/access-plans?per_page=100',
	// 	} )
	// 		.then( ( plans ) => {
	// 			const planOptions = plans.map( ( plan ) => {
	// 				return {
	// 					label: plan.title.rendered,
	// 					value: plan.id,
	// 				};
	// 			} );
	//
	// 			setAccessPlans( [
	// 				{
	// 					label: __( 'Select access plan', 'lifterlms' ),
	// 					value: '',
	// 				},
	// 				...planOptions,
	// 			] );
	// 		} )
	// 		.catch( () => {
	// 			setAccessPlans( [] );
	// 		} );
	// }, [] );

	const memoizedServerSideRender = useMemo( () => {
		let emptyPlaceholder = __( 'No Access Plans found matching your selection. This block will not be displayed.', 'lifterlms' );

		if ( ! attributes.id && accessPlans.length > 0 ) {
			emptyPlaceholder = __( 'No Access Plan selected. Please choose an Access Plan from the block sidebar panel.', 'lifterlms' );
		}

		return (
			<ServerSideRender
				block={ blockJson.name }
				attributes={ attributes }
				LoadingResponsePlaceholder={ () => <Spinner /> }
				ErrorResponsePlaceholder={ () => (
					<p className={ 'llms-block-error' }>
						{ __(
							'Error loading content. Please check block settings are valid. This block will not be displayed.',
							'lifterlms'
						) }
					</p>
				) }
				EmptyResponsePlaceholder={ () => (
					<p className={ 'llms-block-empty' }>
						{ emptyPlaceholder }
					</p>
				) }
			/>
		);
	}, [ attributes ] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Access Plan Button Settings', 'lifterlms' ) }>
					<PanelRow>
						<ComboboxControl
							label={ __( 'Access Plan', 'lifterlms' ) }
							value={ attributes.id ?? '' }
							options={ accessPlans }
							onChange={ ( id ) => setAttributes( { id } ) }
							isLoading={ isLoading }
							onFilterValueChange={ setSearchTerm }
							allowReset={ true }
							help={ __(
								'Select the access plan to display a button for.',
								'lifterlms'
							) }
						/>
					</PanelRow>
					<PanelRow>
						<BaseControl
							help={ __(
								'Controls the size of the button.',
								'lifterlms'
							) }
						>
							<Flex direction={ 'column' }>
								<BaseControl.VisualLabel>
									{ __( 'Size', 'lifterlms' ) }
								</BaseControl.VisualLabel>
								<ButtonGroup>
									{ [ 'Default', 'Large', 'Small' ].map( ( size ) => {
										const value = size.toLowerCase();

										return (
											<Button
												key={ value }
												isPrimary={
													value === attributes.size
												}
												onClick={ () =>
													setAttributes( {
														size: value,
													} )
												}
											>
												{ size }
											</Button>
										);
									} ) }
								</ButtonGroup>
							</Flex>
						</BaseControl>
					</PanelRow>
					<PanelRow>
						<SelectControl
							label={ __( 'Type', 'lifterlms' ) }
							help={ __(
								'Controls the style of the button. Your theme and/or custom CSS may alter the colors defined by these styles.',
								'lifterlms'
							) }
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
					<PanelRow>
						<TextControl
							label={ __( 'Text', 'lifterlms' ) }
							help={ __(
								'The text to display on the button.',
								'lifterlms'
							) }
							value={ attributes.text }
							onChange={ ( text ) => setAttributes( { text } ) }
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>{ memoizedServerSideRender }</Disabled>
			</div>
		</>
	);
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
} );
