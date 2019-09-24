<?php
/**
 * LifterLMS Notifications Management and Interface
 * Loads and allows interactions with notification views, controllers, and processors
 *
 * @package LifterLMS/Notifications/Classes
 *
 * @since 3.8.0
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Notifications Management and Interface
 * Loads and allows interactions with notification views, controllers, and processors
 *
 * @since 3.8.0
 * @since 3.24.0 Unknown.
 * @since 3.36.1 Record notifications as read during the `wp_print_footer_scripts` hook.
 */
class LLMS_Notifications {

	/**
	 * Singleton instance
	 *
	 * @var  LLMS_Notifications
	 */
	protected static $_instance = null;

	/**
	 * Controller instances
	 *
	 * @var  LLMS_Abstract_Notification_Controller[]
	 */
	private $controllers = array();

	/**
	 * Notifications being displayed on this page load.
	 *
	 * @var array
	 */
	private $displayed = array();

	/**
	 * Background processor instances
	 *
	 * @var  LLMS_Abstract_Notification_Processor[]
	 */
	private $processors = array();

	/**
	 * Array of processors needing to be dispatched on shutdown
	 *
	 * @var  string[]
	 */
	private $processors_to_dispatch = array();

	/**
	 * [string $view_classname => string $trigger ]
	 *
	 * @var  string[]
	 */
	private $views = array();

	/**
	 * Main Instance
	 *
	 * @return    LLMS_Notifications
	 * @since     3.8.0
	 * @version   3.8.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 * @since 3.22.0 Unknown.
	 * @since 3.36.1 Record basic notifications as read during `wp_print_footer_scripts`.
	 *
	 * @return void
	 */
	private function __construct() {

		$this->load();
		add_action( 'wp', array( $this, 'enqueue_basic' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'mark_displayed_basics_as_read' ) );
		add_action( 'shutdown', array( $this, 'dispatch_processors' ) );

	}

	/**
	 * On shutdown, check for processors that have items in the queue that need to be saved
	 * save & dispatch the background process
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function dispatch_processors() {

		foreach ( $this->processors_to_dispatch as $key => $name ) {

			$processor = $this->get_processor( $name );
			if ( $processor ) {
				unset( $this->processors_to_dispatch[ $key ] );
				$processor->save()->dispatch();
			}
		}

	}

	/**
	 * Enqueue basic notifications for onscreen display
	 *
	 * @since 3.22.0
	 * @since 3.36.1 Don't automatically mark notifications as read.
	 *
	 * @return void
	 */
	public function enqueue_basic() {

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		// get 5 most recent new notifications for the current user
		$query = new LLMS_Notifications_Query(
			array(
				'per_page'   => 5,
				'statuses'   => 'new',
				'types'      => 'basic',
				'subscriber' => $user_id,
			)
		);

		$this->displayed = $query->get_notifications();

		// push to JS
		LLMS_Frontend_Assets::enqueue_inline_script(
			'llms-queued-notifications',
			'window.llms = window.llms || {};window.llms.queued_notifications = ' . json_encode( $this->displayed ) . ';'
		);

	}

	/**
	 * Record notifications as read.
	 *
	 * Ensures that notifications are not missed due to redirects that happen after `wp`.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function mark_displayed_basics_as_read() {

		if ( $this->displayed ) {
			foreach ( $this->displayed as $notification ) {
				$notification->set( 'status', 'read' );
			}
		}

	}

	/**
	 * Get the directory path for core notification classes
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function get_directory() {
		return LLMS_PLUGIN_DIR . 'includes/notifications/';
	}

	/**
	 * Get a single controller instance
	 *
	 * @param    string $controller  trigger id (eg: lesson_complete)
	 * @return   LLMS_Abstract_Notification_Controller|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_controller( $controller ) {
		if ( isset( $this->controllers[ $controller ] ) ) {
			return $this->controllers[ $controller ];
		}
		return false;
	}

	/**
	 * Get loaded controllers
	 *
	 * @return   LLMS_Abstract_Notification_Controller[]
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_controllers() {
		return $this->controllers;
	}

	/**
	 * Retrieve a single processor instance
	 *
	 * @param    string $processor  name of the processor (eg: email)
	 * @return   LLMS_Abstract_Notification_Processor|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_processor( $processor ) {
		if ( isset( $this->processors[ $processor ] ) ) {
			return $this->processors[ $processor ];
		}
		return false;
	}

	/**
	 * Get loaded processors
	 *
	 * @return   LLMS_Abstract_Notification_Processor[]
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_processors() {
		return $this->processors;
	}

	/**
	 * Retrieve a view instance of a notification
	 *
	 * @param    LLMS_Notification $notification  instance of an LLMS_Notification
	 * @return   LLMS_Abstract_Notification_View|false
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	public function get_view( $notification ) {

		$trigger = $notification->get( 'trigger_id' );

		if ( in_array( $trigger, $this->views ) ) {
			$views = array_flip( $this->views );
			$class = $views[ $trigger ];
			$view  = new $class( $notification );
			return $view;
		}

		return false;

	}

	/**
	 * Get the classname for the view of a given notification based off it's trigger
	 *
	 * @param    string $trigger  trigger id (eg: lesson_complete).
	 * @param    string $prefix   default = 'LLMS'
	 * @return   string
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	private function get_view_classname( $trigger, $prefix = null ) {

		$prefix = $prefix ? $prefix : 'LLMS';
		$name   = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $trigger ) ) );
		return sprintf( '%1$s_Notification_View_%2$s', $prefix, $name );

	}

	/**
	 * Load all notifications
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	private function load() {

		$triggers = array(
			'achievement_earned',
			'certificate_earned',
			'course_complete',
			'course_track_complete',
			'enrollment',
			'lesson_complete',
			'manual_payment_due',
			'payment_retry',
			'purchase_receipt',
			'quiz_failed',
			'quiz_graded',
			'quiz_passed',
			'section_complete',
			'student_welcome',
			'subscription_cancelled',
		);

		foreach ( $triggers as $name ) {

			$this->load_controller( $name );
			$this->load_view( $name );

		}

		$processors = array(
			'email',
		);

		foreach ( $processors as $name ) {
			$this->load_processor( $name );
		}

		/**
		 * Third party notifications can hook into this action
		 * Use $this->load_view(), $this->load_controller(), & $this->load_processor()
		 * to load notifications into the class and be auto-called by the APIs herein
		 */
		do_action( 'llms_notifications_loaded', $this );

	}

	/**
	 * Load and initialize a single controller
	 *
	 * @param    string $trigger  trigger id (eg: lesson_complete)
	 * @param    string $path     full path to the controller file, allows third parties to load external controllers
	 * @return   boolean              true if the controller is added and loaded, false otherwise
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function load_controller( $trigger, $path = null ) {

		// default path for core views
		if ( ! $path ) {
			$path = $this->get_directory() . 'controllers/class.llms.notification.controller.' . $this->name_to_file( $trigger ) . '.php';
		}

		if ( file_exists( $path ) ) {

			$this->controllers[ $trigger ] = require_once $path;
			return true;

		}

		return false;

	}

	/**
	 * Load a single processor
	 *
	 * @param    string $type   processor type id
	 * @param    string $path   optional path (for allowing 3rd party processor loading)
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function load_processor( $type, $path = null ) {

		// default path for core processors
		if ( ! $path ) {
			$path = $this->get_directory() . 'processors/class.llms.notification.processor.' . $type . '.php';
		}

		if ( file_exists( $path ) ) {

			$this->processors[ $type ] = require_once $path;
			return true;

		}

		return false;
	}

	/**
	 * Load a single view
	 *
	 * @param    string $trigger  trigger id (eg: lesson_complete)
	 * @param    string $path     full path to the view file, allows third parties to load external views
	 * @param    string $prefix   Classname prefix. Defaults to "LLMS". Can be used by 3rd parties to adjust
	 *                            the prefix in accordance with the projects standards.
	 * @return   boolean              true if the view is added and loaded, false otherwise
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	public function load_view( $trigger, $path = null, $prefix = null ) {

		// default path for core views
		if ( ! $path ) {
			$path = $this->get_directory() . 'views/class.llms.notification.view.' . $this->name_to_file( $trigger ) . '.php';
		}

		if ( file_exists( $path ) ) {

			require_once $path;
			$this->views[ $this->get_view_classname( $trigger, $prefix ) ] = $trigger;
			return true;

		}

		return false;

	}

	/**
	 * Convert a trigger name to a filename string
	 * Eg lesson_complete to lesson.complete
	 *
	 * @param    string $name  trigger name
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function name_to_file( $name ) {
		return str_replace( '_', '.', $name );
	}

	/**
	 * Schedule a processor to dispatch its queue on shutdown
	 *
	 * @param    string $type  processor name/type (eg: email)
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function schedule_processing( $type ) {

		if ( ! in_array( $type, $this->processors_to_dispatch ) ) {

			$this->processors_to_dispatch[] = $type;

		}

	}

}
