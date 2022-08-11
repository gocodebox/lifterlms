// External deps.
import styled from '@emotion/styled';
import InfiniteScroll from 'react-infinite-scroller';
import { debounce } from 'lodash';

import { __ } from '@wordpress/i18n';
import { Button, ButtonGroup, SearchControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

import iconMetadata from '../metadata.json';

import Icon from './icon';

/**
 * A styled div wrapper component.
 */
const Wrapper = styled.div`		
	& .llms-fa-icon-picker--content-header {
		border-bottom: 1px solid rgb( 204, 204, 204 );
		margin: 0 -8px;
		padding: 0 8px 8px;
	}
	& .llms-fa-icon-picker--list {
		display: flex;
		flex-wrap: wrap;
		padding-top: 8px;
		max-height: 440px;
		overflow: auto;
		> div {
			width: 100%;
		}
	}
	& .llms-fa-icon-picker--icon-button {
		border: 1px solid rgba( 0, 0, 0, 0.1 );
		display: inline-block;
		height: 90px;
		padding: 12px 12px 8px;
		margin: 2px;
		width: calc( 25% - 4px );
		vertical-align: middle;
	}
	& .llms-fa-icon-picker--icon-button i {
		display: block;
		font-size: 24px;
	}
	& .llms-fa-icon-picker--icon-button span {
		display: block;
		font-size: 10px;
	}
	
`;

const STYLES = [
	{
		label: __( 'Solid', 'lifterlms' ),
		id: 'solid',
	},
	{
		label: __( 'Regular', 'lifterlms' ),
		id: 'regular',
	},
	{
		label: __( 'Brands', 'lifterlms' ),
		id: 'brands',
	},
];

/**
 * Renders an infinitely scrollable list of the available icons.
 *
 * @since [version]
 *
 * @param {Object}   props                Component properties.
 * @param {Function} props.onChange       Function to call when a new icon is selected.
 * @param {string}   props.selectedStyle  The currently selected icon style.
 * @param {Object}   props.availableIcons The available icons to display.
 * @param {string}   props.iconPrefix     The project icon prefix.
 * @param {number}   props.perPage        Number of icons to display per "page" of results.
 * @return {WPElement} A scrollable list.
 */
function List( { onChange, selectedStyle, availableIcons, iconPrefix, perPage } ) {
	const [ endIndex, setEndIndex ] = useState( perPage ),
		numIcons = Object.keys( availableIcons ).length;
	return (
		<div className="llms-fa-icon-picker--list">
			<InfiniteScroll
				loadMore={ debounce( () => setEndIndex( endIndex + perPage ), 300 ) }
				hasMore={ ! numIcons || endIndex <= numIcons ? true : false }
				useWindow={ false }
			>
				{ Object.keys( availableIcons ).slice( 0, endIndex ).map( ( icon, key ) => (
					<Button
						key={ key }
						className="llms-fa-icon-picker--icon-button"
						onClick={ () => onChange( icon, selectedStyle, availableIcons[ icon ].label ) }
					>
						<Icon { ...{ icon, iconStyle: selectedStyle, iconPrefix } } />
						<span>{ availableIcons[ icon ].label }</span>
					</Button>
				) ) }
			</InfiniteScroll>
		</div>
	);
}

/**
 * Renders the icon picker list.
 *
 * @since [version]
 *
 * @param {Object}   props            Component properties.
 * @param {Function} props.onChange   Icon select callback function.
 * @param {string}   props.iconPrefix Project icon prefix.
 * @param {number}   props.perPage    Number of icons to display per page of results.
 * @return {WPElement} The list component.
 */
export default function( { onChange, iconPrefix, perPage = 48 } ) {
	const [ availableIcons, setAvailableIcons ] = useState( [] ),
		[ search, setSearch ] = useState( '' ),
		[ selectedStyle, setSelectedStyle ] = useState( STYLES[ 0 ].id );

	useEffect( () => {
		const normalSearch = search.toLowerCase(),
			filteredIcons = Object.fromEntries(
				Object.entries( iconMetadata ).filter( ( [ , { terms, styles } ] ) => {
					if ( ! styles.includes( selectedStyle ) ) {
						return false;
					}

					return ! search || terms.map( ( term ) => term.toLowerCase() ).some( ( term ) => term.includes( normalSearch ) );
				} )
			);

		setAvailableIcons( { ...filteredIcons } );
	}, [ search, selectedStyle ] );

	return (
		<Wrapper>
			<header className="llms-fa-icon-picker--content-header">
				<SearchControl
					value={ search }
					onChange={ ( newSearch ) => setSearch( newSearch ) }
				/>
				<span>{ __( 'Styles:', 'lifterlms' ) } </span>
				<ButtonGroup>
					{ STYLES.map( ( { label, id } ) => (
						<Button
							key={ id }
							text={ label }
							variant={ selectedStyle === id ? 'primary' : 'secondary' }
							onClick={ () => setSelectedStyle( id ) }
						/>
					) ) }
				</ButtonGroup>
			</header>
			<List { ...{ onChange, selectedStyle, availableIcons, iconPrefix, perPage } } />
		</Wrapper>
	);
}
