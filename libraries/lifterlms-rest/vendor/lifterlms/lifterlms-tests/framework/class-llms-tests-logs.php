<?php
/**
 * Log handler to expose logs to test cases
 *
 * @since 1.14.0
 * @version 1.14.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Tests_Logs class.
 *
 * @since 1.14.0
 */
class LLMS_Tests_Logs {

	/**
	 * Holds an array of log messages.
	 *
	 * @var array
	 */
	protected $logs = array();

	/**
	 * Constructor
	 *
	 * @since 1.14.0
	 *
	 * @return
	 */
	public function __construct() {
		add_filter( 'llms_log_message', array( $this, 'store' ), 999, 2 );
	}

	public function tear_down() {
		remove_filter( 'llms_log_message', array( $this, 'store' ), 999 );
	}

	public function clear( $handle = 'llms' ) {
		$this->logs = array();
	}

	public function get( $handle = 'llms' ) {
		return isset( $this->logs[ $handle ] ) ? $this->logs[ $handle ] : array();
	}

	public function get_all() {
		return $this->logs;
	}

	public function store( $msg, $handle ) {

		if ( ! $this->get( $handle ) ) {
			$this->logs[ $handle ] = array();
		}

		$this->logs[ $handle ][] = $msg;

		return $msg;

	}

}


