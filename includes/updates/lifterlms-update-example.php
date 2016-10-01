<?php
/**
 * Example background update class
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Update_100 extends LLMS_Update {

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
	protected $functions = array(
		'function_1',
		'function_2',
		'function_3',
		'function_4',
	);

	/**
	 * Version number of the update
	 * @var  string
	 */
	protected $version = '5.0.0';

	public function function_1() {
		$this->log( 'function_1 started' );
		sleep( 5 );
		$this->function_complete( 'function_1' );
	}

	public function function_2() {
		$this->log( 'function_2 started' );
		sleep( 5 );
		$this->function_complete( 'function_2' );
	}

	public function function_3() {
		$this->log( 'function_3 started' );
		sleep( 5 );
		$this->function_complete( 'function_3' );
	}

	public function function_4() {
		$this->log( 'function_4 started' );
		sleep( 5 );
		$this->function_complete( 'function_4' );
	}

}

return new LLMS_Update_100;
