<?php
/**
* Students Metabox for Courses & Memberships
*
* Add & remove students
* @since    3.0.0
* @version  3.13.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Students extends LLMS_Admin_Metabox {

	/**
	 * Capability to check in order to display the metabox to the user
	 * @var    string
	 * @since  3.13.0
	 */
	public $capability = 'view_lifterlms_reports';

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-students';
		$this->title = __( 'Student Management', 'lifterlms' );
		$this->screens = array(
			'course',
			'llms_membership',
		);
		$this->priority = 'default';

	}

	/**
	 * Unused with our custom metabox output
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Custom metabox output function
	 * @return   void
	 * @since    3.0.0
	 * @version  3.4.0
	 */
	public function output() {

		$screen = get_current_screen();

		if ( 'add' === $screen->action ) {

			_e( 'You must publish this post before you can manage students.', 'lifterlms' );

		} else {

			global $post;

			llms_get_template( 'admin/post-types/students.php', array(
				'post_id' => $post->ID,
			) );

		}

	}

}
