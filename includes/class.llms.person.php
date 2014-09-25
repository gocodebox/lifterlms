<?php

/**
* Person base class. 
*
* Class used for instantiating course object
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Person {

	/**
	* person data array
	* @access private
	* @var array
	*/
	protected $_data;

	/**
	* Has data been changed?
	* @access private
	* @var bool
	*/
	private $_changed = false;

	/**
	 * Constructor
	 *
	 * Initializes person data
	 */
	public function __construct() {

		if ( empty( LLMS()->session->person ) ) {

			$this->_data = LLMS()->session->person;

		}

		// When leaving or ending page load, store data
    	add_action( 'shutdown', array( $this, 'save_data' ), 10 );
	}

	/**
	 * save_data function.
	 *
	 * @return void
	 */
	public function save_data() {
		if ( $this->_changed ) {
			$GLOBALS['lifterlms']->session->person = $this->_data;
		}
	}
}