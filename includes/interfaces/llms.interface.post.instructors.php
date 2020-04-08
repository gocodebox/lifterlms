<?php
/**
 * LifterLMS Post Instructors Interface
 *
 * @package LifterLMS/Interfaces
 *
 * @since 3.13.0
 * @version 3.13.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Interface_Post_Instructors interface
 *
 * @since 3.13.0
 */
interface LLMS_Interface_Post_Instructors {

	/**
	 * Retrieve an instance of the Post Instructors model
	 *
	 * @since 3.13.0
	 *
	 * @return obj
	 */
	public function instructors();

	/**
	 * Retrieve course instructor information
	 *
	 * @since 3.13.0
	 *
	 * @param boolean $exclude_hidden If true, excludes hidden instructors from the return array.
	 * @return array
	 */
	public function get_instructors( $exclude_hidden = false );

	/**
	 * Save instructor information
	 *
	 * @since 3.13.0
	 *
	 * @param array $instructors Array of course instructor information.
	 * @return array
	 */
	public function set_instructors( $instructors = array() );

}
