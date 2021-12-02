<?php
/**
 * LLMS_Prevent_Concurrent_Logins class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Prevent_Concurrent_Logins class.
 *
 * @since [version]
 */
class LLMS_Prevent_Concurrent_Logins {

	use LLMS_Trait_Singleton;

	/**
	 * Array of sessions for the current user.
	 *
	 * @var array
	 */
	private $user_sessions;

	/**
	 * Current user ID.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Private Constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function __construct() {

		if ( get_option( 'lifterlms_prevent_concurrent_logins', false ) &&
				! empty( get_option( 'lifterlms_prevent_concurrent_logins_roles', array() ) ) ) {
			add_action( 'init', array( $this, 'init' ) );
		}

	}

	/**
	 * Initialize.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function init() {

		$this->user_id = get_current_user_id();
		if ( empty( $this->user_id ) ) {
			return;
		}

		$this->user_sessions = wp_get_all_sessions();

		// No logged in user or current user has only one active session: nothing to do.
		if ( count( $this->user_sessions ) < 2 ) {
			return;
		}

		// Current user doesn't have any restricted role: nothing to do.
		if ( empty( array_intersect( get_userdata( $this->user_id )->roles, get_option( 'lifterlms_prevent_concurrent_logins_roles', array() ) ) ) ) {
			return;
		}

		/**
		 * Filters whether or not allowing a specific user to have concurrent sessions.
		 *
		 * @since [version]
		 *
		 * @param bool $allow   Whether or not the user should be allowed to have concurrent sessions.
		 * @param int  $user_id WP_User ID of the current use.
		 */
		if ( (bool) apply_filters( 'llms_allow_user_concurrent_logins', false, $user_id ) ) {
			return;
		}

		// Current user doesn't have any restricted role: nothing to do.
		if ( empty( array_intersect( get_userdata( $this->user_id )->roles, get_option( 'lifterlms_prevent_concurrent_logins_roles', array() ) ) ) ) {
			return;
		}

		$this->destroy_all_sessions_but_newest();

	}

	/**
	 * Maybe prevent current logins by destroying all sessions but the newest.
	 *
	 * @since [version]
	 *
	 * @return int 0 if the kept session is the current one, 1 otherwise.
	 */
	private function destroy_all_sessions_but_newest() {

		$is_current_session_newest_session = $this->current_user_current_session_login_time() === $this->current_user_current_session_login_time();

		$is_current_session_newest_session
			?
			wp_destroy_other_sessions()
			:
			wp_destroy_current_session();

		return (int) $is_current_session_newest_session;

	}

	/**
	 * Retrieve current session for the current user.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	private function current_user_current_session_login_time() {

		$sessions = WP_Session_Tokens::get_instance( $this->user_id );
		return $sessions->get( wp_get_session_token() )['login'];

	}

	/**
	 * Retrieve newest session login time for the current user.
	 *
	 * The bigger the login time is the newest the session is.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	private function current_user_newest_session_loin_time() {

		return max( array_column( $this->user_sessions, 'login' ) );

	}

}

return LLMS_Prevent_Concurrent_Logins::instance();
