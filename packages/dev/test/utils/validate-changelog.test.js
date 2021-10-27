const {
	isAttributionValid,
	isLinkValid,
	getChangelogValidationIssues,
} = require( '../../src/utils' );

describe( 'isAttributionValid', () => {
	const testData = [
		// Valid data.
		[ 'Should accept a GitHub username', '@username', true ],
		[ 'Should accept a markdown link', '[username](https://fake.tld)', true ],
		// Invalid data.
		[ 'Should not accept a username without a leading @ symbol', 'username', false ],
		[ 'Should not accept a markdown link without a fully qualified URL', '[username](www.fake.tld)', false ],
		[ 'Should not accept a markdown reference link', '[username][link]', false ],
		// Weird types.
		[ 'Should not accept an integer', 123, false ],
		[ 'Should not accept an object', {}, false ],
		[ 'Should not accept an array', [], false ],
	];
	it.each( testData )( '%s', ( name, input, expected ) => {
		expect( isAttributionValid( input ) ).toStrictEqual( expected );
	} );
} );

describe( 'isLinkValid', () => {
	const testData = [
		// Valid data.
		[ 'Should accept a reference to the current repo', '#123', true ],
		[ 'Should accept a reference to another repo', 'org/repo#123', true ],
		// Invalid data.
		[ 'Should not accept a local reference without a # symbol', '123', false ],
		[ 'Should not accept a reference to another repo without a # symbol', 'org/repo123', false ],
		[ 'Should not accept a reference to another repo without an organization', 'repo#123', false ],
		// Weird types.
		[ 'Should not accept an integer', 123, false ],
		[ 'Should not accept an object', {}, false ],
		[ 'Should not accept an array', [], false ],
	];
	it.each( testData )( '%s', ( name, input, expected ) => {
		expect( isLinkValid( input ) ).toStrictEqual( expected );
	} );
} );

describe( 'getChangelogValidationIssues', () => {
	it( 'should return errors when missing required fields', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( {}, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'Missing required field: "significance".', 'Missing required field: "type".', 'Missing required field: "entry".' ] );
	} );

	it( 'should return errors for invalid significance values', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( { significance: 'fake', type: 'changed', entry: 'entry' }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'Invalid value "fake" supplied for field: "significance".' ] );
	} );

	it( 'should return errors for invalid type values', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( { type: 'fake', significance: 'patch', entry: 'entry' }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'Invalid value "fake" supplied for field: "type".' ] );
	} );

	it( 'should return warnings when non-standard keys are found in the entry object', () => {
		const { valid, warnings } = getChangelogValidationIssues( { extra: 1 }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [ 'Unexpected key: "extra".' ] );
	} );

	it( 'should return errors when an array is not submitted for the attributions list', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( { attributions: 1, type: 'changed', significance: 'patch', entry: 'entry' }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'The "attributions" field must be an array.' ] );
	} );

	it( 'should return errors when an array is not submitted for the links list', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( { links: 1, type: 'changed', significance: 'patch', entry: 'entry' }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'The "links" field must be an array.' ] );
	} );

	it( 'should return errors when an invalid attribution is submitted', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( { attributions: [ 'abc' ], type: 'changed', significance: 'patch', entry: 'entry' }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'The attribution "abc" is invalid.' ] );
	} );

	it( 'should return errors when an invalid link is submitted', () => {
		const { valid, errors, warnings } = getChangelogValidationIssues( { links: [ 'abc' ], type: 'changed', significance: 'patch', entry: 'entry' }, false );

		expect( valid ).toStrictEqual( false );
		expect( warnings ).toStrictEqual( [] );
		expect( errors ).toStrictEqual( [ 'The link "abc" is invalid.' ] );
	} );

	const testData = [
		[
			'should validate a valid entry that is missing optional fields',
			{
				significance: 'major',
				type: 'added',
				entry: 'Entry content.',
			},
			[],
		],
		[
			'should validate a valid entry that includes valid optional fields',
			{
				significance: 'major',
				type: 'added',
				entry: 'Entry content.',
				comment: 'A comment',
				title: 'title',
				attributions: [ '@username', '[user](https://fake.tld)' ],
				links: [ '#1234', 'org/repo#123' ],
			},
			[],
		],
		[
			'should validate a valid entry that has warnings and no errors',
			{
				significance: 'major',
				type: 'added',
				entry: 'Entry content.',
				fake: 'extra-field-generates-warning',
			},
			[ 'Unexpected key: "fake".' ],
		],
	];
	it.each( testData )( '%s', ( name, entry, expectedWarnings ) => {
		const { valid, errors, warnings } = getChangelogValidationIssues( entry, false );
		expect( valid ).toStrictEqual( true );
		expect( warnings ).toStrictEqual( expectedWarnings );
		expect( errors ).toStrictEqual( [] );
	} );
} );
