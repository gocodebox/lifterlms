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

	/**
	 * Test get_transactions_list() pages transactions and transaction-less orders by date.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_transactions_list_paginates_by_date() {

		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );

		$paid    = $this->create_order_for_student( $student, true );
		$txn_ids = array();
		foreach ( array( '2020-01-01', '2020-02-01', '2020-03-01' ) as $date ) {
			$txn = $paid->record_transaction(
				array(
					'amount'       => 25.99,
					'status'       => 'llms-txn-succeeded',
					'payment_type' => 'recurring',
				)
			);
			wp_update_post(
				array(
					'ID'        => $txn->get( 'id' ),
					'post_date' => $date . ' 00:00:00',
				)
			);
			$txn_ids[ $date ] = $txn->get( 'id' );
		}

		$free = $this->create_order_for_student( $student, false );
		wp_update_post(
			array(
				'ID'        => $free->get( 'id' ),
				'post_date' => '2020-04-01 00:00:00',
			)
		);

		$get_ids = function ( $list ) {
			return array_map(
				function ( $row ) {
					return $row->get( 'id' );
				},
				$list['rows']
			);
		};

		$page_one = LLMS_Unit_Test_Util::call_method( 'LLMS_Student_Dashboard', 'get_transactions_list', array( 1, 2 ) );
		$page_two = LLMS_Unit_Test_Util::call_method( 'LLMS_Student_Dashboard', 'get_transactions_list', array( 2, 2 ) );

		$this->assertSame( 2, $page_one['pages'] );
		$this->assertSame( array( $free->get( 'id' ), $txn_ids['2020-03-01'] ), $get_ids( $page_one ) );
		$this->assertSame( array( $txn_ids['2020-02-01'], $txn_ids['2020-01-01'] ), $get_ids( $page_two ) );
	}

	/**
	 * Test that My Grades redirects to the course list only when the requested course doesn't exist.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_redirect_invalid_my_grades_course() {

		LLMS_Install::create_pages();

		$dashboard  = new LLMS_Student_Dashboard();
		$grades_url = llms_get_endpoint_url( 'my-grades', '', llms_get_page_url( 'myaccount' ) );
		$course     = $this->factory->course->create_and_get( array( 'sections' => 0 ) );

		$redirects_to = function ( $value ) use ( $dashboard, $grades_url ) {
			$this->go_to( add_query_arg( 'my-grades', $value, $grades_url ) );
			try {
				$dashboard->maybe_redirect_invalid_my_grades_course();
			} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {
				return $exception->getMessage();
			}
			return false;
		};

		// A course ID, unknown slug, or unpublished course redirects to the course list.
		$this->assertSame( $grades_url . ' [302] YES', $redirects_to( $course->get( 'id' ) ) );
		$this->assertSame( $grades_url . ' [302] YES', $redirects_to( 'not-a-course' ) );

		// A valid course, the course list, and its pagination don't redirect.
		$this->assertFalse( $redirects_to( $course->get( 'name' ) ) );
		$this->assertFalse( $redirects_to( '' ) );
		$this->assertFalse( $redirects_to( 'page/2' ) );

		wp_update_post(
			array(
				'ID'          => $course->get( 'id' ),
				'post_status' => 'draft',
			)
		);
		$this->assertSame( $grades_url . ' [302] YES', $redirects_to( $course->get( 'name' ) ) );
	}

}
