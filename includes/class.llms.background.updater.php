<?php
/**
 * LLMS_Background_Updater
 *
 * @package LifterLMS/Classes
 *
 * @since 3.4.3
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Background database upgrader
 *
 * Process db updates in the background
 *
 * Replaces abstract updater and update classes from 3.4.2 and lower.
 *
 * @since 3.4.3
 */
class LLMS_Background_Updater extends WP_Background_Process {

	/**
	 * Action name
	 *
	 * @var string
	 */
	protected $action = 'llms_bg_updater';

	/**
	 * Enables event logging
	 *
	 * @var boolean
	 */
	private $enable_logging = true;

	/**
	 * Constructor
	 *
	 * @since 3.4.3
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();

		if ( ! defined( 'LLMS_BG_UPDATE_LOG' ) ) {
			define( 'LLMS_BG_UPDATE_LOG', true );
		}

		$this->enable_logging = ( defined( 'LLMS_BG_UPDATE_LOG' ) && LLMS_BG_UPDATE_LOG );

	}

	/**
	 * Called when queue is emptied and action is complete
	 *
	 * @since 3.4.3
	 *
	 * @return void
	 */
	protected function complete() {
		$this->log( 'Update complete' );
		LLMS_Install::update_db_version();
		parent::complete();
	}

	/**
	 * Starts the queue
	 *
	 * @since 3.4.3
	 *
	 * @return void
	 */
	public function dispatch() {

		$dispatched = parent::dispatch();

		if ( is_wp_error( $dispatched ) ) {
			$this->log( sprintf( 'Unable to dispatch updater: %s' ), $dispatched->get_error_message() );
		}

	}

	/**
	 * Retrieve approximate progress of updates in the queue
	 *
	 * @since 3.4.3
	 * @since 3.16.10 Unknown.
	 *
	 * @return int
	 */
	public function get_progress() {

		// If the queue is empty we've already finished.
		if ( $this->is_queue_empty() ) {
			return 0;
		}

		// Get the progress.
		$batch     = $this->get_batch();
		$total     = max( array_keys( $batch->data ) ) + 1;
		$remaining = count( $batch->data );
		if ( ! $total ) {
			return 0;
		}
		return ceil( ( ( $total - $remaining ) / $total ) * 100 );
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
	 * @since 3.4.3
	 *
	 * @return void
	 */
	public function handle_cron_healthcheck() {

		// Background process already running.
		if ( $this->is_process_running() ) {
			return;
		}

		// No data to process.
		if ( $this->is_queue_empty() ) {
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();

	}

	/**
	 * Returns true if the updater is running
	 *
	 * @since 3.4.3
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return ( false === $this->is_queue_empty() );
	}

	/**
	 * Log event data to an update file when logging enabled
	 *
	 * @since 3.4.3
	 *
	 * @param mixed $data Data to log.
	 * @return void
	 */
	public function log( $data ) {

		if ( $this->enable_logging ) {
			llms_log( $data, 'updater' );
		}

	}

	/**
	 * Processes an item in the queue
	 *
	 * @since 3.4.3
	 * @since 3.16.10 Unknown.
	 * @since 5.2.0 Use `llms_get_callable_name()` to log callback.
	 *
	 * @param mixed $callback PHP callable (function name, callable array, etc...).
	 * @return mixed Returns `false` when the callback is complete (removes it from the queue).
	 *               Returns $callback to leave it in the queue.
	 */
	protected function task( $callback ) {

		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';

		$callback_name = llms_get_callable_name( $callback );
		if ( is_callable( $callback ) ) {
			$this->log( sprintf( 'Running %s callback', $callback_name ) );
			if ( call_user_func( $callback ) ) {
				return $callback;
			}
			$this->log( sprintf( 'Finished %s callback', $callback_name ) );
		} else {
			$this->log( sprintf( 'Could not find %s callback', $callback_name ) );
		}

		return false;

	}

	/**
	 * Save queue
	 *
	 * Overwrites parent method to empty `$this->data` following a save.
	 *
	 * This ensures save() can be called multiple times without recording duplicates.
	 *
	 * @since 5.2.0
	 *
	 * @return LLMS_Background_Updater
	 */
	public function save() {

		parent::save();
		// Reset data to avoid duplicates if save() is called more than once.
		$this->data = array();

		return $this;
	}

}
