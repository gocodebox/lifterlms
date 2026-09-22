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
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->clear_inline_scripts();
		$this->dequeue_test_assets();

	}

	/**
	 * Dequeue assets that tests may add to the WP scripts/styles queue so each test starts clean.
	 *
	 * @return void
	 */
	private function dequeue_test_assets() {

		$scripts = array( 'llms', 'llms-ajax', 'llms-form-checkout', 'llms-notifications', 'llms-quiz', 'llms-favorites', 'llms-iziModal', 'llms-select2', 'llms-jquery-matchheight', 'webui-popover', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'jquery-ui-slider' );
		$styles  = array( 'lifterlms-styles', 'webui-popover', 'llms-select2-styles', 'llms-iziModal', 'certificates' );

		foreach ( $scripts as $handle ) {
			wp_dequeue_script( $handle );
		}
		foreach ( $styles as $handle ) {
			wp_dequeue_style( $handle );
		}
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
	 * @since 7.0.0
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
	 * @since 7.0.0
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
	 * @since 7.0.0
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

	/**
	 * Data provider for context-specific asset handles.
	 *
	 * @return array[]
	 */
	public function data_provider_llms_context_handles() {

		return array(
			array( 'script', 'llms-ajax' ),
			array( 'script', 'jquery-ui-tooltip' ),
			array( 'script', 'jquery-ui-datepicker' ),
			array( 'script', 'jquery-ui-slider' ),
			array( 'script', 'webui-popover' ),
			array( 'style', 'webui-popover' ),
		);
	}

	/**
	 * Test that context-specific assets are NOT enqueued on a plain (non-LifterLMS) page.
	 *
	 * @dataProvider data_provider_llms_context_handles
	 *
	 * @param string $type   Asset type ('script' or 'style').
	 * @param string $handle Asset handle.
	 * @return void
	 */
	public function test_context_assets_not_enqueued_on_plain_page( $type, $handle ) {

		$this->go_to( home_url( '/?p=1' ) );

		LLMS_Frontend_Assets::enqueue_styles();
		LLMS_Frontend_Assets::enqueue_scripts();

		$this->assertAssetNotEnqueued( $type, $handle );
	}

	/**
	 * Test that context-specific assets ARE enqueued on a LifterLMS course page.
	 *
	 * @dataProvider data_provider_llms_context_handles
	 *
	 * @param string $type   Asset type ('script' or 'style').
	 * @param string $handle Asset handle.
	 * @return void
	 */
	public function test_context_assets_enqueued_on_course_page( $type, $handle ) {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->go_to( get_permalink( $course_id ) );

		LLMS_Frontend_Assets::enqueue_styles();
		LLMS_Frontend_Assets::enqueue_scripts();

		$this->assertAssetIsEnqueued( $type, $handle );
	}

	/**
	 * Test that the llms-form-checkout script loads on account and checkout pages.
	 *
	 * @return void
	 */
	public function test_form_checkout_enqueued_on_account_and_checkout() {

		LLMS_Install::create_pages();

		// Checkout page.
		$this->go_to( llms_get_page_url( 'checkout' ) );
		LLMS_Frontend_Assets::enqueue_scripts();
		$this->assertAssetIsEnqueued( 'script', 'llms-form-checkout' );

		wp_dequeue_script( 'llms-form-checkout' );

		// Account page.
		$this->go_to( llms_get_page_url( 'myaccount' ) );
		LLMS_Frontend_Assets::enqueue_scripts();
		$this->assertAssetIsEnqueued( 'script', 'llms-form-checkout' );
	}

	/**
	 * Test the llms_load_frontend_assets filter can force-load assets on a plain page.
	 *
	 * @return void
	 */
	public function test_load_frontend_assets_filter_force_on() {

		$callback = function () {
			return true;
		};
		add_filter( 'llms_load_frontend_assets', $callback );

		$this->go_to( home_url( '/?p=1' ) );

		LLMS_Frontend_Assets::enqueue_styles();
		LLMS_Frontend_Assets::enqueue_scripts();

		$this->assertAssetIsEnqueued( 'script', 'llms-ajax' );
		$this->assertAssetIsEnqueued( 'script', 'webui-popover' );
		$this->assertAssetIsEnqueued( 'style', 'webui-popover' );

		remove_filter( 'llms_load_frontend_assets', $callback );
	}

	/**
	 * Test that lifterlms-styles and the llms script still load by default on a plain page.
	 *
	 * These stay always-on for backward compatibility; the filter exists to suppress them.
	 *
	 * @return void
	 */
	public function test_core_styles_scripts_always_on_by_default() {

		$this->go_to( home_url( '/?p=1' ) );

		LLMS_Frontend_Assets::enqueue_styles();
		LLMS_Frontend_Assets::enqueue_scripts();

		$this->assertAssetIsEnqueued( 'style', 'lifterlms-styles' );
		$this->assertAssetIsEnqueued( 'script', 'llms' );
	}

	/**
	 * Test the llms_load_frontend_assets filter can suppress the gated context assets.
	 *
	 * On an LLMS page with the filter forced off, the gated assets stay suppressed
	 * while the always-on core assets (lifterlms-styles, llms) still load.
	 *
	 * @return void
	 */
	public function test_load_frontend_assets_filter_force_off() {

		$callback = function () {
			return false;
		};
		add_filter( 'llms_load_frontend_assets', $callback );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->go_to( get_permalink( $course_id ) );

		LLMS_Frontend_Assets::enqueue_styles();
		LLMS_Frontend_Assets::enqueue_scripts();

		// Gated assets stay suppressed even on an LLMS page.
		$this->assertAssetNotEnqueued( 'script', 'llms-ajax' );
		$this->assertAssetNotEnqueued( 'script', 'webui-popover' );
		$this->assertAssetNotEnqueued( 'style', 'webui-popover' );

		remove_filter( 'llms_load_frontend_assets', $callback );
	}

}

