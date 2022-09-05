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

}
