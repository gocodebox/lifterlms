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

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmeta_data( $user_id, $post_id ) {
		global $wpdb;
//LLMS_log('get_user_postmeta_data ran useroid=' . $user_id . ' postid=' . $post_id);
		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE user_id = %s and post_id = %d', $user_id, $post_id) );
//LMS_log($results);
		for ($i=0; $i < count($results); $i++) {
			$results[$results[$i]->meta_key] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

}