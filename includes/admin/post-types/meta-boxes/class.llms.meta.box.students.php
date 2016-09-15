<?php
/**
* Students Metabox for Courses & Memberships
*
* Add & remove
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Students extends LLMS_Admin_Metabox {


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

	public static function get_students_data( $post, $page = 1 ) {

		$object = false;

		$limit = 20;
		$skip = ( $page - 1 ) * $limit;
		$query_limit = $limit + 1; // get an extra result to check if we have more pages

		$students = array(
			'more' => false,
			'page' => intval( $page ),
			'students' => array(),
		);

		$results = llms_get_enrolled_students( $post->ID, array_keys( llms_get_enrollment_statuses() ), $query_limit, $skip );

		// if we have more results than the true limit, we have another page to grab
		if ( count( $results ) > $limit ) {
			$students['more'] = true;
			// remove the extra result
			array_pop( $results );
		}

		$students['students'] = $results;

		return $students;

	}

	/**
	 * Custom metabox output function
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function output() {

		$screen = get_current_screen();

		if ( 'add' === $screen->action ) {

			_e( 'You must publish this post before you can manage students.', 'lifterlms' );

		} else {

			global $post;

			$page = isset( $_GET['llms-students'] ) ? $_GET['llms-students'] : 1;

			llms_get_template( 'admin/post-types/students.php', array(
				'page' => $page,
				'post_id' => $post->ID,
				'students' => self::get_students_data( $post, $page ),
			) );

		}

	}

}
