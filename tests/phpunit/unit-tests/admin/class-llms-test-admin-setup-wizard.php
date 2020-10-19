<?php
/**
 * Test Setup Wizard
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group setup_wizard
 *
 * @since [version]
 */
class LLMS_Test_Admin_Setup_Wizard extends LLMS_Unit_Test_Case {

	/**
	 * Setup Before Class
	 *
	 * Include required class files
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {

		parent::setupBeforeClass();
		include LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.setup.wizard.php';

	}

	/**
	 * Setup test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Admin_Setup_Wizard();

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor() {

		foreach ( array( '__return_true' => 10, '__return_false' => false ) as $func => $expect ) {

			add_filter( 'llms_enable_setup_wizard', $func );

			remove_action( 'admin_enqueue_scripts', array( $this->main, 'enqueue' ) );
			remove_action( 'admin_menu', array( $this->main, 'admin_menu' ) );
			remove_action( 'admin_init', array( $this->main, 'save' ) );

			$this->assertEquals( false, has_action( 'admin_enqueue_scripts', array( $this->main, 'enqueue' ) ) );
			$this->assertEquals( false, has_action( 'admin_menu', array( $this->main, 'admin_menu' ) ) );
			$this->assertEquals( false, has_action( 'admin_init', array( $this->main, 'save' ) ) );

			$this->main = new LLMS_Admin_Setup_Wizard();

			$this->assertEquals( $expect, has_action( 'admin_enqueue_scripts', array( $this->main, 'enqueue' ) ) );
			$this->assertEquals( $expect, has_action( 'admin_menu', array( $this->main, 'admin_menu' ) ) );
			$this->assertEquals( $expect, has_action( 'admin_init', array( $this->main, 'save' ) ) );

			remove_filter( 'llms_enable_setup_wizard', $func );

		}

	}

	/**
	 * Test admin_menu()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_admin_menu() {

		// No user.
		$this->assertFalse( $this->main->admin_menu() );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertEquals( 'admin_page_llms-setup', $this->main->admin_menu() );

		$this->assertEquals( 'yes', get_option( 'lifterlms_first_time_setup' ) );

		// Clean up.
		delete_option( 'lifterlms_first_time_setup' );
		wp_set_current_user( null );

	}

	/**
	 * Test enqueue()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_enqueue() {

		$this->assertTrue( $this->main->enqueue() );

	}

	/**
	 * Test generator_course_status()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generator_course_status() {

		$this->assertEquals( 'publish', $this->main->generator_course_status( 'fake' ) );
		$this->assertEquals( 'publish', $this->main->generator_course_status( 'draft' ) );

	}

	/**
	 * Test get_current_step()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_current_step() {

		$this->assertEquals( 'intro', $this->main->get_current_step() );

		$this->mockGetRequest( array( 'step' => 'mock' ) );
		$this->assertEquals( 'mock', $this->main->get_current_step() );

	}

	/**
	 * Test get_next_step()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_next_step() {

		// Not found.
		$this->assertFalse( $this->main->get_next_step( 'fake' ) );

		// No next step.
		$this->assertFalse( $this->main->get_next_step( 'finish' ) );

		$this->assertEquals( 'pages', $this->main->get_next_step( 'intro' ) );

		$this->mockGetRequest( array( 'step' => 'intro' ) );
		$this->assertEquals( 'pages', $this->main->get_next_step() );

	}


	/**
	 * Test get_prev_step()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_prev_step() {

		// Not found.
		$this->assertFalse( $this->main->get_prev_step( 'fake' ) );

		// No previous step.
		$this->assertFalse( $this->main->get_prev_step( 'intro' ) );

		$this->assertEquals( 'coupon', $this->main->get_prev_step( 'finish' ) );

		$this->mockGetRequest( array( 'step' => 'finish' ) );
		$this->assertEquals( 'coupon', $this->main->get_prev_step() );

	}

	/**
	 * Test get_save_text()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_save_text() {

		$this->assertEquals( 'Allow', LLMS_Unit_Test_Util::call_method( $this->main, 'get_save_text', array( 'coupon' ) ) );
		$this->assertEquals( 'Install a Sample Course', LLMS_Unit_Test_Util::call_method( $this->main, 'get_save_text', array( 'finish' ) ) );

		$this->assertEquals( 'Save & Continue', LLMS_Unit_Test_Util::call_method( $this->main, 'get_save_text', array( 'anything-else' )  ));

	}

	/**
	 * Test get_save_text()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_skip_text() {

		$this->assertEquals( 'No thanks', LLMS_Unit_Test_Util::call_method( $this->main, 'get_skip_text', array( 'coupon' ) ) );
		$this->assertEquals( 'Skip this step', LLMS_Unit_Test_Util::call_method( $this->main, 'get_skip_text', array( 'anything-else' )  ));

	}

	/**
	 * Test get_step_url()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_step_url() {

		$this->assertEquals( 'http://example.org/wp-admin/?page=llms-setup&step=mock', LLMS_Unit_Test_Util::call_method( $this->main, 'get_step_url', array( 'mock' ) ) );

	}

	/**
	 * Test get_steps()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_steps() {

		$res = $this->main->get_steps();
		$this->assertTrue( is_array( $res ) );
		foreach ( $res as $key => $val ) {
			$this->assertTrue( ! empty( $key ) );
			$this->assertTrue( ! empty( $val ) );
			$this->assertTrue( is_string( $key ) );
			$this->assertTrue( is_string( $val ) );
		}

	}

	/**
	 * Test output()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_output() {

		$output = $this->get_output( array( $this->main, 'output' ), array( 'intro' ) );

		$this->assertStringContains( '<div id="llms-setup-wizard">', $output );
		$this->assertStringContains( '<h1 id="llms-logo">', $output );
		$this->assertStringContains( '<ul class="llms-setup-progress">', $output );

	}

	/**
	 * Test save() when there are nonce or user permission issues
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_permissions_issues() {

		// No nonce.
		$this->assertNull( $this->main->save() );

		// Invalid nonce.
		$data = array(
			'llms_setup_nonce' => 'fake',
		);
		$this->mockPostRequest( $data );
		$this->assertNull( $this->main->save() );

		// Missing user.
		$data = array(
			'llms_setup_nonce' => wp_create_nonce( 'llms_setup_save' ),
		);
		$this->mockPostRequest( $data );
		$this->assertNull( $this->main->save() );

	}

	/**
	 * Test save_coupon() when an http error is encountered
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_coupon_http_error() {

		$handler = function( $preempt, $args, $url ) {
			if ( 'https://lifterlms.com/llms-api/tracking' === $url ) {
				return new WP_Error( 'mock-err', 'Error' );
			}
			return $preempt;
		};

		add_filter( 'pre_http_request', $handler, 10, 3 );

		$ret = LLMS_Unit_Test_Util::call_method( $this->main, 'save_coupon' );

		$this->assertIsWpError( $ret );
		$this->assertWPErrorCodeEquals( 'mock-err', $ret );

		remove_filter( 'pre_http_request', $handler, 10 );

	}

	/**
	 * Test save_coupon() when the tracking data api returns an error
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_coupon_api_error() {

		$handler = function( $preempt, $args, $url ) {
			if ( 'https://lifterlms.com/llms-api/tracking' === $url ) {
				return array( 'body' => json_encode( array( 'success' => false, 'message' => 'Server error' ) ) );
			}
			return $preempt;
		};

		add_filter( 'pre_http_request', $handler, 10, 3 );

		$ret = LLMS_Unit_Test_Util::call_method( $this->main, 'save_coupon' );

		$this->assertIsWpError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-setup-coupon-save-tracking-api', $ret );

		remove_filter( 'pre_http_request', $handler, 10 );

	}

	/**
	 * Test save_coupon() when the tracking data api returns data in an unexpected format
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_coupon_unknown_error() {

		$handler = function( $preempt, $args, $url ) {
			if ( 'https://lifterlms.com/llms-api/tracking' === $url ) {
				return array( 'body' => json_encode( array() ) );
			}
			return $preempt;
		};

		add_filter( 'pre_http_request', $handler, 10, 3 );

		$ret = LLMS_Unit_Test_Util::call_method( $this->main, 'save_coupon' );

		$this->assertIsWpError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-setup-coupon-save-unknown', $ret );

		remove_filter( 'pre_http_request', $handler, 10 );

	}

	/**
	 * Test save_coupon() success
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_coupon_success() {

		delete_option( 'llms_allow_tracking' );
		$handler = function( $preempt, $args, $url ) {
			if ( 'https://lifterlms.com/llms-api/tracking' === $url ) {
				return array( 'body' => json_encode( array( 'success' => true, 'message' => '' ) ) );
			}
			return $preempt;
		};

		add_filter( 'pre_http_request', $handler, 10, 3 );

		$ret = LLMS_Unit_Test_Util::call_method( $this->main, 'save_coupon' );

		$this->assertTrue( $ret );
		$this->assertEquals( 'yes', get_option( 'llms_allow_tracking' ) );

		remove_filter( 'pre_http_request', $handler, 10 );

	}

	public function test_save_finish_error() {



	}

	public function test_save_finish() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'save_finish' );
		$this->assertTrue( is_numeric( $res ) );
		$this->assertEquals( 'course', get_post_type( $res ) );

	}

	/**
	 * Test save_pages()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_pages() {

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'save_pages' ) );

	}

	/**
	 * Test save_payments()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_payments() {

		// With values submitted.
		$this->mockPostRequest( array(
			'country'         => 'MOCK',
			'currency'        => 'CURR',
			'manual_payments' => 'yes'
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'save_payments' ) );

		$this->assertEquals( 'MOCK', get_option( 'lifterlms_country' ) );
		$this->assertEquals( 'CURR', get_option( 'lifterlms_currency' ) );
		$this->assertEquals( 'yes', get_option( 'llms_gateway_manual_enabled' ) );

		delete_option( 'lifterlms_country' );
		delete_option( 'lifterlms_currency' );
		delete_option( 'llms_gateway_manual_enabled' );

		// No values, use the defaults.
		$this->mockPostRequest( array() );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'save_payments' ) );

		$this->assertEquals( 'US', get_option( 'lifterlms_country' ) );
		$this->assertEquals( 'USD', get_option( 'lifterlms_currency' ) );
		$this->assertEquals( 'no', get_option( 'llms_gateway_manual_enabled' ) );

	}

}
