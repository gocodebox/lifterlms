<?php
/**
 * LifterLMS Membership Model
 * @since    3.0.0
 * @version  3.13.0
 *
 * @property  $auto_enroll  (array)  Array of course IDs users will be autoenrolled in upon successfull enrollment in this membership
 * @property  $instructors  (array)  Course instructor user information
 * @property  $restriction_redirect_type  (string)  What type of redirect action to take when content is restricted by this membership [none|membership|page|custom]
 * @property  $redirect_page_id  (int)  WP Post ID of a page to redirect users to when $restriction_redirect_type is 'page'
 * @property  $redirect_custom_url  (string)  Arbitrary URL to redirect users to when $restriction_redirect_type is 'custom'
 * @property  $restriction_add_notice  (string)  Whether or not to add an on screen message when content is restricted by this membership [yes|no]
 * @property  $restriction_notice  (string)  Notice to display when $restriction_add_notice is 'yes'
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Membership extends LLMS_Post_Model implements LLMS_Interface_Post_Instructors {

	protected $properties = array(
		'auto_enroll' => 'array',
		'instructors' => 'array',
		'redirect_page_id' => 'absint',
		'restriction_add_notice' => 'yesno',
		'restriction_notice' => 'html',
		'restriction_redirect_type' => 'text',
		'redirect_custom_url' => 'text',
	);

	protected $db_post_type = 'llms_membership'; // maybe fix this
	protected $model_post_type = 'membership';

	/**
	 * Retrieve an instance of the Post Instructors model
	 * @return   obj
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function instructors() {
		return new LLMS_Post_Instructors( $this );
	}

	/**
	 * Add courses to autoenrollment by id
	 * @param    array|int     $course_ids  array of course id or course id as int
	 * @return   boolean                    true on success, false on error or if the value in the db is unchanged
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_auto_enroll_courses( $course_ids ) {

		if ( ! is_array( $course_ids ) ) {
			$course_ids = array( $course_ids );
		}

		return $this->set( 'auto_enroll', array_unique( array_merge( $course_ids, $this->get_auto_enroll_courses() ) ) );

	}

	/**
	 * Get an array of the auto enrollment course ids
	 * use a custom function due to the default "get_array" returning an array with an empty string
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_auto_enroll_courses() {
		if ( ! isset( $this->auto_enroll ) ) {
			$courses = array();
		} else {
			$courses = $this->get( 'auto_enroll' );
		}
		return apply_filters( 'llms_membership_get_auto_enroll_courses', $courses, $this );
	}

	/**
	 * Retrieve course instructor information
	 * @param    boolean    $exclude_hidden  if true, excludes hidden instructors from the return array
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_instructors( $exclude_hidden = false ) {

		return apply_filters( 'llms_membership_get_instructors',
			$this->instructors()->get_instructors( $exclude_hidden ),
			$this,
			$exclude_hidden
		);

	}

	/**
	 * Retrieve an instance of the LLMS_Product for this course
	 * @return   obj         instance of an LLMS_Product
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_product() {
		return new LLMS_Product( $this->get( 'id' ) );
	}

	/**
	 * Get an array of student IDs based on enrollment status in the membership
	 * @param    string|array  $statuses  list of enrollment statuses to query by
	 *                                    status query is an OR relationship
	 * @param    integer    $limit        number of results
	 * @param    integer    $skip         number of results to skip (for pagination)
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_students( $statuses = 'enrolled', $limit = 50, $skip = 0 ) {

		return llms_get_enrolled_students( $this->get( 'id' ), $statuses, $limit, $skip );

	}

	/**
	 * Remove a course from auto enrollment
	 * @param    int     $course_id  WP Post ID of the course
	 * @return   bool
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function remove_auto_enroll_course( $course_id ) {
		return $this->set( 'auto_enroll', array_diff( $this->get_auto_enroll_courses(), array( $course_id ) ) );
	}

	/**
	 * Save instructor information
	 * @param    array      $instructors  array of course instructor information
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function set_instructors( $instructors = array() ) {

		return $this->instructors()->set_instructors( $instructors );

	}

}
