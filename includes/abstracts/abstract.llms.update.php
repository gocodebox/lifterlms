<?php
/**
 * Handle background database updates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Update {

	/**
	 * Array of callable function names (within the class)
	 * that need to be called to complete the update
	 *
	 * if functions are dependent on each other
	 * the functions themselves should schedule additional actions
	 * via $this->schedule_function() upon completion
	 *
	 * @var  array
	 */
	protected $functions = array();

	/**
	 * Version number of the update
	 * @var  string
	 */
	protected $version = '';

	/**
	 * Constructor
	 * @since    3.0.0
	 * @version  3.3.1
	 */
	public function __construct() {

		if ( ! defined( 'LLMS_BG_UPDATE_LOG' ) ) {
			define( 'LLMS_BG_UPDATE_LOG', true );
		}

		$this->add_actions();

		$progress = $this->get_progress();

		switch ( $progress['status'] ) {

			// start the update
			case 'pending':
				$this->start();
			break;

			case 'finished':
			break;

			// check progress
			case 'running':
			default:
				if ( is_admin() && ! defined( 'DOING_CRON' ) ) {
					$this->output_progress_notice( $progress );
				}
				$this->check_progress( $progress );
			break;

		}

	}

	/**
	 * Add action hooks for each function in the update
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function add_actions() {

		foreach ( $this->functions as $func ) {

			add_action( $this->get_hook( $func ), array( $this, $func ), 10, 1 );

		}

	}

	/**
	 * Checks progress of functions within the update
	 * and triggers completion when finished
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function check_progress( $progress ) {

		if ( ! in_array( 'incomplete', array_values( $progress['functions'] ), true ) ) {

			$this->update_status( 'finished' );
			$this->complete();

		} else {

			$vals = array_count_values( $progress['functions'] );
			$remaining = isset( $vals['incomplete'] ) ? $vals['incomplete'] : 0;
			$completed = isset( $vals['done'] ) ? $vals['done'] : 0;
			$this->log( sprintf( 'Progress: %d completed, %d remaining', $completed, $remaining ) );

		}

	}

	/**
	 * Called when the queue is emptied for the group
	 * Outputs an admin notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function complete() {

		$this->log( sprintf( 'LLMS update tasks completed for version %s', $this->version ) );
		LLMS_Install::update_db_version( $this->version );
		delete_option( 'llms_update_' . $this->version );

	}

	/**
	 * Adds all functions to the queue
	 * @return   void
	 * @since    3.0.0.
	 * @version  3.0.0.
	 */
	private function enqueue() {

		// schedule an action for each function
		foreach ( $this->functions as $func ) {
			$this->schedule_function( $func );
		}

		$this->log( sprintf( 'LLMS update tasks enqueued for version %s', $this->version ) );

	}

	/**
	 * Should be called by each update function when it's finished
	 * updates the progress in the functions array
	 * @param    string     $function  function name
	 * @return   void
	 * @since    3.0.0
	 * @version  3.3.1
	 */
	protected function function_complete( $function ) {

		$progress = $this->get_progress();
		$progress['functions'][ $function ] = 'done';
		update_option( 'llms_update_' . $this->version, $progress );
		$this->log( sprintf( '%s::%s() is complete', get_class( $this ), $function ) );

	}

	/**
	 * Get the name of the action hook for a given function
	 * @param    string     $function  name of the function
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function get_hook( $function ) {
		return 'llms_update_' . $this->version . '_function_' . $function;
	}

	/**
	 * Get data about the progress of an update
	 * @return   array
	 * @since    3.0.0
	 * @version  3.3.1
	 */
	private function get_progress() {

		$default = array(
			'status' => 'pending',
			'functions' => array_fill_keys( $this->functions, 'incomplete' ),
		);

		return get_option( 'llms_update_' . $this->version, $default );

	}

	/**
	 * Schedules a function
	 * @param    string     $func  function name / callable
	 * @param    assay      $args  array of arguments to pass to the function
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	protected function schedule_function( $func, $args = array() ) {
		$this->log( sprintf( 'function `%s()` scheduled with arguments: %s', $func, json_encode( $args ) ) );
		wc_schedule_single_action( time(), $this->get_hook( $func ), $args, 'llms_update_' . $this->version );
	}

	/**
	 * Logs the start of the queue
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function start() {
		$this->log( sprintf( 'LLMS update tasks started for version %s', $this->version ) );
		$this->update_status( 'running' );
		$this->enqueue();
	}

	/**
	 * Update the progress data to a new status
	 * @param    string     $status  new status
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function update_status( $status ) {

		$p = $this->get_progress();
		$p['status'] = $status;
		update_option( 'llms_update_' . $this->version, $p );

	}

	/**
	 * Log data related to the queue
	 * @param    string     $msg  message to log
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	protected function log( $msg ) {

		if ( defined( 'LLMS_BG_UPDATE_LOG' ) && LLMS_BG_UPDATE_LOG ) {
			llms_log( $msg, 'updater' );
		}

	}


	/**
	 * Output a LifterLMS Admin Notice displaying the progress of the background updates
	 * @param    array     $progress  progress array from $this->get_progress()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	private function output_progress_notice( $progress ) {

		$id = 'llms_db_update_notice_' . $this->version;

		if ( LLMS_Admin_Notices::has_notice( $id ) ) {
			LLMS_Admin_Notices::delete_notice( $id );
		}

		$vals = array_count_values( $progress['functions'] );
		$val = isset( $vals['done'] ) ? $vals['done'] : 0;
		$max = count( $progress['functions'] );
		$width = $val ? ( $val / $max ) * 100 : 0;
		$html = '
			<p>' . sprintf( __( 'LifterLMS Database Upgrade %s Progress Report', 'lifterlms' ), $this->version ) . '</p>
			<div style="background:#efefef;height:18px;margin:0.5em 0;"><div style="background:#ef476f;display:block;height:18px;width:' . $width . '%;"><span style="padding:0 0.5em;color:#fff;">' . $width . '%</span></div></div>
			<p><em>' . sprintf( __( 'This completion percentage is an estimate, please be patient and %sclick here%s for more information.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-database-updates/#upgrade-progress-report" target="_blank">', '</a>' ) . '</em></p>
		';

		LLMS_Admin_Notices::add_notice( $id, array(
			'dismissible' => false,
			'flash' => true,
			'html' => $html,
			'type' => 'info',
		) );

	}

}
