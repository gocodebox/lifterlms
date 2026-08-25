<?php
/**
 * Test LLMS_Customer_Query.
 *
 * @package LifterLMS/Tests
 *
 * @group customers
 * @group customer_query
 *
 * @since [version]
 */
class LLMS_Test_Customer_Query extends LLMS_UnitTestCase {

	/**
	 * Create a customer with a paid order.
	 *
	 * @since [version]
	 *
	 * @param float $amount    Transaction amount.
	 * @param int   $frequency Plan billing frequency (`0` = one-time).
	 * @return int User ID.
	 */
	protected function create_paid_customer( $amount = 100, $frequency = 0 ) {

		$student = $this->get_mock_student();
		$plan    = $this->get_mock_plan( $amount, $frequency );
		$order   = $this->get_mock_order( $plan, false, $student );
		$order->record_transaction(
			array(
				'amount' => $amount,
				'status' => 'llms-txn-succeeded',
			)
		);

		return $student->get( 'id' );
	}

	/**
	 * Test the query returns customers and sorts by LTV.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_sort_by_ltv() {

		$low  = $this->create_paid_customer( 10 );
		$high = $this->create_paid_customer( 250 );

		$query = new LLMS_Customer_Query(
			array(
				'per_page' => 50,
				'sort'     => array(
					'ltv' => 'DESC',
				),
			)
		);

		$ids = array_map( 'intval', wp_list_pluck( $query->get_customers(), 'user_id' ) );
		$this->assertContains( $low, $ids );
		$this->assertContains( $high, $ids );

		$high_pos = array_search( $high, $ids, true );
		$low_pos  = array_search( $low, $ids, true );
		$this->assertLessThan( $low_pos, $high_pos );
	}

	/**
	 * Test search by email.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_search() {

		$student = $this->get_mock_student();
		$user_id = $student->get( 'id' );
		$email   = 'unique-customer-' . $user_id . '@example.com';
		wp_update_user(
			array(
				'ID'         => $user_id,
				'user_email' => $email,
			)
		);

		$order = $this->get_mock_order( null, false, $student );
		$order->record_transaction(
			array(
				'amount' => 25,
				'status' => 'llms-txn-succeeded',
			)
		);

		$query = new LLMS_Customer_Query(
			array(
				'search'   => $email,
				'per_page' => 10,
			)
		);

		$ids = wp_list_pluck( $query->get_customers(), 'user_id' );
		$this->assertEquals( array( $user_id ), array_map( 'intval', $ids ) );
	}

	/**
	 * Test free_only segment.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_segment_free_only() {

		$paid_id = $this->create_paid_customer( 80 );

		$free_student = $this->get_mock_student();
		$free_id      = $free_student->get( 'id' );
		$this->get_mock_order( $this->get_mock_plan( 0 ), false, $free_student );

		$query = new LLMS_Customer_Query(
			array(
				'segment'  => 'free_only',
				'per_page' => 100,
			)
		);

		$ids = array_map( 'intval', wp_list_pluck( $query->get_customers(), 'user_id' ) );
		$this->assertContains( $free_id, $ids );
		$this->assertNotContains( $paid_id, $ids );
	}

	/**
	 * Test active_subs segment.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_segment_active_subs() {

		$student = $this->get_mock_student();
		$user_id = $student->get( 'id' );
		$order   = $this->get_mock_order( $this->get_mock_plan( 20, 1 ), false, $student );
		$order->set( 'status', 'llms-active' );

		$one_time = $this->create_paid_customer( 15 );

		$query = new LLMS_Customer_Query(
			array(
				'segment'  => 'active_subs',
				'per_page' => 100,
			)
		);

		$ids = array_map( 'intval', wp_list_pluck( $query->get_customers(), 'user_id' ) );
		$this->assertContains( $user_id, $ids );
		$this->assertNotContains( $one_time, $ids );
	}

	/**
	 * Test high_spenders segment uses LTV threshold.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_segment_high_spenders() {

		delete_transient( 'llms_customer_high_spender_threshold' );

		$amounts = array( 10, 20, 30, 40, 50 );
		$ids     = array();
		foreach ( $amounts as $amount ) {
			$ids[] = $this->create_paid_customer( $amount );
		}

		delete_transient( 'llms_customer_high_spender_threshold' );
		$threshold = llms_get_customer_high_spender_threshold();
		$this->assertGreaterThan( 0, $threshold );

		$query = new LLMS_Customer_Query(
			array(
				'segment'  => 'high_spenders',
				'per_page' => 100,
			)
		);

		foreach ( $query->get_customers() as $customer ) {
			$this->assertGreaterThanOrEqual( $threshold, (float) $customer->ltv );
		}
	}

	/**
	 * Test pagination.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_pagination() {

		for ( $i = 0; $i < 3; $i++ ) {
			$this->create_paid_customer( 5 + $i );
		}

		$page1 = new LLMS_Customer_Query(
			array(
				'page'     => 1,
				'per_page' => 1,
			)
		);
		$page2 = new LLMS_Customer_Query(
			array(
				'page'     => 2,
				'per_page' => 1,
			)
		);

		$this->assertEquals( 1, $page1->get_number_results() );
		$this->assertEquals( 1, $page2->get_number_results() );
		$this->assertNotEquals(
			$page1->get_customers()[0]->user_id,
			$page2->get_customers()[0]->user_id
		);
		$this->assertGreaterThanOrEqual( 2, $page1->get_max_pages() );
	}
}
