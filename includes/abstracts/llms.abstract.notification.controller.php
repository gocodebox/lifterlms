<?php
/**
 * Notification Controller Abstract
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Abstract_Notification_Controller extends LLMS_Abstract_Options_Data implements LLMS_Interface_Notification_Controller {

	/**
	 * Trigger Identifier
	 * @var  [type]
	 */
	public $id;

	/**
	 * Number of accepted arguments passed to the callback function
	 * @var  integer
	 */
	protected $action_accepted_arguments = 1;

	/**
	 * Action hook used to trigger sending of the notification
	 * @var  string
	 */
	protected $action_hook = '';

	/**
	 * Priority used when adding action hook
	 * @var  integer
	 */
	protected $action_priority = 15;

	/**
	 * WP Post ID associated with the triggering action
	 * @var  null
	 */
	protected $post_id = null;

	/**
	 * Array of subscriptions for the notification
	 * @var  array
	 */
	protected $subscriptions = array();

	/**
	 * Array of supported notification types
	 * @var  array
	 */
	protected $supported_types = array();

	/**
	 * WP User ID associated with the triggering action
	 * @var  null
	 */
	protected $user_id = null;

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 * @param    string     $subscriber  subscriber type string
	 * @return   int|false
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function get_subscriber( $subscriber );

	/**
	 * Get the translateable title for the notification
	 * used on settings screens
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract public function get_title();

	/**
	 * Setup the subscriber options for the notification
	 * @param    string     $type  notification type id
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_subscriber_options( $type );



	/**
	 * Holds singletons for extending classes
	 * @var  array
	 */
	private static $_instances = array();

	/**
	 * Get the singleton instance for the extending class
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	public static function instance() {

		$class = get_called_class();

		if ( ! isset( self::$_instances[ $class ] ) ) {
			self::$_instances[ $class ] = new $class();
		}

		return self::$_instances[ $class ];

	}

	/**
	 * Constrcutor
	 * @since    [version]
	 * @version  [version]
	 */
	private function __construct() {

		$this->add_action();

	}

	/**
	 * Add an action to trigger the notification to send
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	protected function add_action() {

		add_action( $this->action_hook, array( $this, 'action_callback' ), $this->action_accepted_arguments, $this->action_priority );

	}

	private function add_custom_subscriptions( $type ) {
		$option = $this->get_option( $type . '_custom_subscribers' );
		$subscribers = explode( ',', $option );
		foreach ( $subscribers as $subscriber ) {
			$subscriber = trim( $subscriber );
			if ( $subscriber ) {
				$this->subscribe( $subscriber, $type );
			}
		}
	}

	private function add_subscriptions() {

		foreach ( $this->get_supported_types() as $type ) {

			foreach ( $this->get_option( $type . '_subscribers', array() ) as $subscriber_key => $enabled ) {

				if ( 'no' === $enabled ) {
					continue;
				} elseif ( 'custom' === $subscriber_key ) {
					$this->add_custom_subscriptions( $type );
				}

				$subscriber = $this->get_subscriber( $subscriber_key );

				if ( $subscriber ) {

					$this->subscribe( $subscriber, $type );

				}

			}

		}

	}

	public function get_mock_view( $type = 'basic', $subscriber = null, $user_id = null, $post_id = null ) {

		$notification = new LLMS_Notification();
		$notification->set( 'type', $type );
		$notification->set( 'subscriber', $subscriber ? $subscriber : get_current_user_id() );
		$notification->set( 'user_id', $user_id ? $user_id : get_current_user_id() );
		$notification->set( 'post_id', null );
		$notification->set( 'trigger_id', $this->id );

		return LLMS()->notifications()->get_view( $notification );

	}


	/**
	 * Retrieve a prefix for options related to the notification
	 * This overrides the LLMS_Abstract_Options_Data method
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_option_prefix() {
		return sprintf( '%1$snotification_%2$s_', $this->option_prefix, $this->id );
	}

	public function get_subscriber_options( $type ) {
		return apply_filters( 'llms_notification_' . $this->id . '_supported_types', $this->set_subscriber_options( $type ), $this );
	}

	/**
	 * Get a subscriptions array for a specific subscriber
	 * @param    mixed     $subscriber  WP User ID, email address, etc...
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_subscriber_subscriptions( $subscriber ) {
		$subscriptions = $this->get_subscriptions();
		return isset( $subscriptions[ $subscriber ] ) ? $subscriptions[ $subscriber ] : array();
	}

	/**
	 * Retrieve subscribers
	 * @param    [type]     $type  [description]
	 * @return   [type]            [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_subscriptions( $type = null ) {
		return $this->subscriptions;
	}

	/**
	 * Get an array of supported notification types
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_supported_types() {
		return apply_filters( 'llms_notification_' . $this->id . '_supported_types', $this->supported_types, $this );
	}

	/**
	 * Send all the subscriptions
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function send() {

		$this->add_subscriptions();

		foreach ( $this->get_subscriptions() as $subscriber => $types ) {

			foreach ( $types as $type ) {

				$this->send_one( $type, $subscriber );

			}

		}

	}

	/**
	 * Send a notification for a subscriber
	 * @param    string     $type           notification type id
	 * @param    mixed      $subscriber     WP User ID for the subscriber, email address, phone number, etc...
	 * @return   int|false
	 * @since    [version]
	 * @version  [version]
	 */
	private function send_one( $type, $subscriber ) {

		$notification = new LLMS_Notification();
		$id = $notification->create( array(
			'post_id' => $this->post_id,
			'subscriber' => $subscriber,
			'type' => $type,
			'trigger_id' => $this->id,
			'user_id' => $this->user_id,
		) );

		// if sucessful, push to the processor where processing is supported
		if ( $id ) {

			$processor = LLMS()->notifications()->get_processor( $type );
			if ( $processor ) {

				$processor->log( sprintf( 'Queuing %1$s notification ID #%2$d', $type, $id ) );
				$processor->push_to_queue( $id );
				LLMS()->notifications()->schedule_processing( $type );

			}

		}

		return $id;

	}

	/**
	 * Subscribe a user to a notification type
	 * @param    mixed     $subscriber  WP User ID, email address, etc...
	 * @param    string    $type        Identifier for a subscription type eg: basic
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function subscribe( $subscriber, $type ) {

		// prevent unsupported types from being subscribed
		if ( ! $this->supports( $type ) ) {
			return;
		}

		$subscriptions = $this->get_subscriber_subscriptions( $subscriber );

		if ( ! in_array( $type, $subscriptions ) ) {
			array_push( $subscriptions, $type );
		}

		$this->subscriptions[ $subscriber ] = $subscriptions;

	}

	/**
	 * Determine if a given notification type is supported
	 * @param    string     $type  notification type id
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function supports( $type ) {
		return in_array( $type, $this->get_supported_types() );
	}

}
