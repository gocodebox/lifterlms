<?php
/**
 * Test certificate template functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_template
 * @group functions_template_view_order
 *
 * @since [version]
 */
class LLMS_Test_Functions_Templates_View_Order extends LLMS_UnitTestCase {

	/**
	 * Utility used to get the number of times a set of actions has run.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_action_counts( $func ) {

		switch ( $func ) {

			case 'llms_template_view_order':
				$list = array(
					'lifterlms_before_view_order_table',
					'llms_view_order_information',
					'llms_view_order_actions',
					'llms_view_order_transactions',
					'lifterlms_after_view_order_table',
				);
				break;

			case 'llms_template_view_order_actions':
				$list = array(
					'llms_view_order_before_secondary',
					'llms_view_order_after_secondary',
				);
				break;

			case 'llms_template_view_order_information':
				$list = array(
					'lifterlms_view_order_table_body',
				);
				break;

		}

		$actions = array_fill_keys( $list, 0 );
		foreach ( $actions as $action => &$count ) {
			$count = did_action( $action );
		}

		return $actions;

	}

	/**
	 * Test llms_template_view_order() with invalid input.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order_not_an_order() {

		$post  = $this->factory->post->create_and_get( array( 'post_type' => 'course' ) );
		$tests = array(
			$post,
			llms_get_post( $post ),
			$post->ID,
			false,
			new stdClass(),
		);
		foreach ( $tests as $input ) {
			$this->assertOutputEquals( 'Invalid Order.', 'llms_template_view_order', array( $input ) );
		}

		foreach ( $this->get_action_counts( 'llms_template_view_order' ) as $action => $count ) {
			$this->assertSame( 0, $count, $action );
		}


	}

	/**
	 * Test llms_template_view_order() when accessed by another user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order_wrong_user() {

		$order = $this->get_mock_order();
		$this->assertOutputEquals( 'Invalid Order.', 'llms_template_view_order', array( $order ) );

		foreach ( $this->get_action_counts( 'llms_template_view_order' ) as $action => $count ) {
			$this->assertSame( 0, $count, $action );
		}

	}

	/**
	 * Test llms_template_view_order().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order() {

		$actions = $this->get_action_counts( 'llms_template_view_order' );

		$order = $this->get_mock_order();
		wp_set_current_user( $order->get( 'user_id' ) );

		$res = $this->get_output( 'llms_template_view_order', array( $order ) );

		$this->assertStringContainsString( '<div class="llms-sd-section llms-view-order">', $res );
		$this->assertStringContainsString( sprintf( 'Order #%d', $order->get( 'id' ) ), $res );

		foreach ( $this->get_action_counts( 'llms_template_view_order' ) as $action => $count ) {
			$this->assertEquals( $actions[ $action] + 1, $count, $action );
		}

	}

	/**
	 * Test llms_template_view_order_actions().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order_actions() {

		$actions = $this->get_action_counts( 'llms_template_view_order_actions' );

		$order = $this->get_mock_order();

		$res = $this->get_output( 'llms_template_view_order_actions', array( $order ) );

		$this->assertStringContainsString( '<aside class="order-secondary">', $res );

		foreach ( $this->get_action_counts( 'llms_template_view_order_actions' ) as $action => $count ) {
			$this->assertEquals( $actions[ $action] + 1, $count, $action );
		}

	}

	/**
	 * Test llms_template_view_order_information().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order_information() {

		$actions = $this->get_action_counts( 'llms_template_view_order_information' );

		$order = $this->get_mock_order();

		$res = $this->get_output( 'llms_template_view_order_information', array( $order ) );

		$this->assertStringContainsString( '<section class="order-primary">', $res );

		foreach ( $this->get_action_counts( 'llms_template_view_order_information' ) as $action => $count ) {
			$this->assertEquals( $actions[ $action] + 1, $count, $action );
		}

	}

	/**
	 * Test llms_template_view_order_transactions() for an order with no transactions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order_transactions_no_txns() {

		$order = $this->get_mock_order();
		$this->assertOutputEmpty( 'llms_template_view_order_transactions', array( $order ) );

	}

	/**
	 * Test llms_template_view_order_transactions() for an order with no transactions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_template_view_order_transactions() {

		$order = $this->get_mock_order();
		$order->record_transaction();

		$res = $this->get_output( 'llms_template_view_order_transactions', array( $order ) );

		$this->assertStringContainsString( '<table class="orders-table transactions" id="llms-txns">', $res );

		// No pagination.
		$this->assertStringNotContainsString( '<tfoot>', $res );

		// Test pagination.
		$order->record_transaction();
		$order->record_transaction();

		$filter = function( $count ) {
			return 2;
		};
		add_filter( 'llms_student_dashboard_transactions_per_page', $filter );

		$res = $this->get_output( 'llms_template_view_order_transactions', array( $order ) );

		$this->assertStringContainsString( '<tfoot>', $res );
		$this->assertStringContainsString( '<a class="llms-button-secondary small" href="?txnpage=2#llms-txns">Next</a>', $res );

		// Two transactions in the table.
		$dom = llms_get_dom_document( $res );
		$this->assertEquals( 2, $dom->getElementsByTagName( 'tbody' )[0]->getElementsByTagName( 'tr' )->length );

		// Go to page 2.
		$this->mockGetRequest( array( 'txnpage' => 2 ) );
		$res = $this->get_output( 'llms_template_view_order_transactions', array( $order ) );

		$this->assertStringContainsString( '<tfoot>', $res );
		$this->assertStringContainsString( '<a class="llms-button-secondary small" href="?txnpage=1#llms-txns">Back</a>', $res );

		// One transaction in the table.
		$dom = llms_get_dom_document( $res );
		$this->assertEquals( 1, $dom->getElementsByTagName( 'tbody' )[0]->getElementsByTagName( 'tr' )->length );

		remove_filter( 'llms_student_dashboard_transactions_per_page', $filter );

	}

}
