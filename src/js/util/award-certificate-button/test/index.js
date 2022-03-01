import getMessage from '../message';
import { getScratchUrl, getRedirectUrl } from '../urls';

describe( 'AwardCertificateButton', () => {
	describe( 'getMessage', () => {
		const testData = [
			[ 'No student and no template', null, null ],
			[ 'Student and no template', 123, null ],
			[ 'Template and no student', null, 456 ],
			[ 'Template and student', 1, 2 ],
		];
		test.each( testData )( '%s', ( name, studentId, templateId ) => {
			expect( getMessage( studentId, templateId ) ).toMatchSnapshot();
		} );
	} );

	describe( 'urls', () => {
		describe( 'getRedirectUrl', () => {
			test( 'Returns the URL', () => {
				expect( getRedirectUrl( 123 ) ).toMatchSnapshot();
			} );
		} );

		describe( 'getScratchUrl', () => {
			const testData = [
				[ 'With student ID', 123 ],
				[ 'Without student ID', null ],
			];
			test.each( testData )( '%s', ( name, studentId ) => {
				expect( getScratchUrl( studentId ) ).toMatchSnapshot();
			} );
		} );
	} );
} );
