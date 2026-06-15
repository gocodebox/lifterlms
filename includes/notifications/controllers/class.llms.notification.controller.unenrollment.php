<?php
/**
 * Notification Controller: Unenrollment
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Unenrollment
 *
 * @since [version]
 */
class LLMS_Notification_Controller_Unenrollment extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var string
	 */
	public $id = 'unenrollment';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * The unenrollment action fires with 4 args: $user_id, $product_id, $trigger, $new_status.
	 *
	 * @var int
	 */
	protected $action_accepted_args = 4;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * `llms_user_removed_from_membership_level` was deprecated in 3.37.9 and removed in
	 * 6.0.0; `llms_user_removed_from_membership` is the current hook.
	 *
	 * @var array
	 */
	protected $action_hooks = array(
		'llms_user_removed_from_course',
		'llms_user_removed_from_membership',
	);

	/**
	 * Callback function, called after a user is unenrolled from a course or membership
	 *
	 * @param int    $user_id     WP User ID of the unenrolled user.
	 * @param int    $post_id     WP Post ID of the course or membership.
	 * @param string $trigger     Enrollment trigger that caused the unenrollment.
	 * @param string $new_status  New enrollment status applied to the user.
	 * @return void
	 * @since [version]
	 * @version [version]
	 */
	public function action_callback( $user_id = null, $post_id = null, $trigger = null, $new_status = null ) {

		$this->user_id = $user_id;
		$this->post_id = $post_id;
		$this->course  = llms_get_post( $post_id );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @param string $subscriber Subscriber type string.
	 * @return int|false
	 * @since [version]
	 * @version [version]
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'author':
				$uid = $this->course->get( 'author' );
				break;

			case 'student':
				$uid = $this->user_id;
				break;

			default:
				$uid = false;

		}

		return $uid;

	}

	/**
	 * Get the translatable title for the notification
	 * used on settings screens
	 *
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	public function get_title() {
		return __( 'Unenrollment', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * All subscribers default to disabled ('no') per the feature request:
	 * site administrators must opt in to receive unenrollment notifications.
	 *
	 * @param string $type Notification type id.
	 * @return array
	 * @since [version]
	 * @version [version]
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'basic':
				$options[] = $this->get_subscriber_option_array( 'student', 'no' );
				$options[] = $this->get_subscriber_option_array( 'author', 'no' );
				break;

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'student', 'no' );
				$options[] = $this->get_subscriber_option_array( 'author', 'no' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
				break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Unenrollment::instance();
