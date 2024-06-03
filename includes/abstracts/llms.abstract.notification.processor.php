<?php
/**
 * LifterLMS Notification Background Processor Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.8.0
 * @version 6.10.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Notification Background Processor abstract class
 *
 * @since 3.8.0
 * @since 3.38.0 Modified return of `dispatch()` override to return the return value of the parent method.
 */
abstract class LLMS_Abstract_Notification_Processor extends WP_Background_Process {

	/**
	 * Enables event logging
	 *
	 * @var boolean
	 */
	private $enable_logging = true;

	/**
	 * Constructor
	 *
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function __construct() {

		parent::__construct();

		if ( ! defined( 'LLMS_NOTIFICATIONS_LOGGING' ) ) {
			define( 'LLMS_NOTIFICATIONS_LOGGING', true );
		}

		$this->enable_logging = ( defined( 'LLMS_NOTIFICATIONS_LOGGING' ) && LLMS_NOTIFICATIONS_LOGGING );

	}

	/**
	 * Called when queue is emptied and action is complete
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function complete() {

		$this->log( sprintf( 'Processing for %s finished', $this->action ) );
		parent::complete();

	}

	/**
	 * Starts the queue.
	 *
	 * @since 3.8.0
	 * @since 3.38.0 Added return from parent method.
	 * @since 6.10.1 Fixed malformed sprintf when logging dispatch errors.
	 *
	 * @return array|WP_Error Response from `wp_remote_post()`.
	 */
	public function dispatch() {

		$this->log(
			sprintf(
				'Dispatching %s',
				$this->action
			)
		);

		$dispatched = parent::dispatch();

		if ( is_wp_error( $dispatched ) ) {
			$this->log(
				sprintf(
					'Unable to dispatch %1$s: %2$s',
					$this->action,
					$dispatched->get_error_message()
				)
			);
		}

		return $dispatched;

	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 *
	 * Overridden to enable the "force" option to work, replaces "exit" with "return"
	 * so that we can redirect and manually call the cronjob
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}
		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}
		$this->handle();
	}

	/**
	 * Returns true if the processor is running
	 *
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function is_processing() {
		return ( false === $this->is_queue_empty() );
	}

	/**
	 * Log event data to an update file when logging enabled
	 *
	 * @param    mixed $data  data to log
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function log( $data ) {

		if ( $this->enable_logging ) {
			llms_log( $data, 'notifications' );
		}

	}

}
