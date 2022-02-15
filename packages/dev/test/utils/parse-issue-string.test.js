const { parseIssueString } = require( '../../src/utils' );

describe( 'parseIssueString', () => {
	const testData = [
		[ 'Should accept issue references to the current project', { org: 'gocodebox', repo: 'lifterlms', num: '123' }, '#123' ],
		[ 'Should accept issue references to the current project', { org: 'org', repo: 'repo', num: '456' }, 'org/repo#456' ],
	];
	it.each( testData )( '%s', ( name, expected, issue ) => {
		expect( parseIssueString( issue ) ).toStrictEqual( expected );
	} );
} );

