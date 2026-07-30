<?php
/**
 * Test customer functions.
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group customers
 * @group functions
 * @group functions_customer
 *
 * @since [version]
 */
class LLMS_Test_Functions_Customer extends LLMS_UnitTestCase {

	/**
	 * Test llms_is_customer() and metrics for a paid customer.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_metrics_with_transactions_and_refund() {

		$student = $this->get_mock_student();
		$user_id = $student->get( 'id' );

		$this->assertFalse( llms_is_customer( $user_id ) );

		$plan  = $this->get_mock_plan( 100, 0 );
		$order = $this->get_mock_order( $plan, false, $student );
		$order->record_transaction(
			array(
				'amount' => 100,
				'status' => 'llms-txn-succeeded',
			)
		);

		$txn = $order->record_transaction(
			array(
				'amount' => 50,
				'status' => 'llms-txn-refunded',
			)
		);
		$txn->set( 'refund_amount', 50 );

		llms_delete_customer_metrics_cache( $user_id );

		$this->assertTrue( llms_is_customer( $user_id ) );

		$metrics = llms_get_customer_metrics( $user_id );

		$this->assertEquals( 1, $metrics['order_count'] );
		$this->assertEquals( 150.0, $metrics['gross'] );
		$this->assertEquals( 50.0, $metrics['refunded'] );
		$this->assertEquals( 100.0, $metrics['ltv'] );
		$this->assertEquals( 100.0, $metrics['aov'] );
		$this->assertNotEmpty( $metrics['first_order_date'] );
		$this->assertNotEmpty( $metrics['last_order_date'] );
		$this->assertSame( get_lifterlms_currency(), $metrics['currency'] );
	}

	/**
	 * Test free-only customers have zero LTV.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_metrics_free_only() {

		$student = $this->get_mock_student();
		$user_id = $student->get( 'id' );
		$plan    = $this->get_mock_plan( 0 );
		$order   = $this->get_mock_order( $plan, false, $student );

		llms_delete_customer_metrics_cache( $user_id );

		$metrics = llms_get_customer_metrics( $user_id );

		$this->assertTrue( llms_is_customer( $user_id ) );
		$this->assertEquals( 1, $metrics['order_count'] );
		$this->assertEquals( 0.0, $metrics['ltv'] );
		$this->assertEquals( 0.0, $metrics['aov'] );
		$this->assertEquals( 0, $metrics['active_recurring_count'] );
	}

	/**
	 * Test active recurring count.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_metrics_active_recurring() {

		$student = $this->get_mock_student();
		$user_id = $student->get( 'id' );
		$plan    = $this->get_mock_plan( 25, 1 );
		$order   = $this->get_mock_order( $plan, false, $student );
		$order->set( 'status', 'llms-active' );

		llms_delete_customer_metrics_cache( $user_id );

		$metrics = llms_get_customer_metrics( $user_id );
		$this->assertEquals( 1, $metrics['active_recurring_count'] );
	}

	/**
	 * Test admin URL helper.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_customers_admin_url() {

		$list = llms_get_customers_admin_url();
		$this->assertStringContainsString( 'page=llms-customers', $list );
		$this->assertStringContainsString( 'post_type=llms_order', $list );

		$single = llms_get_customers_admin_url( 42 );
		$this->assertStringContainsString( 'customer_id=42', $single );
	}

	/**
	 * Test segment list filterability.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_customer_segments() {

		$segments = llms_get_customer_segments();
		$this->assertArrayHasKey( 'all', $segments );
		$this->assertArrayHasKey( 'high_spenders', $segments );
		$this->assertArrayHasKey( 'active_subs', $segments );
		$this->assertArrayHasKey( 'free_only', $segments );
		$this->assertArrayHasKey( 'at_risk', $segments );
	}
}
