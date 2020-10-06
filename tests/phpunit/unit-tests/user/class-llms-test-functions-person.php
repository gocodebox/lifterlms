<?php
/**
 * Tests for user related functions
 *
 * @group LLMS_Student
 * @group functions
 * @group functions_person
 *
 * @since 3.7.0
 * @since 3.8.0 Added tests for `llms_can_user_bypasse_restrictions()`.
 * @since 3.9.0 Added tests for `llms_get_student()`.
 * @since 4.5.0 Added tests for `llms_set_person_auth_cookie()` and `llms_set_user_login_time()`.
 */
class LLMS_Test_Functions_Person extends LLMS_UnitTestCase {

	/**
	 * Test llms_can_user_bypass_restrictions()
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function test_llms_can_user_bypass_restrictions() {

		// allow admins to bypass
		update_option( 'llms_grant_site_access', array( 'administrator' ) );

		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$student = $this->factory->user->create( array( 'role' => 'student' ) );

		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );
		$this->assertFalse( llms_can_user_bypass_restrictions( $student ) );

		$this->assertFalse( llms_can_user_bypass_restrictions( 'fake' ) );

		// pass in a student
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

		// should still work with two roles
		update_option( 'llms_grant_site_access', array( 'administrator', 'editor' ) );
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

	}

	/**
	 * Test llms_get_student
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_llms_get_student() {

		$uid = $this->factory->user->create();

		$this->assertTrue( is_a( llms_get_student( $uid ), 'LLMS_Student' ) );
		$this->assertTrue( is_a( llms_get_student( new WP_User( $uid ) ), 'LLMS_Student' ) );
		$this->assertTrue( is_a( llms_get_student( new LLMS_Student( $uid ) ), 'LLMS_Student' ) );

		$this->assertFalse( is_a( llms_get_student( $uid + 1 ), 'LLMS_Student' ) );
		$this->assertFalse( is_a( llms_get_student( 'string' ), 'LLMS_Student' ) );

	}

	/**
	 * Test llms_set_person_auth_cookie()
	 *
	 * @since 4.5.0
	 *
	 * @expectedDeprecated llms_set_person_auth_cookie
	 *
	 * @return void
	 */
	public function test_llms_set_person_auth_cookie() {
		llms_set_person_auth_cookie( $this->factory->user->create() );
	}

	/**
	 * Test llms_set_user_login_time()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_llms_set_user_login_time() {

		$user = $this->factory->user->create_and_get();

		$date = '2020-03-21 10:32:48';
		llms_tests_mock_current_time( $date );

		llms_set_user_login_time( $user->user_login, $user );

		$this->assertEquals( $date, get_user_meta( $user->ID, 'llms_last_login', true ) );

	}

}
