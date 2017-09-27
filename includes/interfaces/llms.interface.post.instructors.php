<?php
/**
 * LifterLMS Post Instructors Interface
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface LLMS_Interface_Post_Instructors {

	/**
	 * Retrieve an instance of the Post Instructors model
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	public function instructors();

	/**
	 * Retrieve course instructor information
	 * @param    boolean    $exclude_hidden  if true, excludes hidden instructors from the return array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_instructors( $exclude_hidden = false );

	/**
	 * Save instructor information
	 * @param    array      $instructors  array of course instructor information
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_instructors( $instructors = array() );

}
