import registerWordCountModule from '../index';
import wordCountModule from '../module';

describe( 'registerWordCountModule()', () => {

	test( 'Quill is not available', () => {

		window.Quill = undefined;
		expect( registerWordCountModule() ).toStrictEqual( false );

	} );

	test( 'Quill is available', () => {

		window.Quill = {
			register: jest.fn(),
		};
		expect( registerWordCountModule() ).toStrictEqual( true );
		expect( window.Quill.register ).toHaveBeenCalledWith( 'modules/wordcount', wordCountModule );

	} );

} );
