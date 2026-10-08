<?php
/**
 * Test the API Keys admin settings save handler.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group admin
 * @group admin_settings_api_keys
 *
 * @since 1.0.7
 */
class LLMS_REST_Test_Admin_Settings_API_Keys extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.7
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		// Ensure required classes are loaded.
		set_current_screen( 'index.php' );
		LLMS_REST_API()->includes();
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/admin/class-llms-rest-admin-settings-page.php';

		// Instantiating the page requires the API Keys settings class.
		new LLMS_REST_Admin_Settings_Page();

	}

	/**
	 * Tear down the test case.
	 *
	 * @since 1.0.7
	 *
	 * @return void
	 */
	public function tear_down() {

		unset( $_GET['add-key'], $_GET['edit-key'] );
		unset( $_POST['llms_rest_key_description'], $_POST['llms_rest_key_user_id'], $_POST['llms_rest_key_permissions'] );

		parent::tear_down();

	}

	/**
	 * Count the API keys owned by a given user.
	 *
	 * @since 1.0.7
	 *
	 * @param int $user_id WP_User ID.
	 * @return int
	 */
	private function count_keys_for_user( $user_id ) {

		$query = new LLMS_REST_API_Keys_Query(
			array(
				'user_id' => $user_id,
			)
		);

		return count( $query->get_results() );

	}

	/**
	 * An LMS Manager cannot create a key owned by a user they are not allowed to edit.
	 *
	 * @since 1.0.7
	 *
	 * @return void
	 */
	public function test_save_create_rejects_unauthorized_owner() {

		$manager = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$admin   = $this->factory->user->create( array( 'role' => 'administrator' ) );

		wp_set_current_user( $manager );

		$_GET['add-key']                   = '1';
		$_POST['llms_rest_key_description'] = 'admin-key';
		$_POST['llms_rest_key_user_id']    = $admin;
		$_POST['llms_rest_key_permissions'] = 'read_write';

		$ret = LLMS_Rest_Admin_Settings_API_Keys::save();

		$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_user_id', $ret );
		$this->assertEquals( 0, $this->count_keys_for_user( $admin ) );

	}

	/**
	 * An LMS Manager can create a key owned by a user they are allowed to edit.
	 *
	 * @since 1.0.7
	 *
	 * @return void
	 */
	public function test_save_create_allows_authorized_owner() {

		$manager = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$student = $this->factory->user->create( array( 'role' => 'student' ) );

		wp_set_current_user( $manager );

		$_GET['add-key']                    = '1';
		$_POST['llms_rest_key_description']  = 'student-key';
		$_POST['llms_rest_key_user_id']     = $student;
		$_POST['llms_rest_key_permissions'] = 'read_write';

		$ret = LLMS_Rest_Admin_Settings_API_Keys::save();

		$this->assertInstanceOf( 'LLMS_REST_API_Key', $ret );
		$this->assertEquals( $student, $ret->get( 'user_id' ) );

	}

}
