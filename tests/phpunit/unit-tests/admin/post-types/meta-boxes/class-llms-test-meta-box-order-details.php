<?php
/**
 * Tests for LifterLMS Order Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group metaboxes
 * @group order_details
 * @group metaboxes_post_type
 *
 * @since 5.3.0
 */
class LLMS_Test_Meta_Box_Order_Details extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Meta_Box_Order_Details();

	}

	/**
	 * Test save() nonce-related errors
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_save_errs_nonce() {

		// No nonce.
		$this->assertEquals( -1, $this->main->save( 123 ) );

		// Invalid nonce.
		$this->mockPostRequest( $this->add_nonce_to_array( array(), false ) );
		$this->assertEquals( -1, $this->main->save( 123 ) );

	}

	/**
	 * Test save() with an invalid order.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_save_order_err() {

		$post_id = $this->factory->post->create();
		$this->mockPostRequest( $this->add_nonce_to_array( array() ) );

		// Not an order post type.
		$this->assertEquals( 0, $this->main->save( $post_id ) );

		// Non-existent post id.
		$this->assertEquals( 0, $this->main->save( ++$post_id ) );

	}

	/**
	 * Test save() gateway data.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_save_success_payment_gateway_data() {

		$updates = array(
			'payment_gateway'         => 'mock_gateway',
			'gateway_customer_id'     => 'cust_12345',
			'gateway_subscription_id' => 'sub_678',
			'gateway_source_id'       => 'source_1011',
		);

		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$this->mockPostRequest( $this->add_nonce_to_array( $updates ) );

		$this->assertEquals( 1, $this->main->save( $post_id ) );

		$order = llms_get_post( $post_id );
		foreach ( $updates as $key => $val ) {
			$this->assertEquals( $val, $order->get( $key ) );
		}

	}

	/**
	 * Test save() when remaining payment data is updated.
	 *
	 * @since 5.3.0
	 * @since [version] Create the order via `$this->get_mock_order()` which also sets a valid gateway.
	 *
	 * @return void
	 */
	public function test_save_success_remaining_payment_data() {

		$order = $this->get_mock_order();
		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 5 );
		$order->set( 'billing_period', 'day' );

		$this->mockPostRequest( $this->add_nonce_to_array( array(
			'_llms_remaining_payments' => 3,
			'_llms_remaining_note'    => 'Mock note',
		) ) );

		$this->main->save( $order->get( 'id' ) );

		// Data.
		$this->assertEquals( 3, $order->get( 'billing_length' ) );
		$this->assertEquals( 3, $order->get_remaining_payments() );

		// Notes.
		remove_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );
		$notes = $order->get_notes();
		add_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );

		$user_note  = array_pop( $notes );
		$this->assertEquals( 'Mock note', $user_note->comment_content );

		$system_note = array_pop( $notes );
		$this->assertEquals( 'The billing length of the order has been modified from 5 days to 3 days.', $system_note->comment_content );

	}

	/**
	 * Test save() when remaining payment data is updated but the order doesn't support recurring payment modifications.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_unsuccess_remaining_payment_data_when_order_does_not_support_recurring_payment_modifications() {

		// The order's gateway is not set, so the order does not supports modifying recurring payments.
		$order_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$order    = llms_get_post( $order_id );
		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 5 );
		$order->set( 'billing_period', 'day' );


		$this->mockPostRequest( $this->add_nonce_to_array( array(
			'_llms_remaining_payments' => 3,
			'_llms_remaining_note'    => 'Mock note',
		) ) );

		$this->main->save( $order->get( 'id' ) );

		// Data.
		$this->assertEquals( 5, $order->get( 'billing_length' ) );
		$this->assertEquals( 5, $order->get_remaining_payments() );

		// Notes.
		remove_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );
		$notes = $order->get_notes();
		add_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );

		$user_note  = array_pop( $notes );

		$this->assertNotEquals( 'Mock note', $user_note->comment_content );
		$this->assertNotEquals( 'The billing length of the order has been modified from 5 days to 3 days.', $user_note->comment_content );
		$this->assertEmpty( $notes );

	}

	/**
	 * Test save_remaining_payments() when no changes should occur.
	 *
	 * @since 5.3.0
	 * @since [version] Create the order via `$this->get_mock_order()` which also sets a valid gateway.
	 *
	 * @return void
	 */
	public function test_save_remaining_payments_no_changes() {

		$order = $this->get_mock_order();

		// Single order.
		$order->set( 'order_type', 'single' );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		// Recurring without expiration.
		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 0 );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		// Nothing to save: no update submitted.
		$order->set( 'billing_length', 3 );
		$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		// Update submitted with no change.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => $order->get_remaining_payments(),
		) );
		$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		// Can't end a plan via an adjustment.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 0,
		) );
		$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

	}

	/**
	 * Test save_remaining_payments() when changes are made.
	 *
	 * @since 5.3.0
	 * @since [version] Create the order via `$this->get_mock_order()` which also sets a valid gateway.
	 *
	 * @return void
	 */
	public function test_save_remaining_payments_success() {

		$order = $this->get_mock_order();

		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 5 );

		// Has one payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// Reduce to one remaining payment.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 1,
		) );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		$this->assertEquals( 2, $order->get( 'billing_length' ) );
		$this->assertEquals( 1, $order->get_remaining_payments() );

		// Increase to 7 remaining.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 7,
		) );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		$this->assertEquals( 8, $order->get( 'billing_length' ) );
		$this->assertEquals( 7, $order->get_remaining_payments() );

		// Record another payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// Decrease to 3 remaining.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 3,
		) );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );

		$this->assertEquals( 5, $order->get( 'billing_length' ) );
		$this->assertEquals( 3, $order->get_remaining_payments() );

	}

	/**
	 * Test save_remaining_payments() when changes are made but the order doesn't support recurring payment modifications.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_remaining_payments_unsuccess_when_order_does_not_support_recurring_payment_modifications() {

		// The order's gateway is not set, so the order does not supports modifying recurring payments.
		$order_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$order    = llms_get_post( $order_id );

		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 5 );

		// Has one payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// Try to reduce to one remaining payment.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 1,
		) );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );
		// Billing length unchanged.
		$this->assertEquals( 5, $order->get( 'billing_length' ) );
		// Remaining payments are 4 because a payment has been mande.
		$this->assertEquals( 4, $order->get_remaining_payments() );

		// Try to increase to 7 remaining.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 7,
		) );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );
		// Billing length unchanged.
		$this->assertEquals( 5, $order->get( 'billing_length' ) );
		// Remaining payments are still 4 because only one payment has been mande.
		$this->assertEquals( 4, $order->get_remaining_payments() );

		// Record another payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// Decrease to 2 remaining.
		$this->mockPostRequest( array(
			'_llms_remaining_payments' => 2,
		) );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->main, 'save_remaining_payments', array( $order ) ) );
		// Billing length unchanged.
		$this->assertEquals( 5, $order->get( 'billing_length' ) );
		// Remaining payments are 3 because two payments have been mande.
		$this->assertEquals( 3, $order->get_remaining_payments() );

	}

	/**
	 * Test meta box view contains editable recurring remaining payments.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_recurring_remaining_payments_editable() {

		$order = $this->get_mock_order();
		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 5 );

		// Setup the metabox post.
		$_post = $this->main->post;
		$this->main->post = get_post( $order->get( 'id' ) );

		$this->assertTrue( $order->supports_modify_recurring_payments() );

		$metabox_view = $this->get_output( array( $this->main, 'output' ) );

		$finds = array(
			'<span id="llms-remaining-payments-view">5</span>'      => true,
			'<input type="number" id="llms-num-remaining-payments"' => true,
		);

		// The above editable fields are present.
		foreach ( $finds as $find => $bool ) {
			$func = $bool ? 'assertStringContainsString' : 'assertStringNotContainsString';
			$this->{$func}( $find, $metabox_view, "{$func}: {$find}" );
		}

		// Reset the metabox post.
		$this->main->post = $_post;

	}

	/**
	 * Test meta box view doesn't contain editable recurring remaining payments.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_recurring_remaining_payments_not_editable() {

		$order = $this->get_mock_order();
		$order->set( 'order_type', 'recurring' );
		$order->set( 'billing_length', 5 );

		// The order's gateway is set to something which does not supports modifying recurring payments.
		$order->set( 'payment_gateway', 'garbage' );

		// Setup the metabox post.
		$_post = $this->main->post;
		$this->main->post = get_post( $order->get( 'id' ) );

		$this->assertFalse( $order->supports_modify_recurring_payments() );

		$metabox_view = $this->get_output( array( $this->main, 'output' ) );

		$finds = array(
			'<span id="llms-remaining-payments-view">5</span>'      => true,
			'<input type="number" id="llms-num-remaining-payments"' => false,
		);

		// The above editable fields are present.
		foreach ( $finds as $find => $bool ) {
			$func = $bool ? 'assertStringContainsString' : 'assertStringNotContainsString';
			$this->{$func}( $find, $metabox_view, "{$func}: {$find}" );
		}

		// Reset the metabox post.
		$this->main->post = $_post;

	}
}
