<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once LLMS_PLUGIN_DIR . 'includes/libraries/wp-background-processing/wp-async-request.php';
require_once LLMS_PLUGIN_DIR . 'includes/libraries/wp-background-processing/wp-background-process.php';

/**
 * LifterLMS Background Updater
 * Process db updates in the background
 *
 * Replaces abstract updater and update classes from 3.4.2 and lower
 *
 * @since    3.4.3
 * @version  3.16.10
 */
class LLMS_Background_Updater extends WP_Background_Process {

	/**
	 * action name
	 *
	 * @var  string
	 */
	protected $action = 'llms_bg_updater';

	/**
	 * Enables event logging
	 *
	 * @var  boolean
	 */
	private $enable_logging = true;

	/**
	 * Constructor
	 *
	 * @since    3.4.3
	 * @version  3.4.3
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
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function complete() {
		$this->log( 'Update complete' );
		LLMS_Install::update_db_version();
		LLMS_Install::update_notice();
		parent::complete();
	}

	/**
	 * Starts the queue
	 *
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
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
	 * @return   int
	 * @since    3.4.3
	 * @version  3.16.10
	 */
	public function get_progress() {

		// if the queue is empty we've already finished
		if ( $this->is_queue_empty() ) {
			return 0;
		}

		// get the progress
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
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
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
	 * Returns true if the updater is running
	 *
	 * @return   boolean
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	public function is_updating() {
		return ( false === $this->is_queue_empty() );
	}

	/**
	 * Log event data to an update file when logging enabled
	 *
	 * @param    mixed $data  data to log
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	public function log( $data ) {

		if ( $this->enable_logging ) {
			llms_log( $data, 'updater' );
		}

	}

	/**
	 * Processes an item in the queue
	 *
	 * @param    string $callback  name of the callback function to execute
	 * @return   mixed                 false removes item from the queue
	 *                                 truthy (callback function name) leaves it in the queue for further processing
	 * @since    3.4.3
	 * @version  3.16.10
	 */
	protected function task( $callback ) {

		include_once dirname( __FILE__ ) . '/functions/llms.functions.updates.php';

		if ( is_callable( $callback ) ) {
			$this->log( sprintf( 'Running %s callback', $callback ) );
			if ( call_user_func( $callback ) ) {
				// $this->log( sprintf( '%s callback will rerun', $callback ) );
				return $callback;
			}
			$this->log( sprintf( 'Finished %s callback', $callback ) );

		} else {
			$this->log( sprintf( 'Could not find %s callback', $callback ) );
		}

		return false;

	}

}
