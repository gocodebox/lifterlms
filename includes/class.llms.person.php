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
    	add_action( 'wp_login', array( $this, 'set_user_login_timestamp' ), 10, 2);
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

	public function set_user_login_timestamp ($user_login, $user) {
		$now = current_time( 'timestamp' );
		update_user_meta($user->ID, 'llms_last_login', $now);
	}


	/**
	 * Get data about a specific users memberships
	 * @param  int $user_id user id
	 * @return array / array of objects containing details about users memberships
	 */
	public function get_user_memberships_data( $user_id ) {

		$memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

		$r = array();

		if($memberships) {

			foreach($memberships as $membership_id) {

				$info = $this->get_user_postmeta_data( $user_id, $membership_id );

				if( $info ) {

					$r[$membership_id] = $info;

				}


			}

		}

		return $r;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmeta_data( $user_id, $post_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE user_id = %s and post_id = %d', $user_id, $post_id) );

		for ($i=0; $i <= count($results); $i++) {
			$results[$results[$i]->meta_key] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmetas_by_key( $user_id, $meta_key ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE user_id = %s and meta_key = "%s" ORDER BY updated_date DESC', $user_id, $meta_key ) );

		for ($i=0; $i <= count($results); $i++) {
			$results[$results[$i]->post_id] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

	public function get_permissions() {
	}

}
