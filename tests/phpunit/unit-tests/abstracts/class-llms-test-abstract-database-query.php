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
 * @since [version]
 */
class LLMS_Test_Database_Query extends LLMS_UnitTestCase {

	private $_arguments_original;

	/**
	 * Cleanup on tearDown
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->posts}" );
	}

	/**
	 * Test that by default the query args has no_found_rows set to false
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_found_rows_max_pages_not_empty() {
		// Create some posts to have some element in our test table.
		$this->factory->post->create_many(8);

		$query = $this->query();
		$this->assertEquals( 8, $query->found_results );
		$this->assertEquals( 1, $query->max_pages );
	}

	/**
	 * Test when found rows and max pages are not set
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_found_rows_max_pages_empty() {
		// No results, no found_results no max_pages are set.
		$query = $this->query();
		$this->assertFalse( $query->has_results() );
		$this->assertEquals( 0, $query->found_results );
		$this->assertEquals( 0, $query->max_pages );

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
		$this->assertEquals( 0, $query->found_results );
		$this->assertEquals( 0, $query->max_pages );
	}

	/**
	 * Test sql_select_columns() method
	 *
	 * @since [version]
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
	 * @since [version]
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

		$query = $this->getMockForAbstractClass( 'LLMS_Database_Query');

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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return string
	 */
	public function _parse_args( $args, $query ) {
		return wp_parse_args( $this->_arguments_original, $args );
	}
}
