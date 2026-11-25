import PostSearchControl from '../post-search-control';

jest.mock( '../base-search-control', () => () => null );

describe( 'PostSearchControl', () => {
	test( 'Default result label decodes HTML entities in the post title', () => {
		const { formatSearchResultLabel } = PostSearchControl( {} ).props;

		expect(
			formatSearchResultLabel( {
				id: 123,
				title: { rendered: 'en dash &#8211; HTML entity &amp; more' },
			} )
		).toBe( 'en dash – HTML entity & more (ID# 123)' );
	} );
} );
