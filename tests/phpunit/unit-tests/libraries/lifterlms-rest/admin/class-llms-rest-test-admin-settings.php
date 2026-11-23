<?php
/**
 * Test the LLMS_REST_Admin_Settings class.
 *
 * @package LifterLMS_Rest/Tests/Admin
 *
 * @group admin
 * @group rest_admin
 *
 * @since 11.0.0
 */
class LLMS_REST_Test_Admin_Settings extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Set up the tests.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		set_current_screen( 'index.php' );
		LLMS_REST_API()->includes();

		$this->obj = new LLMS_REST_Admin_Settings();

	}

	/**
	 * Clean up webhooks between tests.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_webhooks" );

	}

	/**
	 * Retrieve the notice output for the current user.
	 *
	 * @since 11.0.0
	 *
	 * @return string
	 */
	private function get_notice() {
		LLMS_REST_API()->webhooks()->load();
		ob_start();
		$this->obj->output_undeliverable_webhooks_notice();
		return ob_get_clean();
	}

	/**
	 * Undeliverable active webhooks are listed for each user who can manage webhooks until that user dismisses the notice,
	 * and the notice returns when the list changes.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function test_undeliverable_webhooks_notice() {

		$admin      = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$manager    = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );

		// Allowed: course topic by a manager, action topic by an admin.
		wp_set_current_user( $manager );
		LLMS_REST_API()->webhooks()->create( array( 'name' => 'Course webhook', 'topic' => 'course.created', 'delivery_url' => 'https://mock.tld', 'status' => 'active' ) );
		wp_set_current_user( $admin );
		LLMS_REST_API()->webhooks()->create( array( 'name' => 'Admin action webhook', 'topic' => 'action.admin_hook', 'delivery_url' => 'https://mock.tld', 'status' => 'active' ) );

		wp_set_current_user( $admin );
		$this->assertSame( '', $this->get_notice() );

		// Not allowed: action topic saved by a manager (e.g. before this restriction existed).
		$register = function( $topics ) {
			$topics['action.legacy_hook'] = 'Legacy';
			return $topics;
		};
		add_filter( 'llms_rest_webhook_topics', $register );
		wp_set_current_user( $manager );
		$hook = LLMS_REST_API()->webhooks()->create( array( 'name' => 'Manager action webhook', 'topic' => 'action.legacy_hook', 'delivery_url' => 'https://mock.tld', 'status' => 'active' ) );
		LLMS_REST_API()->webhooks()->create( array( 'name' => 'Disabled manager webhook', 'topic' => 'action.legacy_hook', 'delivery_url' => 'https://mock.tld', 'status' => 'disabled' ) );
		remove_filter( 'llms_rest_webhook_topics', $register );

		foreach ( array( $admin, $manager ) as $user ) {
			wp_set_current_user( $user );
			$notice = $this->get_notice();
			$this->assertStringContainsString( 'edit-webhook=' . $hook->get( 'id' ) . '"', $notice );
			$this->assertStringContainsString( 'Manager action webhook', $notice );
			$this->assertStringNotContainsString( 'Admin action webhook', $notice );
			$this->assertStringNotContainsString( 'Course webhook', $notice );
			$this->assertStringNotContainsString( 'Disabled manager webhook', $notice );
			$this->assertStringNotContainsString( 'add_filter', $notice );
		}

		wp_set_current_user( $instructor );
		$this->assertSame( '', $this->get_notice() );

		// Dismissal applies to the dismissing user only.
		wp_set_current_user( $manager );
		$this->mockGetRequest( array(
			'llms-rest-dismiss-webhooks-notice' => 1,
			'_llms_rest_nonce'                  => wp_create_nonce( 'llms-rest-dismiss-webhooks-notice' ),
		) );
		LLMS_REST_API()->webhooks()->load();
		try {
			$this->obj->maybe_dismiss_undeliverable_webhooks_notice();
			$this->fail( 'Expected a redirect.' );
		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {}
		$this->assertSame( '', $this->get_notice() );

		wp_set_current_user( $admin );
		$this->assertStringContainsString( 'Manager action webhook', $this->get_notice() );

		// A new undeliverable webhook shows the notice again to the user who dismissed it.
		add_filter( 'llms_rest_webhook_topics', $register );
		wp_set_current_user( $manager );
		LLMS_REST_API()->webhooks()->create( array( 'name' => 'Second manager webhook', 'topic' => 'action.legacy_hook', 'delivery_url' => 'https://mock.tld', 'status' => 'active' ) );
		remove_filter( 'llms_rest_webhook_topics', $register );
		$this->assertStringContainsString( 'Second manager webhook', $this->get_notice() );

	}

}
