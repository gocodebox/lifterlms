<?php
/**
 * Tests for LifterLMS Prevemt Concurrent Logins class
 *
 * @group LLMS_Prevent_Concurrent_Logins
 *
 * @since 5.6.0
 */
class LLMS_Test_Prevent_Concurrent_Logins extends  LLMS_UnitTestCase {

	/**
	 * Test maybe_prevent_concurrent_logins().
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_concurrent_logins() {

 		// By default the 'student' role is not allowed to log-in multiple times at once.
		$user_id = $this->factory->user->create( array( 'role' => 'student' ) );
		$session_1 = $this->_log_in( $user_id, time() + DAY_IN_SECONDS );
		$this->assertEquals(
			array(
				$session_1,
			),
			wp_get_all_sessions()
		);

		// First login, nothing to prevent.
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			false,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);

		// Another login.
		$session_2 = $this->_log_in( $user_id, time() + ( 2 * DAY_IN_SECONDS ) );
		$this->assertEquals(
			array(
				$session_1,
				$session_2,
			),
			wp_get_all_sessions()
		);

		// Second login, the first session should be destroyed.
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			true,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);
		$this->assertEquals(
			array( $session_2 ),
			wp_get_all_sessions()
		);

	}

	/**
	 * Test maybe_prevent_concurrent_logins().
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_concurrent_logins_allow_roles() {
		$prevent_option = get_option( 'lifterlms_prevent_concurrent_logins' );
		$roles_option   = get_option( 'lifterlms_prevent_concurrent_logins_roles' );

		// By default the 'student' role is not allowed to log-in multiple times at once.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$session_1 = $this->_log_in( $user_id, time() + DAY_IN_SECONDS );
		$this->assertEquals(
			array(
				$session_1,
			),
			wp_get_all_sessions()
		);

		// First login, nothing to prevent.
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			false,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);

		// Another login.
		$session_2 = $this->_log_in( $user_id, time() + ( 2 * DAY_IN_SECONDS ) );
		$this->assertEquals(
			array(
				$session_1,
				$session_2,
			),
			wp_get_all_sessions()
		);

		// Second login, since we're an allowed role, there's nothing to prevent.
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			false,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);
		$this->assertEquals(
			array(
				$session_1,
				$session_2,
			),
			wp_get_all_sessions()
		);

		// Change the allowed role option to disallow administrators.
		update_option( 'lifterlms_prevent_concurrent_logins_roles', array( 'administrator' ) );

		// Another login.
		$session_3 = $this->_log_in( $user_id, time() + ( 3 * DAY_IN_SECONDS ) );
		$this->assertEquals(
			array(
				$session_1,
				$session_2,
				$session_3,
			),
			wp_get_all_sessions()
		);

		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			true,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);
		$this->assertEquals(
			array(
				$session_3,
			),
			wp_get_all_sessions()
		);

		// Allow current user to login mutiple times via a filter.
		$allow_current_user = function( $allow, $uid ) use ( $user_id ) {
			return $uid === $user_id ? true : $allow;
		};
		add_filter( 'llms_allow_user_concurrent_logins', $allow_current_user, 10, 2 );

		// Another login.
		$session_4 = $this->_log_in( $user_id, time() + ( 4 * DAY_IN_SECONDS ) );
		$this->assertEquals(
			array(
				$session_3,
				$session_4,
			),
			wp_get_all_sessions()
		);
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			false,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);
		$this->assertEquals(
			array(
				$session_3,
				$session_4,
			),
			wp_get_all_sessions()
		);

		remove_filter( 'llms_allow_user_concurrent_logins', $allow_current_user, 10, 2 );

		// Change the allowed role option to an empty array.
		update_option( 'lifterlms_prevent_concurrent_logins_roles', array() );
		// Another login.
		$session_5 = $this->_log_in( $user_id, time() + ( 5 * DAY_IN_SECONDS ) );
		$this->assertEquals(
			array(
				$session_3,
				$session_4,
				$session_5,
			),
			wp_get_all_sessions()
		);
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			false,
			LLMS_Prevent_Concurrent_Logins::instance()->maybe_prevent_concurrent_logins()
		);
		$this->assertEquals(
			array(
				$session_3,
				$session_4,
				$session_5,
			),
			wp_get_all_sessions()
		);

		// Reset.
		update_option( 'lifterlms_prevent_concurrent_logins', $prevent_option );
		update_option( 'lifterlms_prevent_concurrent_logins_roles', $roles_option );

	}

	/**
	 * Test maybe_prevent_concurrent_logins().
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function test_destroy_all_sessions_but_newest() {

		// First login, it's also the newest, I expect it to be kept: 1.
		$user_id   = $this->factory->user->create();
		$session_1 = $this->_log_in( $user_id, time() + ( 1 * DAY_IN_SECONDS ), $first_token );
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			1,
			LLMS_Unit_Test_Util::call_method(
				LLMS_Prevent_Concurrent_Logins::instance(),
				'destroy_all_sessions_but_newest'
			)
		);
		$this->assertEquals(
			array(
				$session_1
			),
			wp_get_all_sessions()
		);

		// Another login.
		$session_2 = $this->_log_in( $user_id, time() + ( 2 * DAY_IN_SECONDS ) );
		$this->assertEquals(
			array(
				$session_1,
				$session_2,
			),
			wp_get_all_sessions()
		);

		// Second login, it's also the newest, I expect it to be kept: 1.
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		$this->assertEquals(
			1,
			LLMS_Unit_Test_Util::call_method(
				LLMS_Prevent_Concurrent_Logins::instance(),
				'destroy_all_sessions_but_newest'
			)
		);
		$this->assertEquals(
			array(
				$session_2,
			),
			wp_get_all_sessions()
		);

		// Now simulate the current session is the oldest.
		wp_destroy_all_sessions( $user_id );
		$session_1 = $this->_log_in( $user_id, time() + ( 1 * DAY_IN_SECONDS ), $first_token );
		$session_2 = $this->_log_in( $user_id, time() + ( 2 * DAY_IN_SECONDS ), $second_token );

		// Make the session 2 the oldest:
		$session_2['login'] = time() - ( 2 * DAY_IN_SECONDS );
		WP_Session_Tokens::get_instance( $user_id )->update(
			$second_token,
			$session_2
		);
		$this->assertEquals(
			array(
				$session_1,
				$session_2,
			),
			wp_get_all_sessions()
		);
		LLMS_Prevent_Concurrent_Logins::instance()->init();
		// I expect the first session (promoted as newest) to be kept.
		$this->assertEquals(
			0,
			LLMS_Unit_Test_Util::call_method(
				LLMS_Prevent_Concurrent_Logins::instance(),
				'destroy_all_sessions_but_newest'
			)
		);
		$this->assertEquals(
			array(
				$session_1,
			),
			wp_get_all_sessions()
		);

	}

	/**
	 * Simulate a log in.
	 *
	 * @since 5.6.0
	 *
	 * @param int    $user_id   WP_User ID.
	 * @param int    $epiration Expiration time.
	 * @param string $token     Passed by reference, the created session token.
	 * @return array Login session.
	 */
	private function _log_in( $user_id, $expiration, &$token = '' ) {

		$manager    = WP_Session_Tokens::get_instance( $user_id );
		$token      = $manager->create( $expiration );
		wp_set_current_user( $user_id );
		$logged_in_cookie = wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token );
		$this->cookies->set( LOGGED_IN_COOKIE, $logged_in_cookie, $expiration + ( 12 * HOUR_IN_SECONDS ), SITECOOKIEPATH, COOKIE_DOMAIN, false, true );
		return $manager->get( $token );

	}

}
