<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Convert LifterLMS Tables to CSVs as a background process
 * @since    [version]
 * @version  [version]
 */
class LLMS_Processor_Table_To_Csv extends LLMS_Abstract_Processor {

	/**
	 * Unique identifier for the processor
	 * @var  string
	 */
	protected $id = 'table_to_csv';

	/**
	 * WP Cron Hook for scheduling the bg process
	 * @var  string
	 */
	private $schedule_hook = 'llms_table_to_csv';

	/**
	 * Action triggered to queue queries needed to generate the CSV
	 * @param    string     $handler  LLMS_Table Handler name
	 * @param    int        $user_id  WP User ID of the user who initiated the export
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function dispatch_generation( $handler, $user_id ) {

		$this->log( sprintf( 'csv generation dispatched for table %s', $handler ) );

		// cancel process in case it's currently running
		$this->cancel_process();

		$table = $this->get_handler( $handler );

		if ( $table ) {

			// set the user to be the initiating user so the table will have the correct data
			wp_set_current_user( $user_id );

			$args = array(
				'_processor' => array(
					'file' => LLMS_TMP_DIR . $table->get_export_file_name() . '.csv',
					'handler' => get_class( $table ),
					'user_id' => $user_id,
				),
				'per_page' => 250,
				'page' => 1,
			);

			$table->get_results( $args );

			while ( $args['page'] <= $table->get_max_pages() ) {

				$this->push_to_queue( $args );
				$args['page']++;

			}

			// save queue and dispatch the process
			$this->save()->dispatch();

			$this->log( sprintf( 'csv generation started for table %s', $handler ) );

		} else {

			$this->log( sprintf( 'handler %s does not exist', $handler ) );

		}

	}

	private function get_handler( $handler ) {

		if ( 0 !== strpos( $handler, 'LLMS_Table_' ) ) {
			$handler = 'LLMS_Table_' . $handler;
		}

		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/class.llms.admin.reporting.php';
		LLMS_Admin_Reporting::includes();

		if ( class_exists( $handler ) ) {
			return new $handler();
		}

		return false;

	}

	/**
	 * Initializer
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	protected function init() {

		// for the cron
		add_action( $this->schedule_hook, array( $this, 'dispatch_generation' ), 10, 2 );

		// for LifterLMS actions which trigger recalculation
		$this->actions = array(
			'llms_table_generate_csv' => array(
				'arguments' => 1,
				'callback' => 'schedule_generation',
				'priority' => 10,
			),
		);

	}

	/**
	 * Schedule the generation of a CSV
	 * This will schedule an event that will setup the queue of items for the background process
	 * @param    int     $table  instance of an LLMS_Table
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function schedule_generation( $table ) {

		$this->log( sprintf( 'csv generation triggered for table %s', $table->get_handler() ) );

		$args = array( $table->get_handler(), get_current_user_id() );

		if ( ! wp_next_scheduled( $this->schedule_hook, $args ) ) {

			wp_schedule_single_event( time(), $this->schedule_hook, $args );
			$this->log( sprintf( 'csv generation scheduled for table %s', $table->get_handler() ) );

		}

	}


	/**
	 * Execute calculation for each item in the queue until all students
	 * in the course have been polled
	 * Stores the data in the postmeta table to be accessilbe via LLMS_Course
	 * @param    array     $args  query arguments passed to LLMS_Table
	 * @return   boolean      	  true to keep the item in the queue and process again
	 *                            false to remove the item from the queue
	 * @since    [version]
	 * @version  [version]
	 */
	public function task( $args ) {

		$this->log( sprintf( 'csv generation task started for table %s', $args['_processor']['handler'] ) );
		$this->log( $args );

		$table = $this->get_handler( $args['_processor']['handler'] );
		if ( ! $table ) {
			$this->log( sprintf( 'csv generation task failed for table %s (Handler not found)', $args['_processor']['handler'] ) );
			return false;
		}

		$fh = @fopen( $args['_processor']['file'], 'a+' );
		if ( ! $fh ) {
			$this->log( sprintf( 'csv generation task failed for table %s (file not opened)', $args['_processor']['handler'] ) );
			return false;
		}

		// set the user to be the initiating user so the table will have the correct data
		wp_set_current_user( $args['_processor']['user_id'] );

		$delimiter = apply_filters( 'llms_table_to_csv_processor_delimiter', ',', $this, $args );

		$rows = $table->get_export( $args );
		foreach ( $rows as $row ) {
			fputcsv( $fh, $row, $delimiter );
		}

		fclose( $fh );

		if ( $table->is_last_page() ) {

			$mailer = LLMS()->mailer()->get_email( 'table_to_csv' );
			$mailer->add_recipient( $args['_processor']['user_id'] );

			$mailer->add_attachment( $args['_processor']['file'] );

			$mailer->set_subject( sprintf( esc_html__( 'Your %1$s export file from %2$s', 'lifterlms' ), $table->get_title(), get_bloginfo( 'name' ) ) );
			$mailer->set_body( __( 'Please find the attached CSV file.', 'lifterlms' ) );

			// log when wp_mail fails
			if ( ! $mailer->send() ) {
				$this->log( sprintf( 'error sending csv email for table %s', $args['_processor']['handler'] ) );
			} else {
				unlink( $args['_processor']['file'] );
			}

		}

		return false;

	}

}

return new LLMS_Processor_Table_To_CSV();
