<?php
/**
 * Tests for LLMS_Student_Dashboard class
 *
 * @package LifterLMS/Tests
 *
 * @group student_dashboard
 *
 * @since [version]
 */
class LLMS_Test_Student_Dashboard extends LLMS_UnitTestCase {

	/**
	 * Create an order for a student, optionally recurring.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Student $student   The student to create the order for.
	 * @param bool         $recurring Whether the order should be recurring (a subscription).
	 * @return LLMS_Order
	 */
	private function create_order_for_student( $student, $recurring = true ) {
		$plan = $this->get_mock_plan( 25.99, $recurring ? 1 : 0 );
		return $this->get_mock_order( $plan, false, $student );
	}

	/**
	 * Test LLMS_Student::get_subscriptions() returns only recurring orders.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_subscriptions_returns_only_recurring_orders() {

		$student = $this->get_mock_student();

		$recurring = $this->create_order_for_student( $student, true );
		$single    = $this->create_order_for_student( $student, false );

		$subscriptions = $student->get_subscriptions();

		$this->assertEquals( 1, $subscriptions['count'] );
		$this->assertArrayHasKey( $recurring->get( 'id' ), $subscriptions['orders'] );
		$this->assertArrayNotHasKey( $single->get( 'id' ), $subscriptions['orders'] );
	}

	/**
	 * Test that the "My Subscriptions" nav item is hidden when the student has no subscriptions.
	 *
	 * A student with only a one-time (non-recurring) order should not see the tab.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_subscriptions_nav_hidden_without_subscription() {

		$student = $this->get_mock_student();
		$this->create_order_for_student( $student, false );

		wp_set_current_user( $student->get( 'id' ) );

		$tabs = LLMS_Student_Dashboard::get_tabs_for_nav();

		$this->assertArrayNotHasKey( 'subscriptions', $tabs );
	}

	/**
	 * Test that the "My Subscriptions" nav item is visible when the student has a subscription.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_subscriptions_nav_visible_with_subscription() {

		$student = $this->get_mock_student();
		$this->create_order_for_student( $student, true );

		wp_set_current_user( $student->get( 'id' ) );

		$tabs = LLMS_Student_Dashboard::get_tabs_for_nav();

		$this->assertArrayHasKey( 'subscriptions', $tabs );
	}

	/**
	 * Test that the "My Subscriptions" endpoint is always registered (reachable by direct URL).
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_subscriptions_endpoint_is_registered() {

		$dashboard = new LLMS_Student_Dashboard();
		$endpoints = $dashboard->get_endpoints();

		$this->assertArrayHasKey( 'subscriptions', $endpoints );
		$this->assertEquals( 'subscriptions', $endpoints['subscriptions'] );
	}

}
