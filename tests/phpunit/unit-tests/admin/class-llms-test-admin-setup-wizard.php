<?php
/**
 * Test Setup Wizard
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group setup_wizard
 *
 * @since 7.4.0
 * @version 7.4.0
 */
class LLMS_Test_Admin_Setup_Wizard extends LLMS_Unit_Test_Case {

	/**
	 * Setup Before Class
	 *
	 * Include required class files
	 *
	 * @since 4.8.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-export-api.php';
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.setup.wizard.php';

	}

	/**
	 * Setup test case
	 *
	 * @since 4.8.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Setup_Wizard();

	}

	/**
	 * Test constructor
	 *
	 * @since 4.8.0
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
	 * @since 4.8.0
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
	 * @since 4.8.0
	 * @since 7.4.0 Added mock request to test for `llms-setup` page.
	 *
	 * @return void
	 */
	public function test_enqueue() {

		$this->mockGetRequest( array( 'page' => 'llms-setup' ) );
		$this->assertTrue( $this->main->enqueue() );

	}

	/**
	 * Test get_completed_url().
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_get_completed_url() {

		$ids = $this->factory->course->create_many( 3, array( 'sections' => 0 ) );

		// More than one course redirects to the course post table.
		$this->assertEquals( 'http://example.org/wp-admin/edit.php?post_type=course&orderby=date&order=desc', LLMS_Unit_Test_Util::call_method( $this->main, 'get_completed_url', array( $ids ) ) );
		unset( $ids[2] );
		$this->assertEquals( 'http://example.org/wp-admin/edit.php?post_type=course&orderby=date&order=desc', LLMS_Unit_Test_Util::call_method( $this->main, 'get_completed_url', array( $ids ) ) );

		// One course goes to the the course's edit page.
		unset( $ids[1] );
		$this->assertEquals( get_edit_post_link( $ids[0], 'not-display' ), LLMS_Unit_Test_Util::call_method( $this->main, 'get_completed_url', array( $ids ) ) );

	}

	/**
	 * Test get_current_step()
	 *
	 * @since 4.8.0
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
	 * @since 4.8.0
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
	 * @since 4.8.0
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
	 * @since 4.8.0
	 * @since 7.4.0 Escaped 'Save & Continue' text.
	 *
	 * @return void
	 */
	public function test_get_save_text() {

		$this->assertEquals( 'Allow', LLMS_Unit_Test_Util::call_method( $this->main, 'get_save_text', array( 'coupon' ) ) );
		$this->assertEquals( 'Import Courses', LLMS_Unit_Test_Util::call_method( $this->main, 'get_save_text', array( 'finish' ) ) );

		$this->assertEquals( 'Save &amp; Continue', LLMS_Unit_Test_Util::call_method( $this->main, 'get_save_text', array( 'anything-else' )  ));

	}

	/**
	 * Test get_save_text()
	 *
	 * @since 4.8.0
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
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_get_step_url() {

		$this->assertEquals( 'http://example.org/wp-admin/?page=llms-setup&step=mock', LLMS_Unit_Test_Util::call_method( $this->main, 'get_step_url', array( 'mock' ) ) );

	}

	/**
	 * Test get_steps()
	 *
	 * @since 4.8.0
	 * @since 7.4.0 Updated step value to array and check for title.
	 *
	 * @return void
	 */
	public function test_get_steps() {

		$steps = $this->main->get_steps();
		$this->assertTrue( is_array( $steps ) );

		foreach ( $steps as $step => $args ) {
			$this->assertTrue( ! empty( $step ) );
			$this->assertTrue( is_array( $args ) );
			$this->assertArrayHasKey( 'title', $args );
			$this->assertTrue( is_string( $step ) );
			$this->assertTrue( is_string( $args['title'] ?? '' ) );
		}

	}

	/**
	 * Test output()
	 *
	 * @since 4.8.0
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
	 * @since 4.8.0
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
	 * Test save() for an invalid step
	 *
	 * This test also covers an error response from any valid step.
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_invalid_step() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'llms_setup_nonce' => wp_create_nonce( 'llms_setup_save' ),
			'llms_setup_save'  => 'fake-step',
 		) );

		$res = $this->main->save();

		$this->assertIsWpError( $res );
		$this->assertWPErrorCodeEquals( 'llms-setup-save-invalid', $res );

		$this->assertEquals( $res, $this->main->error );

	}

	/**
	 * Test save() for success (and redirection)
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_success() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockGetRequest( array(
			'step' => 'pages',
		) );

		$this->mockPostRequest( array(
			'llms_setup_nonce' => wp_create_nonce( 'llms_setup_save' ),
			'llms_setup_save'  => 'pages',
 		) );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/?page=llms-setup&step=payments [302] YES' );

		$this->main->save();

	}

	/**
	 * Test save_coupon() when an http error is encountered
	 *
	 * @since 4.8.0
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
	 * @since 4.8.0
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
	 * @since 4.8.0
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
	 * @since 4.8.0
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

	/**
	 * Test save_finish() when no import ids are provided
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_finish_error_no_ids() {

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'save_finish' ) );

	}

	/**
	 * Test save_finish() when an export api error occurs
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_finish_error_api() {

		$this->mockPostRequest( array(
			'llms_setup_course_import_ids' => array( 1 ),
		) );

		$handler = function( $res ) {
			return new WP_Error( 'mock', 'Mocked API response.' );
		};
		add_filter( 'pre_http_request', $handler );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'save_finish' );
		$this->assertIsWpError( $res );
		$this->assertWPErrorCodeEquals( 'mock', $res );

		remove_filter( 'pre_http_request', $handler );

	}

	/**
	 * Test save_finish() when an error is encountered during generation
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_finish_error_generator() {

		$this->mockPostRequest( array(
			'llms_setup_course_import_ids' => array( 1 ),
		) );

		$handler = function( $res ) {
			return array();
		};
		add_filter( 'pre_http_request', $handler );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'save_finish' );
		$this->assertIsWpError( $res );
		$this->assertWPErrorCodeEquals( 'missing-generator', $res );

		remove_filter( 'pre_http_request', $handler );

	}

	/**
	 * Test save_finish() for success
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_finish_success() {

		$this->mockPostRequest( array(
			'llms_setup_course_import_ids' => array( 33579 ), // Free course template.
		) );

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'save_finish' );

		foreach ( $res as $id ) {

			$this->assertTrue( is_numeric( $id ) );
			$this->assertEquals( 'course', get_post_type( $id ) );

		}

	}

	/**
	 * Test save_pages()
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_save_pages() {

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'save_pages' ) );

	}

	/**
	 * Test save_payments()
	 *
	 * @since 4.8.0
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
