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
 * @since [version]
 */
class LLMS_Test_Notifications_Query extends LLMS_Unit_Test_Case {

	/**
	 * Test that the notifications query, using default args, calculates found rows.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_with_default_args_calculates_found_rows() {
		$query = new LLMS_Notifications_Query();
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		$this->assertSame( 0, strpos( $sql, 'SELECT SQL_CALC_FOUND_ROWS' ) );
	}

	/**
	 * Test that the notifications query, passing no_found_rows as true doesn't calculate found rows.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_correctly_doesnt_calculate_found_rows() {
		$query = new LLMS_Notifications_Query(
			array(
				'no_found_rows' => true,
			)
		);
		$sql = LLMS_Unit_Test_Util::call_method( $query, 'prepare_query' );
		$this->assertSame( false, strpos( $sql, 'SQL_CALC_FOUND_ROWS' ) );
	}

	/**
	 * Test that the notifications query's default args, includes all the available status excluding 'error'.
	 *
	 * @since [version]
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
	 * Test getting notifications, escluding the errored ones (default).
	 *
	 * @since [version]
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
	 * @since [version]
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
