<?php
/**
 * Base background processor class
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Abstract_Processor extends WP_Background_Process {

	/**
	 * Prefix
	 * @var string
	 */
	protected $prefix = 'llms';

	/**
	 * Unique identifier for the processor
	 * @var  string
	 */
	protected $id;

	/**
	 * Initializer
	 * Acts as a constructor that extending processors should implement
	 * at the very least should populate the $this->actions array
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function init();

	/**
	 * Inherited from WP_Background Process
	 * Extending classes should implement
	 * this is called for each item pushed into the queue
	 * @param    array    $item  item in the queue
	 * @return   boolean      	 true to keep the item in the queue and process again
	 *                           false to remove the item from the queue
	 * @since    [version]
	 * @version  [version]
	 */
	// abstract protected function task( $item );



	/**
	 * Array of actions that should be watched to trigger
	 * the process(es)
	 * @var  array
	 */
	protected $actions = array();

	/**
	 * Constructor
	 * Initializes and adds actions
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		$this->action .= '_' . $this->id;

		parent::__construct();

		// setup
		$this->init();

		// add trigger actions
		$this->add_actions();

	}

	/**
	 * Add actions defined in $this->actions
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	private function add_actions() {

		foreach ( $this->get_actions() as $action => $data ) {

			$data = wp_parse_args( $data, array(
				'arguments' => 1,
				'priority' => 10,
			) );

			add_action( $action, array( $this, $data['callback'] ), $data['priority'], $data['arguments'] );

		}

	}

	/**
	 * Called when queue is emptied and process is complete
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	protected function complete() {

		parent::complete();
		$this->set_data( 'last_run', time() );

	}

	/**
	 * Retrieve a filtered array of actions to be added by $this->add_acitons
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_actions() {

		return apply_filters( 'llms_data_processor_' . $this->id . '_actions', $this->actions, $this );

	}

	/**
	 * Retrieve data for the current processor that can be used
	 * in future processes
	 * @param    string     $key      if set, return a specific peice of data rather than the whole array
	 * @param    string     $default  when returning a specific piece of data, allows a default value to be passed
	 * @return   array|mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_data( $key = null, $default = '' ) {

		// get the array of processor data
		$all_data = get_option( 'llms_processor_data', array() );

		// get data for current processor
		$data = isset( $all_data[ $this->id ] ) ? $all_data[ $this->id ] : array(
			'last_run' => 0,
		);

		// get a specific piece of data
		if ( $key ) {
			return isset( $data[ $key ] ) ? $data[ $key ] : $default;
		}

		// return all the data
		return $data;

	}


	/**
	 * Log data to the processors log when processors debugging is enabled
	 * @param    mixed     $data  data to log
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	protected function log( $data ) {

		if ( defined( 'LLMS_PROCESSORS_DEBUG' ) && LLMS_PROCESSORS_DEBUG ) {
			llms_log( $data, 'processors' );
		}

	}

	/**
	 * Save data to the database related to the processor
	 * @param    string     $key    keyn ame
	 * @param    mixed     $value  value
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_data( $key, $value ) {

		// get the array of processor data
		$data = $this->get_data();
		$data[ $key ] = $value;

		// merge the current data with all processor data
		$all_data = wp_parse_args( array( $this->id => $data ), get_option( 'llms_processor_data', array() ) );

		// save it
		update_option( 'llms_processor_data', $all_data );

	}

}
