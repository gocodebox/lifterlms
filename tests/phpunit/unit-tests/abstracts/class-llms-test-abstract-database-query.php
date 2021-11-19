<?php
/**
 * Tests for the LLMS_Database_Query class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group query
 * @group dbquery
 *
 * @since 4.5.1
 */
class LLMS_Test_Database_Query extends LLMS_UnitTestCase {

	private $_arguments_original;

	/**
	 * Cleanup on tear_down
	 *
	 * @since 4.5.1
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->posts}" );
	}

	/**
	 * Retrieve a stub for the abstract.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Database_Query
	 */
	public function get_stub() {

		return new class() extends LLMS_Database_Query {
			protected function parse_args() {}
			protected function prepare_query() {
				return $this->_prepare_query();
			}
		};

	}

	/**
	 * Test usage of deprecated preprare_query() when the method is defined
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_deprecated_preprare_query_defined() {

		$stub = new class() extends LLMS_Database_Query {
			public function __construct() {}
			protected function parse_args() {}
			protected function preprare_query() {
				global $wpdb;
				return "SELECT * FROM {$wpdb->posts} LIMIT 0, 0";
			}
		};

		$class = get_class( $stub );

		// Deprecation notice thrown to identify that the method should be removed.
		$this->expected_deprecated = array_merge( $this->expected_deprecated, array( "{$class}::preprare_query()" ) );

		global $wpdb;
		$this->assertEquals( "SELECT * FROM {$wpdb->posts} LIMIT 0, 0", LLMS_Unit_Test_Util::call_method( $stub, 'prepare_query' ) );

	}

	/**
	 * Test usage of deprecated preprare_query() when the method is not defined and `prepare_query()` doesn't overload the default method.
	 *
	 * @since [version]
	 *
	 * @expectedIncorrectUsage LLMS_Database_Query::prepare_query
	 *
	 * @return void
	 */
	public function test_deprecated_preprare_query_not_defined() {

		$stub = new class() extends LLMS_Database_Query {
			public function __construct() {}
			protected function parse_args() {}

		};

		LLMS_Unit_Test_Util::call_method( $stub, 'prepare_query' );

	}

	/**
	 * Test usage of deprecated preprare_query() when the method is not defined (if it was removed, for example).
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_deprecated_preprare_query_called_directly_but_not_defined() {

		$stub = new class() extends LLMS_Database_Query {
			public function __construct() {}
			protected function parse_args() {}
			protected function prepare_query() {
				global $wpdb;
				return "SELECT * FROM {$wpdb->posts} LIMIT 0, 0";
			}
		};

		$class = get_class( $stub );

		// Deprecation notice thrown to identify that the method should be removed.
		$this->expected_deprecated = array_merge( $this->expected_deprecated, array( "{$class}::preprare_query()" ) );

		global $wpdb;
		$this->assertEquals( "SELECT * FROM {$wpdb->posts} LIMIT 0, 0", $stub->preprare_query() );

	}

	/**
	 * Test __get() and __set() for deprecated properties.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_set_deprecated_public_properties() {

		$query = $this->get_stub();
		$class = get_class( $query );

		$expected_deprecated = array();

		$props = array(
			'found_results'  => 'get_found_results',
			'max_pages'      => 'get_max_pages',
			'number_results' => 'get_number_results',
			'query_vars'     => null,
			'results'        => 'get_results',
		);
		foreach ( $props as $prop => $func ) {

			$val = "{$prop}_fake";

			$query->$prop = $val;
			$this->assertEquals( $val, $query->$prop );

			// Replacement funciton if it exists.
			if ( ! is_null( $func ) ) {
				$this->assertEquals( $val, $query->$func() );
			}

			$expected_deprecated[] = "Public access to property {$class}::{$prop}";

		}

		// Removed sql prop.
		$query->sql = 'test';
		$this->assertEquals( 'test', $query->sql );
		$this->assertEquals( 'test', $query->get_query() );
		$expected_deprecated[] = "Property {$class}::sql";

		$this->expected_deprecated = array_merge( $this->expected_deprecated, $expected_deprecated );
	}

	/**
	 * Test default_arguments()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_default_arguments() {

		$query = $this->get_stub();

		$defaults = LLMS_Unit_Test_Util::call_method( $query, 'default_arguments' );

		$this->assertEquals( 1, $defaults['page'] );
		$this->assertEquals( 25, $defaults['per_page'] );
		$this->assertEquals( array( 'id' => 'ASC' ), $defaults['sort'] );

	}

	/**
	 * Test that by default the query args has no_found_rows set to false
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_default_args_no_found_rows_false() {
		$query = $this->query();
		$args  = LLMS_Unit_Test_Util::call_method( $query, 'get_default_args' );
		$this->assertEquals( false, $args['no_found_rows'] );
	}

	/**
	 * Test that by default the query found_results and max_pages are not empty (when there are results)
	 *
	 * This is because no_found_rows by default is false.
	 *
	 * @since 4.5.1
	 * @since [version] Use getters instead of direct property access.
	 *
	 * @return void
	 */
	public function test_found_rows_max_pages_not_empty() {
		// Create some posts to have some element in our test table.
		$this->factory->post->create_many(8);

		$query = $this->query();
		$this->assertEquals( 8, $query->get_found_results() );
		$this->assertEquals( 1, $query->get_max_pages() );
	}

	/**
	 * Test when found rows and max pages are not set
	 *
	 * @since 4.5.1
	 * @since [version] Use getters instead of direct property access.
	 *
	 * @return void
	 */
	public function test_found_rows_max_pages_empty() {
		// No results, no found_results no max_pages are set.
		$query = $this->query();
		$this->assertFalse( $query->has_results() );
		$this->assertEquals( 0, $query->get_found_results() );
		$this->assertEquals( 0, $query->get_max_pages() );

		// Create some posts to have some element in our test table.
		$this->factory->post->create_many(8);

		// Query but avoiding calculating found rows.
		$query = $this->query(
			array(
				'no_found_rows' => true,
			)
		);

		// We have results but no found_results no max_pages are set.
		$this->assertTrue( $query->has_results() );
		$this->assertEquals( 0, $query->get_found_results() );
		$this->assertEquals( 0, $query->get_max_pages() );
	}

	/**
	 * Test get_skip()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_skip() {

		$stub = $this->get_stub();

		$tests = array(
			// Page, per page, expected.
			array( 1, 2, 0 ),
			array( 1, 25, 0 ),
			array( 2, 25, 25 ),
			array( 2, 300, 300 ),
			array( 2, 300, 300 ),
			array( 10, 5, 45 ),
			array( 8, 10, 70 ),
		);

		foreach ( $tests as $i => $test ) {

			list( $page, $per_page, $expect ) = $test;

			$stub->set( 'page', $page );
			$stub->set( 'per_page', $per_page );

			$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $stub, 'get_skip' ), $i );

		}

	}

	/**
	 * Test sql_select_columns() method
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_sql_select_columns() {

		$query = $this->query();
		$this->assertEquals( 'SQL_CALC_FOUND_ROWS *', LLMS_Unit_Test_Util::call_method( $query, 'sql_select_columns' ) );
		$this->assertEquals( 'SQL_CALC_FOUND_ROWS column', LLMS_Unit_Test_Util::call_method( $query, 'sql_select_columns', array( 'column' ) ) );

		// Query but avoiding calculating found rows.
		$query = $this->query(
			array(
				'no_found_rows' => true,
			)
		);
		$this->assertEquals( '*', LLMS_Unit_Test_Util::call_method( $query, 'sql_select_columns' ) );
		$this->assertEquals( 'column', LLMS_Unit_Test_Util::call_method( $query, 'sql_select_columns', array( 'column' ) ) );
	}

	/**
	 * Build query
	 *
	 * @since 4.5.1
	 *
	 * @param array $args Optional. Query arguments. Default empty array.
	 *                    When not provided the default arguments will be used.
	 * @return void
	 */
	private function query( $args = array() ) {

		add_filter( 'llms_database_query_prepare_query', array( $this, '_prepare_query' ), 10, 2 );
		if ( ! empty( $args ) ) {
			$this->_arguments_original = $args;
			add_filter( 'llms_database_query_parse_args', array( $this, '_parse_args' ), 10, 2 );
		}

		$query = $this->get_stub();

		add_filter( 'llms_database_query_prepare_query', array( $this, '_prepare_query' ), 10, 2 );
		if ( ! empty( $args ) ) {
			remove_filter( 'llms_database_query_parse_args', array( $this, '_parse_args' ), 10, 2 );
			unset($this->_arguments_original);
		}

		return $query;
	}

	/**
	 * Prepare query to build a testable SQL
	 *
	 * @since 4.5.1
	 *
	 * @return string
	 */
	public function _prepare_query( $sql, $query ) {
		global $wpdb;
		$select  = LLMS_Unit_Test_Util::call_method( $query, 'sql_select_columns' );
		$orderby = LLMS_Unit_Test_Util::call_method( $query, 'sql_orderby' );
		$limit   = LLMS_Unit_Test_Util::call_method( $query, 'sql_limit' );

		return "
			SELECT {$select}
			FROM {$wpdb->posts}
			{$orderby}
			{$limit};
		";
	}

	/**
	 * Parse args
	 *
	 * @since 4.5.1
	 *
	 * @return string
	 */
	public function _parse_args( $args, $query ) {
		return wp_parse_args( $this->_arguments_original, $args );
	}
}
