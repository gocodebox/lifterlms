<?php
/**
 * Record events triggered by core/wp actions.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Events_Core class..
 *
 * @since [version]
 */
class LLMS_Events_Core {

	/**
	 * Constructor.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
