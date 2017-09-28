<?php
/**
 * LifterLMS Post Instructors Interface
 * @since    3.13.0
 * @version  3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface LLMS_Interface_Post_Instructors {

	/**
	 * Retrieve an instance of the Post Instructors model
	 * @return   obj
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function instructors();

	/**
	 * Retrieve course instructor information
	 * @param    boolean    $exclude_hidden  if true, excludes hidden instructors from the return array
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_instructors( $exclude_hidden = false );

	/**
	 * Save instructor information
	 * @param    array      $instructors  array of course instructor information
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function set_instructors( $instructors = array() );

}
