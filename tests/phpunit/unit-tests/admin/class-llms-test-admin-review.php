<?php
/**
 * Tests for LLMS_Admin_Review class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_reviews
 *
 * @since 3.24.0
 * @version 7.1.0
 */
class LLMS_Test_Admin_Review extends LLMS_UnitTestCase {

	/**
	 * Setup test class
	 *
	 * @since 4.14.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-review.php';

	}

	/**
	 * Setup test case
	 *
	 * @since 3.24.0
	 * @since 4.14.0 Move file include into `setUpBeforeClass()`.
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Review();

	}

	/**
	 * Test admin_footer() when it's not supposed to display
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_admin_footer_screen_not_set() {
		$this->assertEquals( 'fake', $this->main->admin_footer( 'fake' ) );
	}

	/**
	 * Test admin_footer() when it's supposed to display.
	 *
	 * @since 4.14.0
	 * @since 7.1.0 Updated expected text.
	 *
	 * @return void
	 */
	public function test_admin_footer_screen_on_lifterlms_screen() {

		set_current_screen( 'lifterlms' );
		$this->assertEquals( 'Please rate <strong>LifterLMS</strong> <a class="llms-rating-stars" href="https://wordpress.org/support/plugin/lifterlms/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="https://wordpress.org/support/plugin/lifterlms/reviews/?filter=5#new-post" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word. Thank you from the LifterLMS team!', $this->main->admin_footer( 'fake' ) );
		set_current_screen( 'front' );

	}

	/**
	 * Test dismiss() for a logged out user with no nonce
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_dismiss_permissions_logged_out_no_nonce() {

		try {
			$this->main->dismiss();
		} catch ( WPDieException $e ) {
			$this->assertSame( '', get_option( 'llms_review', '' ) );
		}

	}

	/**
	 * Test dismiss() for a valid user with no nonce
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_dismiss_permissions_logged_in_invalid_nonce() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'success' => 'yes',
			'nonce'   => 'fake',
		) );

		try {
			$this->main->dismiss();
		} catch ( WPDieException $e ) {
			$this->assertSame( '', get_option( 'llms_review', '' ) );
		}

	}

	/**
	 * Test dismiss() when the user goes to wp.org
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_dismiss_success() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'success' => 'yes',
			'nonce'   => wp_create_nonce( 'llms-admin-review-request-dismiss' ),
		) );

		try {
			$this->main->dismiss();
		} catch ( WPDieException $e ) {

			$this->assertEquals( array(
				'time'      => time(),
				'dismissed' => true,
				'success'   => 'yes',
			), get_option( 'llms_review' ) );

		}

	}


	/**
	 * Test dismiss() when the user ignores/dismissed
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_dismiss_nope() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'success' => 'no',
			'nonce'   => wp_create_nonce( 'llms-admin-review-request-dismiss' ),
		) );

		try {
			$this->main->dismiss();
		} catch ( WPDieException $e ) {
			$review = get_option( 'llms_review' );

			$this->assertTrue( $review['dismissed'] );
			$this->assertEquals( 'no', $review['success'] );

		}

	}

	/**
	 * Test maybe_show_notice() when logged out.
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_maybe_show_notice_no_user() {
		$this->assertNull( $this->main->maybe_show_notice() );
	}

	/**
	 * Test maybe_show_notice() on its first run
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_maybe_show_notice_first_run() {

		delete_option( 'llms_review' );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertFalse( $this->main->maybe_show_notice() );

		$this->assertEquals( array(
			'time'      => time(),
			'dismissed' => false,
		), get_option( 'llms_review' ) );

	}

	/**
	 * Test maybe_show_notice()
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_maybe_show() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->factory->student->create_and_enroll_many( 30, $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		// Already Dismissed.
		update_option( 'llms_review', array(
			'time'      => time() - YEAR_IN_SECONDS,
			'dismissed' => true,
		) );
		$this->assertFalse( $this->main->maybe_show_notice() );

		// Too soon.
		update_option( 'llms_review', array(
			'time'      => time() - HOUR_IN_SECONDS,
			'dismissed' => false,
		) );
		$this->assertFalse( $this->main->maybe_show_notice() );

		// Okay.
		update_option( 'llms_review', array(
			'time'      => time() - YEAR_IN_SECONDS,
			'dismissed' => false,
		) );

		$output = $this->get_output( array( $this->main, 'maybe_show_notice' ) );

		$this->assertStringContains( '<div class="notice notice-info is-dismissible llms-admin-notice llms-review-notice">', $output );

	}

	/**
	 * Test maybe_show_notice() when the notice would display (assuming there were enough enrollments)
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_maybe_show_too_few_enrollments() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Okay.
		update_option( 'llms_review', array(
			'time'      => time() - YEAR_IN_SECONDS,
			'dismissed' => false,
		) );

		$this->assertFalse( $this->main->maybe_show_notice() );

	}

	/**
	 * Test round_down().
	 *
	 * @since 3.24.0
	 * @since 4.14.0 Use a loop.
	 *
	 * @return void
	 */
	public function test_round_down() {

		$tests = array(
			// Expected, Input.
			array( 1, 1 ),
			array( 5, 5 ),
			array( 9, 9 ),
			array( 10, 11 ),
			array( 20, 25 ),
			array( 30, 37 ),
			array( 40, 40 ),
			array( 50, 58 ),
			array( 60, 63 ),
			array( 70, 72 ),
			array( 80, 88 ),
			array( 90, 99 ),
			array( 100, 105 ),
			array( 200, 293 ),
			array( 300, 392 ),
			array( 500, 532 ),
			array( 700, 781 ),
			array( 800, 850 ),
			array( 900, 900 ),
			array( 1000, 1000 ),
			array( 1000, 1101 ),
			array( 1000, 1500 ),
			array( 2000, 2205 ),
			array( 5000, 5878 ),
			array( 9000, 9999 ),
			array( 10000, 10000 ),
			array( 10000, 10001 ),
			array( 10000, 10299 ),
			array( 10000, 50099 ),
		);

		foreach ( $tests as $vals ) {
			$this->assertEquals( $vals[0], LLMS_Admin_Review::round_down( $vals[1] ) );
		}

	}

}
