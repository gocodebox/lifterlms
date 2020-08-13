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
 * @since [version]
 */
class LLMS_Test_Admin_Assets extends LLMS_Unit_Test_Case {

	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Admin_Assets();

	}

	/**
	 * Tear down test case
	 *
	 * Dequeue & Dereqister all assets that may have been enqueued during tests.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

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
	 * Test maybe_enqueue_reporting() on a screen where it shouldn't be registered.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
