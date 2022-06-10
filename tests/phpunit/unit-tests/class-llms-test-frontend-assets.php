<?php
/**
 * LLMS Frontend Assets Tests
 *
 * @package LifterLMS/Tests
 *
 * @group assets
 * @group frontend_assets
 *
 * @since 4.4.0
 * @since 6.0.0 Removed testing of removed items.
 *              - `LLMS_Frontend_Assets::enqueue_inline_script()` method
 *              - `LLMS_Frontend_Assets::is_inline_enqueued()` method
 */
class LLMS_Test_Frontend_Assets extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->clear_inline_scripts();

	}

	/**
	 * Retrieves a list of enqueued inline scripts from the LLMS_Assets instance.
	 *
	 * @since 5.6.0
	 *
	 * @return array
	 */
	private function get_inline_scripts() {
		return LLMS_Unit_Test_Util::get_private_property_value( llms()->assets, 'inline' );
	}

	/**
	 * Clears enqueued inline scripts.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function clear_inline_scripts() {
		LLMS_Unit_Test_Util::set_private_property( llms()->assets, 'inline', array() );		
	}

	/**
	 * Test enqueue_content_protection().
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function test_enqueue_content_protection() {

		// Content protection off & user is logged out: no scripts loaded.
		update_option( 'lifterlms_content_protection', 'no' );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertEquals( array(), $this->get_inline_scripts() );

		// Content protection is on and user is logged out: scripts are loaded.
		update_option( 'lifterlms_content_protection', 'yes' );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertArrayHasKey( 'llms-integrity', $this->get_inline_scripts() );

		$this->clear_inline_scripts();

		// Admin can bypass restrictions, script is not loaded.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertEquals( array(), $this->get_inline_scripts() );

		$this->clear_inline_scripts();

		// Student can't copy content.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertArrayHasKey( 'llms-integrity', $this->get_inline_scripts() );

		$this->clear_inline_scripts();

	}

	/**
	 * Test enqueue_inline_scripts().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_enqueue_inline_scripts() {

		// Any page.
		LLMS_Unit_Test_Util::call_method( 'LLMS_Frontend_Assets', 'enqueue_inline_scripts' );

		$expected = array(
			'llms-obj'               => 5.0,
			'llms-ajaxurl'           => 10.0,
			'llms-ajax-nonce'        => 10.01,
			'llms-tracking-settings' => 10.02,
			'llms-LLMS-obj'          => 10.03,
			'llms-l10n'              => 10.04,
		);
		$this->assertEquals( $expected, wp_list_pluck( $this->get_inline_scripts(), 'priority' ) );

		// On checkout page.
		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'checkout' ) );

		LLMS_Unit_Test_Util::call_method( 'LLMS_Frontend_Assets', 'enqueue_inline_scripts' );

		$expected['llms-checkout-urls'] = 10.05;
		$this->assertEquals( $expected, wp_list_pluck( $this->get_inline_scripts(), 'priority' ) );

	}

	/**
	 * Test get_checkout_urls().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_checkout_urls() {

		// Regular page.
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( 'LLMS_Frontend_Assets', 'get_checkout_urls' ) );

		// Checkout.
		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'checkout' ) );
		$this->assertEquals( 
			array( 'createPendingOrder', 'confirmPendingOrder' ), 
			array_keys( LLMS_Unit_Test_Util::call_method( 'LLMS_Frontend_Assets', 'get_checkout_urls' ) )
		);

		// Dashboard.
		$this->go_to( llms_get_endpoint_url( 'orders', 123, llms_get_page_url( 'myaccount' ) ) );
		$this->assertEquals( 
			array( 'switchPaymentSource' ), 
			array_keys( LLMS_Unit_Test_Util::call_method( 'LLMS_Frontend_Assets', 'get_checkout_urls' ) )
		);
	}

}
