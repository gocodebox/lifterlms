<?php
/**
 * Test events query
 *
 * @package LifterLMS/Tests
 *
 * @group events
 * @group query
 * @group dbquery
 *
 * @since [version]
 */
class LLMS_Test_Events_Query extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Teardown the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events" );
	}


	/**
	 * Test that the events query, using default args, calculates found rows
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_with_default_args_calculates_found_rows() {
		$query = new LLMS_Events_Query();
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'preprare_query' );
		$this->assertSame( 0, strpos( $sql, 'SELECT SQL_CALC_FOUND_ROWS' ) );
	}

	/**
	 * Test that the events query, passing no_found_rows as true doesn't calculate found rows
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_correctly_doesnt_calculate_found_rows() {
		$query = new LLMS_Events_Query(
			array(
				'no_found_rows' => true,
			)
		);
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'preprare_query' );
		$this->assertSame( false, strpos( $sql, 'SQL_CALC_FOUND_ROWS' ) );
	}

}
