<?php
/**
 * Run round-trip payment tests using the mock testing gateway.
 *
 * @group payments
 *
 * @since [version]
 */
class LLMS_Test_Payment_Gateway_Integrations extends LLMS_UnitTestCase {

	private $elapsed = 0;

	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();
		add_filter( 'lifterlms_payment_gateways', array( __CLASS__, 'add_mock_gateway' ) );

		// We shouldn't be able to do this but currently we can so whatever.
		LLMS()->payment_gateways()->__construct();

	}

	public static function tearDownAfterClass() {

		remove_filter( 'lifterlms_payment_gateways', array( __CLASS__, 'add_mock_gateway' ) );

		// The gateways class is a bit messed up and loads gateways weird.
		// we need to remove the gateway manually so other tests don't break.
		foreach ( LLMS()->payment_gateways()->payment_gateways as $i => $gateway ) {
			if ( 'mock' === $gateway->id ) {
				unset( LLMS()->payment_gateways()->payment_gateways[ $i ] );
			}
		}
		parent::tearDownAfterClass();

	}

	public function setUp() {

		parent::setUp();
		$this->gateway = LLMS()->payment_gateways()->get_gateway_by_id( 'mock' );
		$this->elapsed = 0;
		$this->time_start = microtime( true );

	}

	private function incrementElapsed() {
		$this->elapsed = microtime( true ) - $this->time_start;
	}

	public function tearDown() {

		$this->incrementElapsed();

		// printf( '%s seconds elapsed', $this->elapsed );

		$this->elapsed = 0;
		$this->time_start = null;

	}

	/**
	 * Register mock gateway
	 *
	 * @since [version]
	 *
	 * @param string[] $gateways Array of gateway class names
	 */
	public static function add_mock_gateway( $gateways ) {
		$gateways[] = 'LLMS_Payment_Gateway_Mock';
		return $gateways;
	}

	public function test_recurring_transaction_lifecycle() {

		// Setup the objects.
		$student = $this->factory->student->create_and_get();

		$plan = $this->get_mock_plan();
		$plan->set( 'period', 'month' );

		$order = new LLMS_Order( 'new' );
		$order   = $order->init( $student, $plan, $this->gateway );

		// Process the order.
		$this->gateway->handle_pending_order( $order, $plan, $student );

		// Reinitialize the order for assertions.
		$order = llms_get_post( $order->get( 'id' ) );

		$order_time = $order->get_date( 'date', 'U' );

		// Order should be active.
		$this->assertEquals( 'llms-active', $order->get( 'status' ) );

		// Check there's only 1 transaction and that it succeeded.
		$txns = $order->get_transactions();
		$this->assertEquals( 1, $txns['count'] );
		$last = array_pop( $txns['transactions'] );
		$this->assertEquals( 'llms-txn-succeeded', $last->get( 'status' ) );

		// Next payment date.
		$next_payment_time = $order->get_date( 'date_next_payment', 'U' );
		$this->assertEquals( strtotime( '+1 month', $order->get_date( 'date', 'U' ) ), $next_payment_time );


		$i = 2;
		while ( $i <= 100 ) {

			// $chaos = HOUR_IN_SECONDS * 2 * -1;
			$chaos = rand( 0, HOUR_IN_SECONDS * 12 ) * ( rand( 0, 1 ) ? -1 : 1 );

			// Time travel.
			llms_mock_current_time( $next_payment_time + $chaos );

			$this->gateway->handle_recurring_transaction( $order );

			$txns = $order->get_transactions();
			$last_txn = array_shift( $txns['transactions'] );
			$last_txn_time = $last_txn->get_date( 'date', 'U' );

			$this->assertEquals( $i, $txns['total'] );
			$this->assertEquals( $last_txn->get_date( 'date', 'U' ), $next_payment_time + $chaos );

			$next_payment_time = $order->get_date( 'date_next_payment', 'U' );


			$expect_base = $order_time === $last_txn_time ? $order_time : $last_txn_time;
			$expect = strtotime( "+1 month", $expect_base );

			$msg = sprintf( '%d: %s', $i, date( 'Y-m-d H:i:s', $next_payment_time ) );
			var_dump( $msg, date( 'Y-m-d H:i:s', $expect ) );
			$this->assertEquals( $expect, $next_payment_time, $msg, 23 * HOUR_IN_SECONDS + 59 );

			++$i;

		}

	}

}
