<?php
/**
 * LifterLMS Notification Abstract
 * @since    ??
 * @version  ??
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Notification_Manager implements LLMS_Interface_Notification_Manager {

	/**
	 * Notification ID
	 * @var  string
	 */
	public $id = '';

	/**
	 * Accepted numeber of arguments that the action callback will recieve
	 * @var  integer
	 */
	protected $accepted_args = 1;

	/**
	 * Action called to trigger this notification
	 * should be something triggered by do_action()
	 * @var  string
	 */
	protected $action = '';

	/**
	 * Priority to fire the callback with
	 * @var  integer
	 */
	protected $priority = 20;

	/**
	 * Array of notification subscribers and their subscription preferences
	 * Subscribers can be added at runtime in addition to those with database desribed subscriptions
	 * @example     Array keys are user ids and array values are arrays of the handlers that user subscribes to
	 * 				array(
	 *              	123 => array( 'basic' ),
	 *              	456 => array( 'basic', 'email' )
	 *              )
	 * @var  array
	 */
	private $subscribers = array();

	/**
	 * Replaces a given merge code with real information
	 * @param    string     $code  unprepared merge code
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function merge_code( $code );

	/**
	 * Set the content of the notification's body
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @param    string     $type           id of the LifterLMS Notification Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_body( $subscriber_id = null, $type = null );

	/**
	 * Define the LifterLMS Notification Handlers that will handle the notification
	 * core handlers are 'basic' and 'email'
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_handlers();

	/**
	 * Set the url of the notification's icon
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @param    string     $type           id of the LifterLMS Notification Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_icon( $subscriber_id = null, $type = null );

	/**
	 * Determine merge codes that can be used with this notification
	 * the merge codes should be returned without the merge prefix & suffix
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_merge_codes();

	/**
	 * Set the content of the notification's title
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @param    string     $type           id of the LifterLMS Notification Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_title( $subscriber_id = null, $type = null );

	/**
	 * Constructor
	 * @since    ??
	 * @version  ??
	 */
	public function __construct() {

		add_action( $this->action, array( $this, 'callback' ), $this->priority, $this->accepted_args );
		$this->subscribers = $this->get_subscribers();

	}

	/**
	 * Add a one-time subscription for a user
	 * @param    int        $user_id  WP User ID
	 * @param    string     $type     id of the LifterLMS Notification Handler
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_subscription( $user_id, $type ) {

		$this->subscribers[ $user_id ][] = $type;

	}

	/**
	 * Retrieve the merged and filtered body content for the notification
	 * @param    int        $subscriber_id  WP User ID
	 * @param    string     $type           id of the LLMS_Notification_Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_body( $subscriber_id = null, $type = null ) {

		$body = $this->merge( $this->set_body( $subscriber_id, $type ), $subscriber_id );

		return apply_filters( $this->get_filter( 'get_body' ), $body, $this, $type );

	}

	/**
	 * Normalize filter names used for apply_filters()
	 * @param    string     $filter  filter name appended to the default filter prefix
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_filter( $filter ) {
		return 'llms_notification_' . $this->id . '_' . $filter;
	}

	/**
	 * Retrieve the handlers enabled for this notification
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_handlers() {
		return apply_filters( $this->get_filter( 'get_handlers' ), $this->set_handlers(), $this );
	}

	// protected function get_html( $subscriber_id = null, $type = null ) {
	// 	ob_start();
	// 	?>
	// 		<div class="llms-notification llms-notification--' + n.type + '" data-id="' + n.id + '">' );

	// 		if ( n.icon ) {
	// 			<img class="llms-notification-icon" alt="' + n.title + '" src="' + n.icon + '">
	// 		}

	// 		if ( n.title ) {
	// 			<h4 class="llms-notification-title">' + n.title + '</h4>
	// 		}

	// 		if ( n.body ) {
	// 			<div class="llms-notification-body">' + n.body + '</div>
	// 		}

	// 		<i class="llms-notification-dismiss fa fa-times-circle" aria-hidden="true"></i>

	// 	<?php
	// 	return apply_filters( $this->get_filter( 'get_html' ), ob_get_clean(), $this );

	// }

	/**
	 * Retrieve the merged and filtered icon url for the notification
	 * @param    int        $subscriber_id  WP User ID
	 * @param    string     $type           id of the LLMS_Notification_Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_icon( $subscriber_id = null, $type = null ) {

		return apply_filters( $this->get_filter( 'get_icon' ), $this->set_icon( $subscriber_id, $type ), $this, $type );

	}

	/**
	 * Retrieve an array of dimensions (pixels) for the notifications icon
	 * @param    string     $type  id of the LLMS_Notification_Handler
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_icon_dimensions( $type = null) {
		return apply_filters( $this->get_filter( 'get_icon_dimensions' ), array( 48, 48 ), $this, $type );
	}

	/**
	 * Retrieve an array of merge codes available for this notification
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_merge_codes() {
		return apply_filters( $this->get_filter( 'get_merge_codes' ), $this->set_merge_codes(), $this );
	}

	/**
	 * Retrieve subscribers for the notification optionally filtered by the handler
	 * @param    string     $type  id of the LLMS_Notification_Handler
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_subscribers( $type = null ) {

		// @todo: query database to find subscribers


		// add existing subscriptions which may have been added at runtime
		$subscribers = $this->subscribers;

		// filter by type
		if ( $type ) {

			foreach ( $subscribers as $uid => $types ) {

				if ( ! in_array( $type, $types ) ) {
					unset( $subscribers[ $uid ] );
				}

			}

		}

		return apply_filters( $this->get_filter( 'get_subscribers' ), $subscribers, $this );

	}

	/**
	 * Retrieve the merged and filtered title content for the notification
	 * @param    int        $subscriber_id  WP User ID
	 * @param    string     $type           id of the LLMS_Notification_Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_title( $subscriber_id = null, $type = null ) {

		$title = $this->merge( $this->set_title( $subscriber_id, $type ), $subscriber_id );

		return apply_filters( $this->get_filter( 'get_title' ), $title, $this, $type );

	}

	/**
	 * Passes the notification to each handler for handling
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function handle() {

		foreach ( $this->get_handlers() as $handler ) {

			$obj = LLMS()->notifications()->get_handler( $handler );
			if ( is_subclass_of( $obj, 'LLMS_Notification_Handler' ) ) {
				$obj->handle( $this );
			}

		}

	}

	/**
	 * Replaces merge all merge fields with actual data
	 * @param    string     $content        unmerged conent
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function merge( $content, $subscriber_id = null ) {

		$codes = $this->get_merge_codes();

		$raw = array();
		$merged = array();

		foreach ( $codes as $code ) {

			$raw[] = $this->prepare_merge_code( $code );
			$merged[] = $this->merge_code( $code, $subscriber_id );

		}

		return str_replace( $raw, $merged, $content );

	}

	/**
	 * Adds the merge prefix and suffix to a merge code before merging actual data
	 * @param    string     $code  merge code
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function prepare_merge_code( $code ) {
		return self::MERGE_CODE_PREFIX . $code . self::MERGE_CODE_SUFFIX;
	}

}
