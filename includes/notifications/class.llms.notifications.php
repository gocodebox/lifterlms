<?php
/**
 * LifterLMS Notifications Management and Interface
 *
 * @package LifterLMS/Notifications/Classes
 *
 * @since 3.8.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Notifications Management and Interface
 *
 * Loads and allows interactions with notification views, controllers, and processors.
 *
 * @since 3.8.0
 * @since 3.24.0 Unknown.
 * @since 3.36.1 Record notifications as read during the `wp_print_footer_scripts` hook.
 * @since 3.38.0 Updated processor scheduling for increased performance and reliability.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_Notifications::dispatch_processors()` method
 *              - `LLMS_Notifications::$_instance` property
 */
class LLMS_Notifications {

	use LLMS_Trait_Singleton;

	/**
	 * Controller instances
	 *
	 * @var LLMS_Abstract_Notification_Controller[]
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
	 * @var LLMS_Abstract_Notification_Processor[]
	 */
	private $processors = array();

	/**
	 * Array of processors needing to be dispatched on shutdown
	 *
	 * @var string[]
	 */
	private $processors_to_dispatch = array();

	/**
	 * [string $view_classname => string $trigger ]
	 *
	 * @var string[]
	 */
	private $views = array();

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 * @since 3.22.0 Unknown.
	 * @since 3.36.1 Record basic notifications as read during `wp_print_footer_scripts`.
	 * @since 3.38.0 Schedule processors using an async scheduled action.
	 * @since 6.0.0 Do not load / enqueue basic notifications on the admin panel.
	 *              Removed the deprecated `llms_processors_async_dispatching` filter hook.
	 *
	 * @return void
	 */
	private function __construct() {

		$this->load();

		if ( ! is_admin() ) {
			add_action( 'wp', array( $this, 'enqueue_basic' ) );
			add_action( 'wp_print_footer_scripts', array( $this, 'mark_displayed_basics_as_read' ) );
		}

		add_action( 'shutdown', array( $this, 'schedule_processors_dispatch' ) );
		add_action( 'llms_dispatch_notification_processor_async', array( $this, 'dispatch_processor_async' ) );

	}

	/**
	 * Async callback to dispatch processors
	 *
	 * Locates the processor by ID and dispatches it for processing.
	 *
	 * The trigger hook `llms_dispatch_notification_processor_async` is called by the action scheduler library.
	 *
	 * @since 3.38.0
	 *
	 * @see llms_dispatch_notification_processor_async
	 *
	 * @param string $id Processor ID.
	 * @return array|WP_Error
	 */
	public function dispatch_processor_async( $id ) {

		$processor = $this->get_processor( $id );
		if ( $processor ) {
			return $processor->dispatch();
		}

		// Translators: %s = Processor ID.
		return new WP_Error( 'invalid-processor', sprintf( __( 'The processor "%s" does not exist.', 'lifterlms' ), $id ) );

	}

	/**
	 * Enqueue basic notifications for onscreen display.
	 *
	 * @since 3.22.0
	 * @since 3.36.1 Don't automatically mark notifications as read.
	 * @since 3.38.0 Use `wp_json_decode()` in favor of `json_decode()`.
	 * @since 4.4.0 Use `LLMS_Assets::enqueue_inline()` in favor of deprecated `LLMS_Frontend_Assets::enqueue_inline_script()`.
	 * @since 7.1.0 Improve notifications query performance by not calculating unneeded found rows.
	 *
	 * @return void
	 */
	public function enqueue_basic() {

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		// Get 5 most recent new notifications for the current user.
		$query = new LLMS_Notifications_Query(
			array(
				'per_page'      => 5,
				'statuses'      => 'new',
				'types'         => 'basic',
				'subscriber'    => $user_id,
				'no_found_rows' => true,
			)
		);

		$this->displayed = $query->get_notifications();

		// Push to JS.
		llms()->assets->enqueue_inline(
			'llms-queued-notifications',
			'window.llms.queued_notifications = ' . wp_json_encode( $this->displayed ) . ';',
			'footer'
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
	 * @since 3.8.0
	 *
	 * @return string
	 */
	private function get_directory() {
		return LLMS_PLUGIN_DIR . 'includes/notifications/';
	}

	/**
	 * Get a single controller instance
	 *
	 * @since 3.8.0
	 *
	 * @param string $controller Trigger id (eg: lesson_complete).
	 * @return LLMS_Abstract_Notification_Controller|false
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
	 * @since 3.8.0
	 *
	 * @return LLMS_Abstract_Notification_Controller[]
	 */
	public function get_controllers() {
		return $this->controllers;
	}

	/**
	 * Retrieve a single processor instance
	 *
	 * @since 3.8.0
	 *
	 * @param string $processor Name of the processor (eg: email).
	 * @return LLMS_Abstract_Notification_Processor|false
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
	 * @since 3.8.0
	 *
	 * @return LLMS_Abstract_Notification_Processor[]
	 */
	public function get_processors() {
		return $this->processors;
	}

	/**
	 * Retrieve a view instance of a notification
	 *
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 * @since 3.38.0 Use strict comparison.
	 *
	 * @param LLMS_Notification $notification Notification instance.
	 * @return LLMS_Abstract_Notification_View|false
	 */
	public function get_view( $notification ) {

		$trigger = $notification->get( 'trigger_id' );

		if ( in_array( $trigger, $this->views, true ) ) {
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
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param string $trigger Trigger id (eg: lesson_complete).
	 * @param string $prefix  Default = 'LLMS'.
	 * @return string
	 */
	private function get_view_classname( $trigger, $prefix = null ) {

		$prefix = $prefix ? $prefix : 'LLMS';
		$name   = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $trigger ) ) );
		return sprintf( '%1$s_Notification_View_%2$s', $prefix, $name );

	}

	/**
	 * Load all notifications
	 *
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 * @since 5.2.0 Added 'upcoming_payment_reminder'.
	 *
	 * @return void
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
			'upcoming_payment_reminder',
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
		 * Run an action after all core notification classes are loaded.
		 *
		 * Third party notifications can hook into this action.
		 * Use `load_view()`, `load_controller()`, and `load_processor()` methods
		 * to load notifications into the class and be auto-called by the core APIs.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Notifications $this Instance of the notifications singleton.
		 */
		do_action( 'llms_notifications_loaded', $this );

	}

	/**
	 * Load and initialize a single controller
	 *
	 * @since 3.8.0
	 *
	 * @param string $trigger Trigger id (eg: lesson_complete).
	 * @param string $path    Full path to the controller file, allows third parties to load external controllers.
	 * @return boolean `true` if the controller is added and loaded, `false` otherwise.
	 */
	public function load_controller( $trigger, $path = null ) {

		// Default path for core views.
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
	 * @since 3.8.0
	 *
	 * @param string $type Processor type id.
	 * @param string $path Optional path (for allowing 3rd party processor loading).
	 * @return boolean
	 */
	public function load_processor( $type, $path = null ) {

		// Default path for core processors.
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
	 * Validate trigger and load its view.
	 *
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @param  string $trigger Trigger id (eg: lesson_complete).
	 * @param  string $path    Full path to the view file, allows third parties to load external views.
	 * @param  string $prefix  Classname prefix. Defaults to "LLMS". Can be used by 3rd parties to adjust
	 *                         the prefix in accordance with the projects standards.
	 * @return boolean `true` if the view is added and loaded, `false` otherwise.
	 */
	public function load_view( $trigger, $path = null, $prefix = null ) {

		// Default path for core views.
		if ( ! $path ) {
			$path = $this->get_directory() . 'views/class.llms.notification.view.' . $this->name_to_file( $trigger ) . '.php';
		}

		if ( file_exists( $path ) ) {

			if ( ! is_null( $prefix ) ) {
				require_once $path;
			}
			$this->views[ $this->get_view_classname( $trigger, $prefix ) ] = $trigger;
			return true;
		}

		return false;
	}

	/**
	 * Convert a trigger name to a filename string
	 *
	 * EG: "lesson_complete" to "lesson.complete".
	 *
	 * @since 3.8.0
	 *
	 * @param string $name Trigger name.
	 * @return string
	 */
	private function name_to_file( $name ) {
		return str_replace( '_', '.', $name );
	}

	/**
	 * Schedule a processor to dispatch its queue on shutdown
	 *
	 * @since 3.8.0
	 * @since 3.38.0 Use strict comparisons.
	 *
	 * @param string $id Processor ID (eg: email).
	 * @return void
	 */
	public function schedule_processing( $id ) {

		if ( ! in_array( $id, $this->processors_to_dispatch, true ) ) {

			$this->processors_to_dispatch[] = $id;

		}

	}

	/**
	 * Check for processors that have items in the queue
	 *
	 * For any found processors, saves their queue and schedules them to be processes via a scheduled event.
	 *
	 * @since 3.38.0
	 *
	 * @return array Array containing information about the scheduled processors.
	 *               The array keys will be the processor ID and the values will be the timestamp of the event or a WP_Error object.
	 */
	public function schedule_processors_dispatch() {

		$scheduled = array();

		if ( $this->processors_to_dispatch ) {

			foreach ( $this->processors_to_dispatch as $key => $id ) {

				// Retrieve the processor.
				$processor = $this->get_processor( $id );

				// Remove it from the list of processors to dispatch.
				unset( $this->processors_to_dispatch[ $key ] );

				$scheduled[ $id ] = $processor ? $this->schedule_single_processor( $processor, $id ) : new WP_Error(
					'invalid-processor',
					// Translators: %s = Processor ID.
					sprintf( __( 'The processor "%s" does not exist.', 'lifterlms' ), $id )
				);

			}
		}

		return $scheduled;

	}

	/**
	 * Save pending batches and schedule the async dispatching of a processor.
	 *
	 * @since 3.38.0
	 *
	 * @param LLMS_Abstract_Notification_Processor $processor Notification processor object.
	 * @param string                               $id        Processor ID.
	 * @return int|WP_Error Timestamp of the scheduled event or an error object.
	 */
	protected function schedule_single_processor( $processor, $id ) {

		$hook = 'llms_dispatch_notification_processor_async';
		$args = array( $id );

		// Save items in the queue.
		$processor->save();

		// Check if there's already a scheduled event.
		$timestamp = as_next_scheduled_action( $hook, $args );

		// If there's no event scheduled already, schedule one.
		if ( ! $timestamp ) {

			$timestamp = llms_current_time( 'timestamp', 1 );

			// Error encountered scheduling the event.
			if ( ! as_schedule_single_action( $timestamp, $hook, $args ) ) {
				$timestamp = new WP_Error(
					'schedule-error',
					// Translators: %s = Processor ID.
					sprintf( __( 'There was an error dispatching the "%s" processor.', 'lifterlms' ), $id )
				);
			}
		}

		return $timestamp;

	}

}
