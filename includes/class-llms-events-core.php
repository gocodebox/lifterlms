<?php
/**
 * Record events triggered by core/wp actions.
 *
 * @package  LifterLMS/Classes
 *
 * @since 3.36.0
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Events_Core class..
 *
 * @since 3.36.0
 */
class LLMS_Events_Core {

	/**
	 * Constructor.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'wp_login', array( $this, 'on_signon' ), 10, 2 );
		add_action( 'clear_auth_cookie', array( $this, 'on_signout' ) );

	}

	/**
	 * Record account.signon event via `wp_login` hook.
	 *
	 * @since 3.36.0
	 *
	 * @param string  $username WP_Users's user_login.
	 * @param WP_User $user User object.
	 * @return LLMS_Event
	 */
	public function on_signon( $username, $user ) {

		return LLMS()->events()->record(
			array(
				'actor_id'     => $user->ID,
				'object_type'  => 'user',
				'object_id'    => $user->ID,
				'event_type'   => 'account',
				'event_action' => 'signon',
			)
		);

	}

	/**
	 * Record an account.signout event via `wp_logout()`
	 *
	 * @since 3.36.0
	 *
	 * @return LLMS_Event
	 */
	public function on_signout() {

		$uid = get_current_user_id();

		return LLMS()->events()->record(
			array(
				'actor_id'     => $uid,
				'object_type'  => 'user',
				'object_id'    => $uid,
				'event_type'   => 'account',
				'event_action' => 'signout',
			)
		);

	}

}

return new LLMS_Events_Core();
