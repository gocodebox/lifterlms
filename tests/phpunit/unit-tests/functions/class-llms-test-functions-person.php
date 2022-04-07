<?php
/**
 * Tests for LifterLMS Core Functions
 *
 * @group functions_person
 * @group functions
 * @group LLMS_Student
 *
 * @since 3.8.0
 * @since 3.9.0 Add tests for `llms_get_student()`.
 * @since 3.9.0 Add tests for `llms_get_usernames_blacklist()`.
 * @since 5.0.0 Add tests for `llms_set_password_reset_cookie()` and `llms_parse_password_reset_cookie()`.
 * @since 6.0.0 Removed testing of the removed `llms_set_person_auth_cookie()` function.
 */
class LLMS_Test_Functions_Person extends LLMS_UnitTestCase {

	/**
	 * Test llms_can_user_bypass_restrictions()
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function test_llms_can_user_bypass_restrictions() {

		// Allow admins to bypass.
		update_option( 'llms_grant_site_access', array( 'administrator' ) );

		$admin      = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$student    = $this->factory->user->create( array( 'role' => 'student' ) );

		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );
		$this->assertFalse( llms_can_user_bypass_restrictions( $student ) );

		$this->assertFalse( llms_can_user_bypass_restrictions( 'fake' ) );

		// Pass in a student.
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

		// Should still work with two roles.
		update_option( 'llms_grant_site_access', array( 'administrator', 'editor' ) );
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

		// Test restrictions against a post.
		update_option( 'llms_grant_site_access', array( 'administrator', 'editor', 'instructor' ) );
		$course_id = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 1 ) );
		$course    = llms_get_post( $course_id );
		$lesson    = $course->get_lessons()[0];
		$quiz      = $lesson->get_quiz();
		$tests     = array( $course_id, $lesson->get( 'id' ), $quiz->get( 'id' ) );

		foreach ( $tests as $post_id ) {

			$this->assertTrue( llms_can_user_bypass_restrictions( $admin, $post_id ) );
			$this->assertFalse( llms_can_user_bypass_restrictions( $instructor, $post_id ) );
			$this->assertFalse( llms_can_user_bypass_restrictions( $student, $post_id ) );

		}

		$course->set_instructors( array(
			array(
				'id' => $instructor,
			)
		) );

		foreach ( $tests as $post_id ) {

			$this->assertTrue( llms_can_user_bypass_restrictions( $admin, $post_id ) );
			$this->assertTrue( llms_can_user_bypass_restrictions( $instructor, $post_id ) );
			$this->assertFalse( llms_can_user_bypass_restrictions( $student, $post_id ) );

		}

	}

	/**
	 * Test llms_get_minimum_password_strength_name().
	 *
	 * @since Unknown.
	 *
	 * @return void
	 */
	public function test_llms_get_minimum_password_strength_name() {

		// Default value.
		$this->assertEquals( 'strong', llms_get_minimum_password_strength_name() );

		// Existing options.
		$this->assertEquals( 'strong', llms_get_minimum_password_strength_name( 'strong' ) );
		$this->assertEquals( 'medium', llms_get_minimum_password_strength_name( 'medium' ) );
		$this->assertEquals( 'weak', llms_get_minimum_password_strength_name( 'weak' ) );
		$this->assertEquals( 'very weak', llms_get_minimum_password_strength_name( 'very-weak' ) );

		// Custom option.
		$this->assertEquals( 'fake', llms_get_minimum_password_strength_name( 'fake' ) );

	}

	/**
	 * Test llms_get_student
	 *
	 * @since 3.9.0
	 *
	 * @return void
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
	 * Test llms_get_usernames_blocklist() function.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_usernames_blocklist() {

		$this->assertTrue( is_array( llms_get_usernames_blocklist() ) );
		$this->assertTrue( in_array( 'admin', llms_get_usernames_blocklist(), true ) );

	}

	/**
	 * Test llms_parse_password_reset_cookie() when no cookie is set.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_parse_password_reset_cookie_no_cookie() {

		$this->cookies->unset( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );

		$res = llms_parse_password_reset_cookie();
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_no_cookie', $res );

	}

	/**
	 * Test llms_parse_password_reset_cookie() when the cookie is malformed.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_parse_password_reset_cookie_bad_cookie() {

		llms_set_password_reset_cookie( 'fake' );

		$res = llms_parse_password_reset_cookie();
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_cookie', $res );

	}

	/**
	 * Test llms_parse_password_reset_cookie() when the user doesn't exist.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_parse_password_reset_cookie_bad_user() {

		$uid = $this->factory->user->create() + 1; // Fake user.

		llms_set_password_reset_cookie( sprintf( '%d:fake', $uid ) );

		$res = llms_parse_password_reset_cookie();
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_key', $res );

	}

	/**
	 * Test llms_parse_password_reset_cookie() when the key is invalid.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_parse_password_reset_cookie_bad_key() {

		$uid = $this->factory->user->create();

		llms_set_password_reset_cookie( sprintf( '%d:fake', $uid ) );

		$res = llms_parse_password_reset_cookie();
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_invalid_key', $res );

	}

	/**
	 * Test llms_parse_password_reset_cookie() when the key is expired.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_parse_password_reset_cookie_expired_key() {

		add_filter( 'password_reset_expiration', '__return_zero' );

		$user = $this->factory->user->create_and_get();
		$key  = get_password_reset_key( $user );

		llms_set_password_reset_cookie( sprintf( '%1$d:%2$s', $user->ID, $key ) );

		$res = llms_parse_password_reset_cookie();

		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_password_reset_expired_key', $res );

		remove_filter( 'password_reset_expiration', '__return_zero' );

	}

	/**
	 * Test llms_parse_password_reset_cookie() success.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_parse_password_reset_cookie_success() {

		$user = $this->factory->user->create_and_get();
		$key  = get_password_reset_key( $user );

		llms_set_password_reset_cookie( sprintf( '%1$d:%2$s', $user->ID, $key ) );

		$res = llms_parse_password_reset_cookie();

		$this->assertEquals( $user->user_login, $res['login'] );
		$this->assertEquals( $key, $res['key'] );

	}

	/**
	 * Test llms_set_password_reset_cookie() under default circumstances
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_set_password_reset_cookie() {

		$name = sprintf( 'wp-resetpass-%s', COOKIEHASH );
		$this->assertTrue( llms_set_password_reset_cookie( 'reset_pass' ) );

		$this->assertArrayHasKey( $name, $this->cookies->get_all() );
		$this->assertEquals( array(
			'value'    => 'reset_pass',
			'expires'  => 0,
			'path'     => '',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => false,
			'httponly' => true,
		), $this->cookies->get( $name ) );

	}

	/**
	 * Test that the llms_set_password_reset_cookie fails.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_set_password_reset_cookie_fail() {

		// Mock failure.
		$this->cookies->expect_error();

		$this->assertFalse( llms_set_password_reset_cookie( 'cookieval' ) );

	}

	/**
	 * Test llms_set_password_reset_cookie() when no value is set (expires the cookie).
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_set_password_reset_cookie_no_val() {

		$this->assertTrue( llms_set_password_reset_cookie() );
		$data = $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );
		$this->assertEmpty( $data['value'] );
		$this->assertTrue( time() > $data['expires'] );

	}

	/**
	 * Test llms_set_password_reset_cookie() sets the cookie path properly.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_set_password_reset_cookie_path() {

		// Regular URL path.
		$_SERVER['REQUEST_URI'] = '/dashboard/lost-password';

		$this->assertTrue( llms_set_password_reset_cookie( 'reset_pass' ) );
		$data = $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );
		$this->assertEquals( '/dashboard/lost-password', $data['path'] );

		// With query string.
		$_SERVER['REQUEST_URI'] = '/dashboard/lost-password?var1=1&var2=2';

		$this->assertTrue( llms_set_password_reset_cookie( 'reset_pass' ) );
		$data = $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );
		$this->assertEquals( '/dashboard/lost-password', $data['path'] );

		// Reset.
		$_SERVER['REQUEST_URI'] = '';

	}

	/**
	 * Test llms_set_password_reset_cookie() sets a secure cookie when SSL is enabled on the site.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_set_password_reset_cookie_ssl() {

		// Mock is_ssl() to return `true`.
		$_SERVER['HTTPS'] = 'ON';

		$this->assertTrue( llms_set_password_reset_cookie( 'reset_pass' ) );
		$data = $this->cookies->get( sprintf( 'wp-resetpass-%s', COOKIEHASH ) );
		$this->assertTrue( $data['secure'] );

		// Reset.
		unset( $_SERVER['HTTPS'] );

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

	/**
	 * Test llms_validate_user() with validation errors.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_validate_user_errors() {

		$ret = llms_validate_user( array() );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms-form-missing-required', $ret );

	}

	/**
	 * Test llms_validate_user() success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_validate_user_success() {

		$user = $this->factory->user->create_and_get( array( 'first_name' => 'Kyle' ) );

		wp_set_current_user( $user->ID );

		$this->assertTrue( llms_validate_user( array( 'first_name' => 'Not Kyle' ), 'checkout' ) );

		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( 'Kyle', $user->first_name );

	}

}
