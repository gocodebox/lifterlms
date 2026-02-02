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
 * @since 4.7.0
 */
class LLMS_Test_Events_Query extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case
	 *
	 * @since 3.36.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
	}

	/**
	 * Teardown the test case
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events" );
	}


	/**
	 * Test that the events query, using default args, builds a valid SELECT query.
	 *
	 * @since 4.7.0
	 * @since 6.0.0 Don't call deprecated `preprare_query()`.
	 * @since [version] Updated: SQL_CALC_FOUND_ROWS no longer used (replaced with COUNT subquery approach).
	 *
	 * @return void
	 */
	public function test_query_with_default_args_builds_valid_query() {
		$query = new LLMS_Events_Query();
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		// Query should start with SELECT (without SQL_CALC_FOUND_ROWS which is deprecated).
		$this->assertSame( 0, strpos( $sql, 'SELECT' ) );
		$this->assertSame( false, strpos( $sql, 'SQL_CALC_FOUND_ROWS' ) );
	}

	/**
	 * Test that the events query, passing no_found_rows as true doesn't calculate found rows.
	 *
	 * @since 4.7.0
	 * @since 6.0.0 Don't call deprecated `preprare_query()`.
	 * @since [version] Updated: SQL_CALC_FOUND_ROWS no longer used (test still verifies query structure).
	 *
	 * @return void
	 */
	public function test_query_correctly_doesnt_calculate_found_rows() {
		$query = new LLMS_Events_Query(
			array(
				'no_found_rows' => true,
			)
		);
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		// Query should not contain SQL_CALC_FOUND_ROWS (which is deprecated and removed).
		$this->assertSame( false, strpos( $sql, 'SQL_CALC_FOUND_ROWS' ) );
	}

}
