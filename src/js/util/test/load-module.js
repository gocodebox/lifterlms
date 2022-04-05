import loadModule from '../load-module';

const mockModule = {
	test: 1,
	test2: 'abc',
	test3: [ 4, 5, 6 ],
	test4: () => {},
};

describe( 'loadModule', () => {
	beforeEach( () => {
		global.window = {};
	} );

	it( 'should load the module in the global llms object when llms is yet to be defined', () => {
		loadModule( 'mockModule', mockModule );

		expect( window.llms.mockModule ).toEqual( mockModule );
		expect( window.llms.mockModule ).not.toBe( mockModule );
	} );

	it( 'should load the module in the global llms object when llms is already defined', () => {
		global.window.llms = { some: 'stuff', already: 9320 };

		loadModule( 'mockModule', mockModule );

		expect( window.llms.mockModule ).toEqual( mockModule );
		expect( window.llms.mockModule ).not.toBe( mockModule );

		expect( window.llms.some ).toEqual( 'stuff' );
		expect( window.llms.already ).toEqual( 9320 );
	} );

	it( 'should extend an object that already exists at the specified key when extend is true', () => {
		global.window.llms = { mockModule: { a: 1, b: 2 } };

		loadModule( 'mockModule', mockModule, true );

		expect( window.llms.mockModule ).toMatchSnapshot();
	} );

	it( 'should overwrite an object that already exists at the specified key when extend is false', () => {
		global.window.llms = { mockModule: { a: 1, b: 2 } };

		loadModule( 'mockModule', mockModule );

		expect( window.llms.mockModule ).toEqual( mockModule );
		expect( window.llms.mockModule ).not.toBe( mockModule );
	} );
} );

