<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Controller: Achievement Earned
 *
 * @since    3.8.0
 * @version  3.8.0
 */
class LLMS_Notification_Controller_Achievement_Earned extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'achievement_earned';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var  integer
	 */
	protected $action_accepted_args = 3;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array( 'llms_user_earned_achievement' );

	/**
	 * Callback function, called upon achievement post generation
	 *
	 * @param    int $user_id          WP User ID of the user who earned the achievement
	 * @param    int $achievement_id   WP Post ID of the new achievement post
	 * @param    int $related_post_id  WP Post ID of the post which triggered the achievement to be awarded
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function action_callback( $user_id = null, $achievement_id = null, $related_post_id = null ) {

		$this->user_id         = $user_id;
		$this->post_id         = $achievement_id;
		$this->related_post_id = $related_post_id;

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @param    string $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

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
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_title() {
		return __( 'Achievement Earned', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @param    string $type  notification type id
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'basic':
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				break;

		}

		return $options;

	}

	/**
	 * Determine what types are supported
	 * Extending classes can override this function in order to add or remove support
	 * 3rd parties should add support via filter on $this->get_supported_types()
	 *
	 * @return   array        associative array, keys are the ID/db type, values should be translated display types
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_supported_types() {
		return array(
			'basic' => __( 'Basic', 'lifterlms' ),
		);
	}

}

return LLMS_Notification_Controller_Achievement_Earned::instance();
