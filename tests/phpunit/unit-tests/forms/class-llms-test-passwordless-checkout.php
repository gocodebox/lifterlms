<?php
/**
 * Test LLMS_Passwordless_Checkout
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group passwordless_checkout
 *
 * @since [version]
 */
class LLMS_Test_Passwordless_Checkout extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		LLMS_Forms::instance()->install( true );
		reset_phpmailer_instance();

		// Don't send cookie headers from the CLI.
		add_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Teardown the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );
		delete_option( LLMS_Passwordless_Checkout::OPTION );
		remove_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Retrieves the IDs of the fields in a form.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location.
	 * @return string[]
	 */
	private function get_field_ids( $location ) {
		return wp_list_pluck( LLMS_Forms::instance()->get_form_fields( $location ), 'id' );
	}

	/**
	 * Retrieves checkout data without any password fields.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_passwordless_data() {

		$data = $this->get_mock_user_data_array();
		unset( $data['password'], $data['password_confirm'] );

		return $data;
	}

	/**
	 * The checkout form keeps its password field when the option is off and loses it, with the confirmation and meter, when it's on.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_checkout_form_password_fields() {

		$ids = $this->get_field_ids( 'checkout' );
		$this->assertContains( 'password', $ids );
		$this->assertContains( 'password_confirm', $ids );
		$this->assertContains( 'llms-password-strength-meter', $ids );

		update_option( LLMS_Passwordless_Checkout::OPTION, 'yes' );

		$ids = $this->get_field_ids( 'checkout' );
		$this->assertNotContains( 'password', $ids );
		$this->assertNotContains( 'password_confirm', $ids );
		$this->assertNotContains( 'llms-password-strength-meter', $ids );
		$this->assertContains( 'email_address', $ids );
		$this->assertContains( 'llms_billing_address_1', $ids );

		// The registration form is unaffected.
		$this->assertContains( 'password', $this->get_field_ids( 'registration' ) );
	}

	/**
	 * Checkout without a password fails while the option is off.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_without_password_option_off() {

		$ret = llms_register_user( $this->get_passwordless_data(), 'checkout' );

		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );
		$this->assertSame( 0, get_current_user_id() );
	}

	/**
	 * Passwordless checkout creates a logged-in user flagged as having no password, and emails a working set password link.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_without_password() {

		update_option( LLMS_Passwordless_Checkout::OPTION, 'yes' );

		$data    = $this->get_passwordless_data();
		$user_id = llms_register_user( $data, 'checkout' );

		$this->assertIsInt( $user_id );
		$this->assertSame( $user_id, get_current_user_id() );
		$this->assertTrue( LLMS_Passwordless_Checkout::is_password_not_set( $user_id ) );

		$user = get_user_by( 'id', $user_id );
		$this->assertNotEmpty( $user->user_pass );

		$sent = tests_retrieve_phpmailer_instance()->get_sent();
		$this->assertSame( $data['email_address'], $sent->to[0][0] );
		$this->assertStringContainsString( 'Set your password', $sent->subject );

		// The emailed key is a valid reset key for the new user.
		$this->assertSame( 1, preg_match( '/[?&](?:amp;|#038;)?key=([^&"]+)/', $sent->body, $matches ) );
		$this->assertInstanceOf( 'WP_User', check_password_reset_key( $matches[1], $user->user_login ) );
	}

	/**
	 * The edit account form offers the set password link instead of asking for a current password until the user sets one.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_account_form_until_password_set() {

		update_option( LLMS_Passwordless_Checkout::OPTION, 'yes' );
		$data    = $this->get_passwordless_data();
		$user_id = llms_register_user( $data, 'checkout' );

		$ids = $this->get_field_ids( 'account' );
		$this->assertNotContains( 'password', $ids );
		$this->assertNotContains( 'password_current', $ids );
		$this->assertContains( 'llms-password-not-set', $ids );
		$this->assertContains( 'email_address', $ids );

		// Updating other account fields still works without any password fields.
		$data['first_name']   = 'Updated';
		$data['display_name'] = 'Updated Name';
		$this->assertSame( $user_id, llms_update_user( $data, 'account' ) );
		$this->assertSame( 'Updated', get_userdata( $user_id )->first_name );

		reset_password( get_userdata( $user_id ), 'a-new-password' );

		$this->assertFalse( LLMS_Passwordless_Checkout::is_password_not_set( $user_id ) );
		$ids = $this->get_field_ids( 'account' );
		$this->assertContains( 'password_current', $ids );
		$this->assertNotContains( 'llms-password-not-set', $ids );
	}

	/**
	 * A logged-in user who never set a password gets the password form instead of the dashboard on the lost password endpoint.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_display_password_form_when_logged_in() {

		global $wp;

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$wp->query_vars['lost-password'] = '';
		$this->assertTrue( apply_filters( 'llms_display_student_dashboard', true ) );

		update_user_meta( $user_id, LLMS_Passwordless_Checkout::META_KEY, 'yes' );
		$this->assertFalse( apply_filters( 'llms_display_student_dashboard', true ) );

		// Other dashboard endpoints still show the dashboard.
		unset( $wp->query_vars['lost-password'] );
		$this->assertTrue( apply_filters( 'llms_display_student_dashboard', true ) );
	}

	/**
	 * A password change from the WP admin profile screen clears the flag, other profile updates don't.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_profile_update_clears_flag() {

		$user_id = $this->factory->user->create();
		update_user_meta( $user_id, LLMS_Passwordless_Checkout::META_KEY, 'yes' );

		wp_update_user(
			array(
				'ID'         => $user_id,
				'first_name' => 'Changed',
			)
		);
		$this->assertTrue( LLMS_Passwordless_Checkout::is_password_not_set( $user_id ) );

		wp_update_user(
			array(
				'ID'        => $user_id,
				'user_pass' => 'a-new-password',
			)
		);
		$this->assertFalse( LLMS_Passwordless_Checkout::is_password_not_set( $user_id ) );
	}
}
