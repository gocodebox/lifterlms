import apiFetch from '@wordpress/api-fetch';

import BaseSearchControl from '../base-search-control';

jest.mock( '@wordpress/api-fetch', () => jest.fn() );

const mockUseState = jest.fn( ( initial ) => [ initial, () => {} ] );
jest.mock( '@wordpress/element', () => ( {
	useState: ( initial ) => mockUseState( initial ),
	useEffect: () => {},
} ) );
jest.mock( '../styled-base-control', () => ( {
	StyledBaseControl: () => null,
} ) );

const getDefaultOptions = ( props ) =>
	BaseSearchControl( { searchPath: '/wp/v2/users', ...props } ).props.children
		.props.defaultOptions;

describe( 'BaseSearchControl', () => {
	test( 'Loads default options when nothing is selected', () => {
		expect( getDefaultOptions() ).toBe( true );
	} );

	test( 'Loads default options when a value is selected but not cached', () => {
		expect( getDefaultOptions( { selectedValue: [ 1 ] } ) ).toBe( true );
	} );

	test( 'Uses cached results when the selection is empty', () => {
		const cached = [
			{ id: 1, label: 'One', value: 1 },
			{ id: 1, label: 'One', value: 1 },
		];
		// First useState call holds the loaded results.
		mockUseState.mockImplementationOnce( () => [ cached, () => {} ] );
		expect( getDefaultOptions( { isMulti: true } ) ).toEqual( [
			cached[ 0 ],
		] );
	} );

	test( 'Ends loading when the request fails', async () => {
		apiFetch.mockImplementationOnce( () => Promise.reject( new Error() ) );
		const { loadOptions } = BaseSearchControl( {
			searchPath: '/wp/v2/users',
			searchDebounceDelay: 0,
		} ).props.children.props;
		const callback = jest.fn();

		loadOptions( '', callback );
		await new Promise( ( resolve ) => setTimeout( resolve, 10 ) );

		expect( callback ).toHaveBeenCalledWith( [] );
	} );

	test( 'Keeps default options passed by the caller', () => {
		const defaultOptions = [ { id: 1, label: 'One', value: 1 } ];
		expect( getDefaultOptions( { defaultOptions } ) ).toBe(
			defaultOptions
		);
	} );

	test( 'Allows the caller to turn off default options', () => {
		expect( getDefaultOptions( { defaultOptions: false } ) ).toBe( false );
	} );
} );
