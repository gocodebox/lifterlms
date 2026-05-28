<?php
/**
 * Test the notifications query.
 *
 * @package LifterLMS/Tests
 *
 * @group notifications
 * @group query
 * @group dbquery
 *
 * @since 7.1.0
 */
class LLMS_Test_Notifications_Query extends LLMS_Unit_Test_Case {

	/**
	 * Test that the notifications query, using default args, sets up a count_query
	 * and does not use SQL_CALC_FOUND_ROWS.
	 *
	 * @since 7.1.0
	 * @since 10.0.0 Updated: SQL_CALC_FOUND_ROWS replaced with count_query.
	 *
	 * @return void
	 */
	public function test_query_with_default_args_sets_count_query() {
		$query = new LLMS_Notifications_Query();
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		$this->assertStringNotContainsString( 'SQL_CALC_FOUND_ROWS', $sql );

		$count_query = LLMS_Unit_Test_Util::get_private_property_value( $query, 'count_query' );
		$this->assertStringStartsWith( 'SELECT COUNT(*)', $count_query );
	}

	/**
	 * Test that the notifications query, passing no_found_rows as true, does not set count_query.
	 *
	 * @since 7.1.0
	 * @since 10.0.0 Updated: SQL_CALC_FOUND_ROWS replaced with count_query.
	 *
	 * @return void
	 */
	public function test_query_correctly_doesnt_set_count_query() {
		$query = new LLMS_Notifications_Query(
			array(
				'no_found_rows' => true,
			)
		);
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		$this->assertStringNotContainsString( 'SQL_CALC_FOUND_ROWS', $sql );

		$count_query = LLMS_Unit_Test_Util::get_private_property_value( $query, 'count_query' );
		$this->assertEmpty( $count_query );
	}

	/**
	 * Test that the notifications query's default args, includes all the available status excluding 'error'.
	 *
	 * @since 7.1.0
	 *
	 * @return void
	 */
	public function test_query_default_args_do_not_contain_error() {
		$query = new LLMS_Notifications_Query();
		$args  = LLMS_Unit_Test_Util::call_method( $query, 'get_default_args' );
		$this->assertNotContains( 'error', $args['statuses'] );
		$this->assertEquals( array( 'new', 'sent', 'read', 'unread', 'deleted', 'failed' ), $args['statuses'] );
	}

	/**
	 * Test found_results and max_pages with real notifications data.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_found_results_with_pagination() {

		$post_id = $this->factory->post->create();

		for ( $i = 0; $i < 8; $i++ ) {
			$n = new LLMS_Notification();
			$n->create(
				array(
					'post_id'    => $post_id,
					'subscriber' => 1,
					'type'       => 'basic',
					'trigger_id' => 1,
					'user_id'    => 1,
				)
			);
		}

		$query = new LLMS_Notifications_Query(
			array(
				'subscriber' => 1,
				'post_id'    => $post_id,
				'per_page'   => 3,
			)
		);

		$this->assertSame( 8, $query->get_found_results() );
		$this->assertSame( 3, $query->get_max_pages() );
		$this->assertSame( 3, $query->get_number_results() );
	}

	/**
	 * Test that no_found_rows skips counting.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_no_found_rows_skips_count() {

		$post_id = $this->factory->post->create();

		$n = new LLMS_Notification();
		$n->create(
			array(
				'post_id'    => $post_id,
				'subscriber' => 1,
				'type'       => 'basic',
				'trigger_id' => 1,
				'user_id'    => 1,
			)
		);

		$query = new LLMS_Notifications_Query(
			array(
				'subscriber'    => 1,
				'post_id'       => $post_id,
				'no_found_rows' => true,
			)
		);

		$this->assertTrue( $query->has_results() );
		$this->assertSame( 0, $query->get_found_results() );
		$this->assertSame( 0, $query->get_max_pages() );
	}

	/**
	 * Test getting notifications, escluding the errored ones (default).
	 *
	 * @since 7.1.0
	 *
	 * @return void
	 */
	public function test_get_notifications_no_errored() {

		$post_id = $this->factory->post->create();

		// Create two notifications.
		$n1    = new LLMS_Notification();
		$nid_1 = $n1->create(
			array(
				'post_id'    => $post_id,
				'subscriber' => 1,
				'type'       => 'basic',
				'trigger_id' => 1,
				'user_id'    => 1,
			)
		);
		$n2    = new LLMS_Notification();
		$nid_2 = $n2->create(
			array(
				'post_id'    => $post_id,
				'subscriber' => 1,
				'type'       => 'email',
				'trigger_id' => 1,
				'user_id'    => 1,
			)
		);
		// Set the last notification status as 'error'.
		$n2->set( 'status', 'error' );

		$n_query = new LLMS_Notifications_Query(
			array(
				'subscriber' => 1,
				'post_id'    => $post_id
			)
		);

		// Expect only the not errored notification retrieved.
		$this->assertEquals( array( $nid_1 ), array_map( 'absint', wp_list_pluck( $n_query->get_notifications(), 'id' ) ) );

	}

	/**
	 * Test getting notifications, including the errored ones.
	 *
	 * @since 7.1.0
	 *
	 * @return void
	 */
	public function test_get_notifications_with_errored() {

		$post_id = $this->factory->post->create();

		// Create two notifications.
		$n1   = new LLMS_Notification();
		$nid_1 = $n1->create(
			array(
				'post_id'    => $post_id,
				'subscriber' => 2,
				'type'       => 'basic',
				'trigger_id' => 1,
				'user_id'    => 1,
			)
		);
		$n2    = new LLMS_Notification();
		$nid_2 = $n2->create(
			array(
				'post_id'    => $post_id,
				'subscriber' => 2,
				'type'       => 'email',
				'trigger_id' => 1,
				'user_id'    => 1,
			)
		);
		// Set the last notification status as 'error'.
		$n2->set( 'status', 'error' );

		$n_query = new LLMS_Notifications_Query(
			array(
				'subscriber' => 2,
				'post_id'    => $post_id,
				'statuses'   => array( 'new', 'sent', 'read', 'unread', 'deleted', 'failed', 'error' ),
			)
		);

		// Expect both the notifications are retrieved.
		$this->assertEqualSets( array( $nid_1, $nid_2 ), array_map( 'absint', wp_list_pluck( $n_query->get_notifications(), 'id' ) ) );

	}

}
