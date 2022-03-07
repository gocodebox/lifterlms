<?php
/**
 * Tests for the LLMS_Abstract_Query class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group query
 * @group abstract_query
 *
 * @since 6.0.0
 */
class LLMS_Test_Abstract_Query extends LLMS_UnitTestCase {

	/**
	 * Set up the test case.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = $this->get_stub();

	}

	/**
	 * Retrieve a mocked abstract.
	 *
	 * @since 6.0.0
	 *
	 * @return LLMS_Abstract_Query
	 */
	private function get_stub() {

		$stub = $this->getMockForAbstractClass( 'LLMS_Abstract_Query' );

		LLMS_Unit_Test_Util::set_private_property( $stub, 'id', 'mock' );

		return $stub;

	}

	/**
	 * Test count_results(), get_number_results(), get_found_results(), get_max_pages(), and has_results().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_count_results() {

		// No results.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'results', array() );
		LLMS_Unit_Test_Util::call_method( $this->main, 'count_results' );

		$this->assertEquals( 0, $this->main->get_number_results() );
		$this->assertEquals( 0, $this->main->get_found_results() );
		$this->assertEquals( 0, $this->main->get_max_pages() );
		$this->assertFalse( $this->main->has_results() );

		// 52 Results.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'results', array_fill( 0, 10, 'a' ) );
		$this->main->method( 'found_results' )->will( $this->returnValue( 52 ) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'count_results' );

		$this->assertEquals( 10, $this->main->get_number_results() );
		$this->assertEquals( 52, $this->main->get_found_results() );
		$this->assertEquals( 6, $this->main->get_max_pages() );
		$this->assertTrue( $this->main->has_results() );

	}

	/**
	 * Test get() and set()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_and_set() {

		// Default values for unset vals.
		$this->assertEquals( '', $this->main->get( 'fake', '' ) );
		$this->assertEquals( 'default', $this->main->get( 'fake', 'default' ) );

		// Set val.
		$this->main->set( 'fake', 'abc' );
		$this->assertEquals( 'abc', $this->main->get( 'fake', 'default' ) );

		// We can set falsies.
		$this->main->set( 'fake', false );
		$this->assertFalse( $this->main->get( 'fake', 'default' ) );

	}

	/**
	 * Test get_allowed_sort_fields()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_allowed_sort_fields() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'allowed_sort_fields', array( 'id' ) );

		$handler = function( $fields ) {
			$fields[] = 'added';
			return $fields;
		};
		add_filter( 'llms_mock_query_allowed_sort_fields', $handler );

		// Filtered field is added.
		$this->assertEquals( array( 'id', 'added' ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_allowed_sort_fields' ) );

		// Filter is suppressed.
		$this->main->set( 'suppress_filters', true );
		$this->assertEquals( array( 'id' ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_allowed_sort_fields' ) );

		remove_filter( 'llms_mock_query_allowed_sort_fields', $handler );

	}

	/**
	 * Test get_default_args()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_default_args() {

		$defaults = LLMS_Unit_Test_Util::call_method( $this->main, 'default_arguments' );

		$handler = function( $val ) {
			$val['added'] = 1;
			return $val;
		};
		add_filter( 'llms_mock_query_get_default_args', $handler );

		// Filtered field is added.
		$this->assertEquals( array_merge( $defaults, array( 'added' => 1 ) ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_default_args' ) );

		// Filter is suppressed.
		$this->main->set( 'suppress_filters', true );
		$this->assertEquals( $defaults, LLMS_Unit_Test_Util::call_method( $this->main, 'get_default_args' ) );

		remove_filter( 'llms_mock_query_get_default_args', $handler );

	}

	/**
	 * Test get_results()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_results() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'results', array( 1 ) );

		$handler = function( $fields ) {
			return array( 1, 2, 3 );
		};
		add_filter( 'llms_mock_query_get_results', $handler );

		// Filtered field is added.
		$this->assertEquals( array( 1, 2, 3 ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_results' ) );

		// Filter is suppressed.
		$this->main->set( 'suppress_filters', true );
		$this->assertEquals( array( 1 ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_results' ) );

		remove_filter( 'llms_mock_query_get_results', $handler );

	}

	/**
	 * Test is_first_page()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_is_first_page() {

		$this->assertTrue( $this->main->is_first_page() );

		$this->main->set( 'page', 2 );
		$this->assertFalse( $this->main->is_first_page() );

	}

	/**
	 * Test is_last_page()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_is_last_page() {

		$this->assertTrue( $this->main->is_last_page() );

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'number_results', 1 );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'max_pages', 1 );
		$this->assertTrue( $this->main->is_last_page() );

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'number_results', 1 );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'max_pages', 2 );
		$this->assertFalse( $this->main->is_last_page() );

		$this->main->set( 'page', 2 );
		$this->assertTrue( $this->main->is_last_page() );

	}

	/**
	 * Test sanitize_id_array()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_id_array() {

		// Test arrays: $0 = input, $1 = expected output.
		$tests = array(
			// Empty values.
			array( 0, array() ),
			array( false, array() ),
			array( array(), array() ),
			// Numeric input.
			array( 1, array( 1 ) ),
			array( "1", array( 1 ) ),
			array( -1, array( 1 ) ),
			array( "-1", array( 1 ) ),
			array( 20190, array( 20190 ) ),
			array( "923", array( 923 ) ),
			// Arrays of numbers
			array( array( 1, 2, "5" ), array( 1, 2, 5 ) ),
			array( array( "2342", 999009, "1" ), array( 2342, 999009, 1 ) ),
			// Non numeric data.
			array( "abc", array() ),
			array( array( "abc" ), array() ),
			// Mixed data.
			array( array( 0, "abc", 1, "202", "", false, 5 ), array( 1, 202, 5 ) ),
			// Comma strings get weird.
			array( array( '1,2,3' ), array( 1 ) ),
			array( array( 'abc,1' ), array() ),
		);

		foreach ( $tests as $test ) {
			list( $input, $expected ) = $test;
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $this->main, 'sanitize_id_array', array( $input ) ) );
		}

	}

	/**
	 * Test sanitize_sort()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_sort() {

		// No `$allowed_sort` defined for the query.
		$this->assertEquals( array( 'whatever' => 'fake' ), LLMS_Unit_Test_Util::call_method( $this->main, 'sanitize_sort', array( array( 'whatever' => 'fake' ) ) ) );

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'allowed_sort_fields', array( 'whatever', 'fake' ) );

		$tests = array(
			array(
				array( 'whatever' => 'fake' ),
				array(),
			),
			array(
				array( 'whatever' => 'ASC', 'fake' => 'fake' ),
				array( 'whatever' => 'ASC' ),
			),
			array(
				array( 'whatever' => 'ASC', 'fake' => 'DESC' ),
				array( 'whatever' => 'ASC', 'fake' => 'DESC' ),
			),
			array(
				array( 'fake' => 'ASC' ),
				array( 'fake' => 'ASC' ),
			),
			array(
				array( 'id' => 'ASC', 'fake' => 'DESC' ),
				array( 'fake' => 'DESC' ),
			),
		);

		foreach ( $tests as $test ) {
			list( $input, $expected ) = $test;
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $this->main, 'sanitize_sort', array( $input ) ) );
		}
	}

}
