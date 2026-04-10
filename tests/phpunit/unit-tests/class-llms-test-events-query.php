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
	 * Test that the events query, using default args, sets up a count_query
	 * and does not use SQL_CALC_FOUND_ROWS.
	 *
	 * @since 4.7.0
	 * @since 6.0.0 Don't call deprecated `preprare_query()`.
	 * @since [version] Updated: SQL_CALC_FOUND_ROWS replaced with count_query.
	 *
	 * @return void
	 */
	public function test_query_with_default_args_sets_count_query() {
		$query = new LLMS_Events_Query();
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		$this->assertStringNotContainsString( 'SQL_CALC_FOUND_ROWS', $sql );

		$count_query = LLMS_Unit_Test_Util::get_private_property_value( $query, 'count_query' );
		$this->assertStringStartsWith( 'SELECT COUNT(*)', $count_query );
	}

	/**
	 * Test found_results and max_pages with real events data.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_found_results_with_pagination() {

		$user_id = $this->factory->user->create();

		for ( $i = 0; $i < 7; $i++ ) {
			$event = new LLMS_Event();
			$event->setUp(
				array(
					'actor_id'     => $user_id,
					'object_type'  => 'post',
					'object_id'    => 1,
					'event_type'   => 'page',
					'event_action' => 'load',
				)
			);
			$event->save();
		}

		$query = new LLMS_Events_Query(
			array(
				'actor'    => $user_id,
				'per_page' => 3,
			)
		);

		$this->assertSame( 7, $query->get_found_results() );
		$this->assertSame( 3, $query->get_max_pages() );
		$this->assertSame( 3, $query->get_number_results() );
	}

	/**
	 * Test that no_found_rows skips counting with real data.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_no_found_rows_skips_count() {

		$user_id = $this->factory->user->create();

		$event = new LLMS_Event();
		$event->setUp(
			array(
				'actor_id'     => $user_id,
				'object_type'  => 'post',
				'object_id'    => 1,
				'event_type'   => 'page',
				'event_action' => 'load',
			)
		);
		$event->save();

		$query = new LLMS_Events_Query(
			array(
				'actor'         => $user_id,
				'no_found_rows' => true,
			)
		);

		$this->assertTrue( $query->has_results() );
		$this->assertSame( 0, $query->get_found_results() );
		$this->assertSame( 0, $query->get_max_pages() );
	}

	/**
	 * Test that the events query, passing no_found_rows as true, does not set count_query.
	 *
	 * @since 4.7.0
	 * @since 6.0.0 Don't call deprecated `preprare_query()`.
	 * @since [version] Updated: SQL_CALC_FOUND_ROWS replaced with count_query.
	 *
	 * @return void
	 */
	public function test_query_correctly_doesnt_set_count_query() {
		$query = new LLMS_Events_Query(
			array(
				'no_found_rows' => true,
			)
		);
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		$this->assertStringNotContainsString( 'SQL_CALC_FOUND_ROWS', $sql );

		$count_query = LLMS_Unit_Test_Util::get_private_property_value( $query, 'count_query' );
		$this->assertEmpty( $count_query );
	}

}
