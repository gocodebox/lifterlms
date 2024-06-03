<?php
/**
 * Test Admin Profile Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_profile
 *
 * @since 5.0.0
 */
class LLMS_Test_Admin_Profile extends LLMS_Unit_Test_Case {

	/**
	 * Set Up Before Class
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-profile.php';

	}

	/**
	 * Set-Up
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Profile();

	}

	/**
	 * Tear down
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		wp_set_current_user( null );

	}

	/**
	 * Test current_user_can_edit_admin_custom_fields() method
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_current_user_can_edit_admin_custom_fields() {

		$func = LLMS_Unit_Test_Util::get_private_method( $this->main, 'current_user_can_edit_admin_custom_fields' );

		// No user logged in.
		$this->assertFalse(
			$func->invokeArgs( $this->main, array( null ) ) // No user passed.
		);

		$user = $this->factory->user->create();

		$this->assertFalse(
			$func->invokeArgs( $this->main, array( $user ) )
		);

		// Create a subscriber.
		$subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		// Log-in.
		wp_set_current_user( $subscriber );

		// Still cannot manage the other user custom fields.
		$this->assertFalse(
			$func->invokeArgs( $this->main, array( $user ) )
		);

		// Create an admin.
		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );
		// Log-in.
		wp_set_current_user( $admin );

		$this->assertTrue(
			$func->invokeArgs( $this->main, array( $user ) )
		);

	}

	/**
	 * Test add_user_meta_fields()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_add_user_meta_fields() {

		$user = $this->factory->user->create();

		// No logged-in user.
		$this->assertFalse(
			$this->main->add_user_meta_fields( $user )
		);

		// Create an admin.
		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );
		// Log-in.
		wp_set_current_user( $admin );

		// Admin user logged-in.
		ob_start(); // ob_start/ob_end_clean wrapper to avoid the view printing (via `include_once`).
		$this->assertTrue(
			$this->main->add_user_meta_fields( $user )
		);
		$this->assertTrue(
			$this->main->add_user_meta_fields( $admin )
		);
		ob_end_clean();

		// Simple user logged-in: no required caps.
		wp_set_current_user( $user );
		$this->assertFalse(
			$this->main->add_user_meta_fields( $user )
		);
		$this->assertFalse(
			$this->main->add_user_meta_fields( $admin )
		);

		// Admin user logged-in but empty custom fields.
		wp_set_current_user( $admin );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'fields', null );

		add_filter( 'llms_admin_profile_fields', '__return_empty_array' );

		$this->assertFalse(
			$this->main->add_user_meta_fields( $user )
		);
		$this->assertFalse(
			$this->main->add_user_meta_fields( $admin )
		);

		remove_filter( 'llms_admin_profile_fields', '__return_empty_array' );

	}
}
