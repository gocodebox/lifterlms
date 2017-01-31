<?php
/**
 * Update the LifterLMS Database to 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Update_330 extends LLMS_Update {

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
		'update_parent_relationships',
	);

	/**
	 * Version number of the update
	 * @var  string
	 */
	protected $version = '3.3.0';

	/**
	 * Rename meta keys for parent section and parent course relationships for
	 * all LifterLMS Lessons and Sections
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.1
	 */
	public function update_parent_relationships() {

		$this->log( 'update_parent_relationships started' );

		global $wpdb;

		// update parent course key for courses and lessons
		$wpdb->query( "UPDATE {$wpdb->postmeta} AS m
			 JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
			 SET m.meta_key = '_llms_parent_course'
			 WHERE m.meta_key = '_parent_course'
			   AND ( p.post_type = 'lesson' OR p.post_type = 'section' );"
		);

		// update parent section key for lessons
		$wpdb->query( "UPDATE {$wpdb->postmeta} AS m
			 JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
			 SET m.meta_key = '_llms_parent_section'
			 WHERE m.meta_key = '_parent_section'
			   AND p.post_type = 'lesson';"
		);

		$this->function_complete( 'update_parent_relationships' );

	}

}

return new LLMS_Update_330;
