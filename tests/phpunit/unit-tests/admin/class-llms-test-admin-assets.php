<?php
/**
 * Test Admin Assets Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_assets
 * @group assets
 *
 * @since 4.3.3
 */
class LLMS_Test_Admin_Assets extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case
	 *
	 * @since 4.3.3
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Assets();

	}

	/**
	 * Tear down test case
	 *
	 * Dequeue & Dereqister all assets that may have been enqueued during tests.
	 *
	 * @since 4.3.3
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		/**
		 * List of asset handles that may have been enqueued or registered during the test
		 *
		 * We do not care if they actually were registered or enqueued, we'll remove them
		 * anyway since the functions will fail silently for assets that were not
		 * previously enqueued or registered.
		 */
		$handles = array(
			'llms-google-charts',
			'llms-analytics'
		);

		foreach ( $handles as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}

	}

	/**
	 * Test block_editor_assets()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_block_editor_assets_for_certificates() {

		if ( ! llms_is_block_editor_supported_for_certificates() ) {
			$this->markTestSkipped( 'Block editor is not supported for certificates on this version of WordPress.' );
		}

		$handle    = 'llms-admin-certificate-editor';
		$inline_id = 'llms-admin-certificate-settings';

		$reset = function() use ( $handle ) {
			LLMS_Unit_Test_Util::set_private_property( llms()->assets, 'inline', array() );
			wp_dequeue_script( $handle );
		};
		$reset();

		// Wrong screen.
		set_current_screen( 'fake' );
		$this->main->block_editor_assets();
		$this->assertAssetNotEnqueued( 'script', $handle );
		$this->assertArrayNotHasKey(
			$inline_id,
			LLMS_Unit_Test_Util::get_private_property_value( llms()->assets, 'inline' ) );

		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$reset();

			set_current_screen( $post_type );
			global $current_screen;
			$current_screen->is_block_editor = true;

			$this->main->block_editor_assets();

			$this->assertAssetIsEnqueued( 'script', $handle );

			$this->assertArrayHasKey(
				$inline_id,
				LLMS_Unit_Test_Util::get_private_property_value( llms()->assets, 'inline' )
			);

		}

		llms_tests_reset_current_screen();
		$current_screen->is_block_editor = false;

	}

	/**
	 * Test get_analytics_options()
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_get_analytics_options() {

		$this->assertEquals( array( 'currency_format' => '$#,##0.00' ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_analytics_options' ) );

		// Simulate comma decimal separator that's forced back to decimals.
		add_filter( 'lifterlms_thousand_separator', function( $sep ) { return '.'; } );
		add_filter( 'lifterlms_decimal_separator', function( $sep ) { return ','; } );

		$this->assertEquals( array( 'currency_format' => '$#,##0.00' ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_analytics_options' ) );

		remove_all_filters( 'lifterlms_thousand_separator' );
		remove_all_filters( 'lifterlms_decimal_separator' );

		// Simulate non US symbol on the right with a space.
		add_filter( 'lifterlms_currency_symbol', function( $sym ) { return 'A'; } );
		add_filter( 'lifterlms_price_format', function( $format ) { return '%2$s %1$s'; } );

		$this->assertEquals( array( 'currency_format' => '#,##0.00 A' ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_analytics_options' ) );

		remove_all_filters( 'lifterlms_currency_symbol' );
		remove_all_filters( 'lifterlms_price_format' );

	}

	/**
	 * Test maybe_enqueue_reporting() on a screen where it shouldn't be registered.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_wrong_screen() {

		$screen = (object) array( 'base' => 'fake' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetNotRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetNotRegistered( 'script', 'llms-analytics' );

		$this->assertAssetNotEnqueued( 'script', 'llms-google-charts' );
		$this->assertAssetNotEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on the general settings page where analytics are required for the data widgets
	 *
	 * This test tests the default "assumed" tab when there's no `tab` set in the $_GET array.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_general_settings_assumed() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-settings' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetIsEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on the general settings page where analytics are required for the data widgets
	 *
	 * This test is the same as test_maybe_enqueue_reporting_general_settings_assumed() except this one explicitly
	 * tests for the presence of the `tab=general` in the $_GET array.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_general_settings_explicit() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-settings' );
		$this->mockGetRequest( array( 'tab' => 'general' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetIsEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on settings tabs other than general, scripts will be registered but not enqueued.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_other_tabs() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-settings' );
		$this->mockGetRequest( array( 'tab' => 'fake' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetNotEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on reporting screens where the scripts aren't needed.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_invalid_reporting_screens() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-reporting' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetNotEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on the enrollments reporting screen
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_enrollments_reporting_screens() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-reporting' );
		$this->mockGetRequest( array( 'tab' => 'enrollments' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetIsEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on the sales reporting screen
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_sales_reporting_screens() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-reporting' );
		$this->mockGetRequest( array( 'tab' => 'sales' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetIsEnqueued( 'script', 'llms-analytics' );

	}

	/**
	 * Test maybe_enqueue_reporting() on the main quizzes reporting screen
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_quiz_main_reporting_screens() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-reporting' );
		$this->mockGetRequest( array( 'tab' => 'quizzes' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetNotEnqueued( 'script', 'llms-analytics' );
		$this->assertAssetNotEnqueued( 'script', 'llms-quiz-attempt-review' );

	}

	/**
	 * Test maybe_enqueue_reporting() on the quiz attempts reporting screen
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_reporting_quiz_attempts_reporting_screens() {

		$screen = (object) array( 'base' => 'lifterlms_page_llms-reporting' );
		$this->mockGetRequest( array( 'tab' => 'quizzes', 'stab' => 'attempts' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_enqueue_reporting', array( $screen ) );

		$this->assertAssetIsRegistered( 'script', 'llms-google-charts' );
		$this->assertAssetIsRegistered( 'script', 'llms-analytics' );

		$this->assertAssetNotEnqueued( 'script', 'llms-analytics' );
		$this->assertAssetIsEnqueued( 'script', 'llms-quiz-attempt-review' );

	}


}
