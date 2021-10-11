<?php
/**
 * Run round-trip payment tests using the mock testing gateway.
 *
 * @group payments
 *
 * @since 3.37.6
 * @since 3.37.12 Added additional assertion message information to assist in debug chaos-related failures.
 * @since 3.37.14 Reduce number of tests run for monthly and yearly chaotic simulations.
 * @since 4.3.1 Increased delta for `test_recurring_lifecycle_for_month_plan_with_chaos_and_frequency()` and `test_recurring_lifecycle_for_month_plan_with_chaos()`.
 * @since 5.3.1 Declare the `$gateway` property.
 */
class LLMS_Test_Payment_Gateway_Integrations extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Payment_Gateway|false
	 */
	protected $gateway;

	/**
	 * Before the class runs, register the mock gateway.
	 *
	 * @since 3.37.6
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()` and renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		add_filter( 'lifterlms_payment_gateways', array( __CLASS__, 'add_mock_gateway' ) );

		// We shouldn't be able to do this but currently we can so whatever.
		llms()->payment_gateways()->__construct();

	}

	/**
	 * After the class runs, remove the mock gateway.
	 *
	 * @since 3.37.6
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()` and renamed from `tearDownAfterClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {

		remove_filter( 'lifterlms_payment_gateways', array( __CLASS__, 'add_mock_gateway' ) );

		// The gateways class is a bit messed up and loads gateways weird.
		// we need to remove the gateway manually so other tests don't break.
		foreach ( llms()->payment_gateways()->payment_gateways as $i => $gateway ) {
			if ( 'mock' === $gateway->id ) {
				unset( llms()->payment_gateways()->payment_gateways[ $i ] );
			}
		}
		parent::tear_down_after_class();

	}

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.6
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->gateway = llms()->payment_gateways()->get_gateway_by_id( 'mock' );
	}

	/**
	 * Register mock gateway
	 *
	 * @since 3.37.6
	 *
	 * @param string[] $gateways Array of gateway class names
	 *
	 * @return string[]
	 */
	public static function add_mock_gateway( $gateways ) {
		$gateways[] = 'LLMS_Payment_Gateway_Mock';
		return $gateways;
	}

	/**
	 * Sets up a mock order for use with tests.
	 *
	 * @since 3.37.6
	 *
	 * @param string $period Access plan period value.
	 * @param int $frequency Access plan frequency value.
	 * @return LLMS_Order
	 */
	private function setup_order( $period, $frequency = 1 ) {

		// Setup the objects.
		$student = $this->factory->student->create_and_get();

		$plan = $this->get_mock_plan();
		$plan->set( 'period', $period );
		$plan->set( 'frequency', $frequency );

		$order = new LLMS_Order( 'new' );
		$order   = $order->init( $student, $plan, $this->gateway );

		// Process the order.
		$this->gateway->handle_pending_order( $order, $plan, $student );

		return $order;

	}

	/**
	 * Run some tests on the initial setup of the order and the first payment.
	 *
	 * @since 3.37.6
	 * @since 5.3.3 Use assertEqualsWithDelta() in favor of 4th parameter supplied to assertEquals().
	 *
	 * @param LLMS_Order $order The order.
	 * @return void
	 */
	private function do_order_setup_tests( $order ) {

		$plan      = llms_get_post( $order->get( 'plan_id' ) );
		$period    = $plan->get( 'period' );
		$frequency = $plan->get( 'frequency' );

		// Order should be active.
		$this->assertEquals( 'llms-active', $order->get( 'status' ) );

		// Check there's only 1 transaction.
		$txns = $order->get_transactions();
		$this->assertEquals( 1, $txns['count'] );

		// Transaction succeeded.
		$last = array_pop( $txns['transactions'] );
		$this->assertEquals( 'llms-txn-succeeded', $last->get( 'status' ) );

		// Next payment date.
		$next_payment_time = $order->get_date( 'date_next_payment', 'U' );
		$this->assertEqualsWithDelta( strtotime( "+{$frequency} {$period}", $order->get_date( 'date', 'U' ) ), $next_payment_time, 5, $period ); // 5 seconds tolerance.

	}

	/**
	 * Runs N charges on a recurring order with optionally included "chaos".
	 *
	 * "Chaos" will run the recurring payment randomly between $chaos_hours before and $chaos_hours after the scheduled payment time.
	 *
	 * @since 3.37.6
	 * @since 3.37.12 Added additional assertion message information to assist in debug chaos-related failures.
	 * @since 5.3.1 If the chaos >= 0, calculate the expected next payment time based on the scheduled payment time.
	 * @since 5.3.3 Use assertEqualsWithDelta() in favor of 4th parameter supplied to assertEquals().
	 *
	 * @param LLMS_Order $order Initialized order to run charges against.
	 * @param int $num Number of charges to run.
	 * @param int $chaos_hours Number of hours of chaos to introduce.
	 * @param int $delta_hours Number of hours of tolerance to allow as the "delta" for date comparison assertions.
	 * @return void
	 */
	private function do_n_charges_for_order( $order, $num, $chaos_hours = 0, $delta_hours = 0 ) {

		$plan      = llms_get_post( $order->get( 'plan_id' ) );
		$period    = $plan->get( 'period' );
		$frequency = $plan->get( 'frequency' );

		$start   = microtime( true );
		$limit   = 2.5;
		$elapsed = 0;
		$i       = 2;
		while ( $i <= $num + 1 && $elapsed <= $limit ) {

			$scheduled_payment_time = (int) $order->get_date( 'date_next_payment', 'U' );

			// Run the recurring payment randomly between 12 hours before and 12 hours after the scheduled payment time.
			$chaos = rand( 0, HOUR_IN_SECONDS * $chaos_hours ) * ( rand( 0, 1 ) ? -1 : 1 );

			// Time travel.
			llms_tests_mock_current_time( $scheduled_payment_time + $chaos );

			// Run the transaction.
			$this->gateway->handle_recurring_transaction( $order );

			$txns = $order->get_transactions();
			$last_txn = array_shift( $txns['transactions'] );
			$last_txn_time = $last_txn->get_date( 'date', 'U' );

			// Should have transactions equal to the current loop interval.
			$this->assertEquals( $i, $txns['total'] );

			// Last transaction date should equal the chaos time, this way we can be sure it was the payment we thought it was.
			$this->assertEquals( $last_txn->get_date( 'date', 'U' ), $scheduled_payment_time + $chaos );

			$next_payment_time = $order->get_date( 'date_next_payment', 'U' );

			if ( $chaos < 0 ) {
				$expect = strtotime( "+{$frequency} {$period}", $last_txn_time );
			} else {
				$expect = strtotime( "+{$frequency} {$period}", $scheduled_payment_time );
			}
			$msg = sprintf(
				'%1$s Payment #%2$d: Got %3$s and expected %4$s ( $chaos_hours = %5$d | $chaos = %6$s )',
				ucfirst( $period ),
				$i,
				date( 'Y-m-d H:i:s', $next_payment_time ),
				date( 'Y-m-d H:i:s', $expect ),
				$chaos_hours,
				$chaos
			);

			// Ensure that the calculated next payment time is 1 period +/- 23:59:59 from the previous transaction.
			$this->assertEqualsWithDelta( $expect, $next_payment_time, $delta_hours ? $delta_hours * HOUR_IN_SECONDS - 1 : 0, $msg );

			++$i;
			$elapsed = microtime( true ) - $start;

		}

		// if ( $elapsed > $limit ) {

		// 	$trace = debug_backtrace();
		// 	$caller = $trace[1];

		// 	$this->markTestSkipped( "{$caller['class']}::{$caller['function']}: {$i}" );

		// }

	}

	/**
	 * Run tests for a for a daily plan
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_day_plan() {

		$order = $this->setup_order( 'day' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99 );

	}

	/**
	 * Run tests for a for a daily plan with irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_day_plan_with_frequency() {

		$order = $this->setup_order( 'day', 3 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 10 );

	}

	/**
	 * Run tests for a for a daily plan_with_chaos
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_day_plan_with_chaos() {

		$order = $this->setup_order( 'day' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99, 6, 12 );

	}

	/**
	 * Run tests for a for a daily plan with chaos and irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_day_plan_with_chaos_and_frequency() {

		$order = $this->setup_order( 'day', 3 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 25, 6, 12 );

	}

	/**
	 * Run tests for a for a weekly plan
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_week_plan() {

		$order = $this->setup_order( 'week' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99 );

	}

	/**
	 * Run tests for a for a weekly plan with irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_week_plan_with_frequency() {

		$order = $this->setup_order( 'week', 8 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 10 );

	}

	/**
	 * Run tests for a for a weekly plan_with_chaos
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_week_plan_with_chaos() {

		$order = $this->setup_order( 'week' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99, 12, 24 );

	}

	/**
	 * Run tests for a for a weekly plan with chaos and irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_week_plan_with_chaos_and_frequency() {

		$order = $this->setup_order( 'week', 2 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99, 12, 24 );

	}

	/**
	 * Run tests for a for a monthly plan
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_month_plan() {

		$order = $this->setup_order( 'month' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99 );

	}

	/**
	 * Run tests for a for a monthly plan with irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_month_plan_with_frequency() {

		$order = $this->setup_order( 'month', 2 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99 );

	}

	/**
	 * Run tests for a for a monthly plan_with_chaos
	 *
	 * @since 3.37.6
	 * @since 3.37.14 Reduce number of tests run.
	 * @since 4.3.1 Increased delta from 24 to 48 hours.
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_month_plan_with_chaos() {

		$order = $this->setup_order( 'month' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 50, 12, 48 );

	}

	/**
	 * Run tests for a for a monthly plan with chaos and irregular frequency
	 *
	 * @since 3.37.6
	 * @since 4.3.1 Increased delta from 24 to 48 hours.
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_month_plan_with_chaos_and_frequency() {

		$order = $this->setup_order( 'month', 3 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 31, 12, 48 );

	}

	/**
	 * Run tests for a for a yearly plan
	 *
	 * @since 3.37.6
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_year_plan() {

		$order = $this->setup_order( 'year' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 99 );

	}

	/**
	 * Run tests for a for a yearly plan with irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_year_plan_with_frequency() {

		$order = $this->setup_order( 'year', 5 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 20 );

	}

	/**
	 * Run tests for a for a yearly plan_with_chaos
	 *
	 * @since 3.37.6
	 * @since 3.37.14 Reduce number of tests run.
	 *
	 * @medium
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_year_plan_with_chaos() {

		$order = $this->setup_order( 'year' );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 50, 12, 24 );

	}

	/**
	 * Run tests for a for a yearly plan with chaos and irregular frequency
	 *
	 * @since 3.37.6
	 *
	 * @return void
	 */
	public function test_recurring_lifecycle_for_year_plan_with_chaos_and_frequency() {

		$order = $this->setup_order( 'year', 2 );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		// Test setup data.
		$this->do_order_setup_tests( $order );

		// Run recurring charges for the order.
		$this->do_n_charges_for_order( $order, 9, 12, 24 );

	}

}
