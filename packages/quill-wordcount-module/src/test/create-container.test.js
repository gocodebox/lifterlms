import createContainer from '../create-container';

describe( 'createContainer()', () => {
	const testData = [
		[ 'No min and no max', { min: null, max: null } ],
		[ 'Min and no max', { min: 5, max: null, l10n: { min: 'Min' } } ],
		[ 'Max and no min', { min: null, max: 1000, l10n: { max: 'Max' } } ],
		[ 'Max and min', { min: 1, max: 2, l10n: { min: 'Min', max: 'Max' } } ],
	];
	test.each( testData )( '%s', ( testName, opts ) => {
		expect( createContainer( opts ) ).toMatchSnapshot();
	} );
} );
