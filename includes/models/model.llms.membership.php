<?php
/**
 * LifterLMS Membership Model
 *
 * @package  LifterLMS/Models
 * @since    3.0.0
 * @version  3.36.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Membership model.
 *
 * @since 3.0.0
 * @since 3.30.0 Added optional argument to `add_auto_enroll_courses()` method.
 * @since 3.32.0 Added `get_student_count()` method.
 * @since 3.36.3 Added `get_categories()`, `get_tags()` and `toArrayAfter()` methods.
 *
 * @property $auto_enroll (array) Array of course IDs users will be autoenrolled in upon successful enrollment in this membership
 * @property $instructors (array) Course instructor user information
 * @property $restriction_redirect_type (string) What type of redirect action to take when content is restricted by this membership [none|membership|page|custom]
 * @property $redirect_page_id (int) WP Post ID of a page to redirect users to when $restriction_redirect_type is 'page'
 * @property $redirect_custom_url (string) Arbitrary URL to redirect users to when $restriction_redirect_type is 'custom'
 * @property $restriction_add_notice (string) Whether or not to add an on screen message when content is restricted by this membership [yes|no]
 * @property $restriction_notice (string) Notice to display when $restriction_add_notice is 'yes'
 * @property $sales_page_content_page_id (int) WP Post ID of the WP page to redirect to when $sales_page_content_type is 'page'
 * @property $sales_page_content_type (string) Sales page behavior [none,content,page,url]
 * @property $sales_page_content_url (string) Redirect URL for a sales page, when $sales_page_content_type is 'url'
 */
class LLMS_Membership
extends LLMS_Post_Model
implements LLMS_Interface_Post_Instructors
		 , LLMS_Interface_Post_Sales_Page {

	/**
	 * Membership post meta.
	 *
	 * @var array
	 */
	protected $properties = array(
		'auto_enroll'                => 'array',
		'instructors'                => 'array',
		'redirect_page_id'           => 'absint',
		'restriction_add_notice'     => 'yesno',
		'restriction_notice'         => 'html',
		'restriction_redirect_type'  => 'text',
		'redirect_custom_url'        => 'text',
		'sales_page_content_page_id' => 'absint',
		'sales_page_content_type'    => 'string',
		'sales_page_content_url'     => 'string',
	);

	/**
	 * Database post type.
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_membership';

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model_post_type = 'membership';

	/**
	 * Add courses to autoenrollment by id
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Added optional `$replace` argument.
	 * @version 3.30.0
	 *
	 * @param array|int $course_ids Array of course id or course id as int.
	 * @param bool      $replace Optional. Default `false`. When `true`, replaces all existing courses with `$course_ids`, when false merges `$course_ids` with existing courses.
	 * @return boolean true on success, false on error or if the value in the db is unchanged.
	 */
	public function add_auto_enroll_courses( $course_ids, $replace = false ) {

		// allow a single course_id to be passed in.
		if ( ! is_array( $course_ids ) ) {
			$course_ids = array( $course_ids );
		}

		// add existing courses to the array if replace is false.
		if ( ! $replace ) {
			$course_ids = array_merge( $course_ids, $this->get_auto_enroll_courses() );
		}

		return $this->set( 'auto_enroll', array_unique( $course_ids ) );

	}

	/**
	 * Get an array of the auto enrollment course ids
	 *
	 * Uses a custom function due to the default "get_array" returning an array with an empty string
	 *
	 * @since 3.0.0
	 *
	 * @return array
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
	 * Retrieve membership categories.
	 *
	 * @since 3.36.3
	 *
	 * @param array $args Array of args passed to `wp_get_post_terms()`.
	 * @return array
	 */
	public function get_categories( $args = array() ) {
		return wp_get_post_terms( $this->get( 'id' ), 'membership_cat', $args );
	}

	/**
	 * Retrieve course instructor information
	 *
	 * @since 3.13.0
	 *
	 * @param boolean $exclude_hidden If true, excludes hidden instructors from the return array.
	 * @return array
	 */
	public function get_instructors( $exclude_hidden = false ) {

		return apply_filters(
			'llms_membership_get_instructors',
			$this->instructors()->get_instructors( $exclude_hidden ),
			$this,
			$exclude_hidden
		);

	}

	/**
	 * Retrieve an instance of the LLMS_Product for this course
	 *
	 * @since 3.3.0
	 * @return LLMS_Product
	 */
	public function get_product() {
		return new LLMS_Product( $this->get( 'id' ) );
	}

	/**
	 * Get the URL to a WP Page or Custom URL when sales page redirection is enabled
	 *
	 * @since 3.20.0
	 *
	 * @return string
	 */
	public function get_sales_page_url() {

		$type = $this->get( 'sales_page_content_type' );
		switch ( $type ) {

			case 'page':
				$url = get_permalink( $this->get( 'sales_page_content_page_id' ) );
				break;

			case 'url':
				$url = $this->get( 'sales_page_content_url' );
				break;

			default:
				$url = get_permalink( $this->get( 'id' ) );

		}

		return apply_filters( 'llms_membership_get_sales_page_url', $url, $this, $type );
	}

	/**
	 * Retrieve the number of enrolled students in the membership.
	 *
	 * @since 3.32.0
	 *
	 * @return int
	 */
	public function get_student_count() {

		$query = new LLMS_Student_Query(
			array(
				'post_id'  => $this->get( 'id' ),
				'statuses' => array( 'enrolled' ),
				'per_page' => 1,
			)
		);

		return $query->found_results;

	}

	/**
	 * Get an array of student IDs based on enrollment status in the membership
	 *
	 * @since    3.0.0
	 *
	 * @param string|string[] $statuses List of enrollment statuses to query by status query is an OR relationship.
	 * @param int             $limit Number of results.
	 * @param int             $skip Number of results to skip (for pagination).
	 * @return array
	 */
	public function get_students( $statuses = 'enrolled', $limit = 50, $skip = 0 ) {

		return llms_get_enrolled_students( $this->get( 'id' ), $statuses, $limit, $skip );

	}

	/**
	 * Retrieve membership tags.
	 *
	 * @since 3.36.3
	 *
	 * @param array $args Array of args passed to `wp_get_post_terms()`.
	 * @return array
	 */
	public function get_tags( $args = array() ) {
		return wp_get_post_terms( $this->get( 'id' ), 'membership_tag', $args );
	}

	/**
	 * Determine if sales page redirection is enabled
	 *
	 * @since 3.20.0
	 *
	 * @return string
	 */
	public function has_sales_page_redirect() {
		$type = $this->get( 'sales_page_content_type' );
		return apply_filters( 'llms_membership_has_sales_page_redirect', in_array( $type, array( 'page', 'url' ) ), $this, $type );
	}

	/**
	 * Retrieve an instance of the Post Instructors model
	 *
	 * @since 3.13.0
	 *
	 * @return LLMS_Post_Instructors
	 */
	public function instructors() {
		return new LLMS_Post_Instructors( $this );
	}

	/**
	 * Remove a course from auto enrollment
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id WP_Post ID of the course.
	 * @return bool
	 */
	public function remove_auto_enroll_course( $course_id ) {
		return $this->set( 'auto_enroll', array_diff( $this->get_auto_enroll_courses(), array( $course_id ) ) );
	}

	/**
	 * Save instructor information
	 *
	 * @since 3.13.0
	 *
	 * @param array $instructors Array of course instructor information.
	 * @return array
	 */
	public function set_instructors( $instructors = array() ) {

		return $this->instructors()->set_instructors( $instructors );

	}

	/**
	 * Add data to the membership model when converted to array.
	 *
	 * Called before data is sorted and returned by `$this->jsonSerialize()`.
	 *
	 * @since 3.36.3
	 *
	 * @param array $arr Data to be serialized.
	 * @return array
	 */
	public function toArrayAfter( $arr ) {
		$arr['categories'] = $this->get_categories(
			array(
				'fields' => 'names',
			)
		);

		$arr['tags'] = $this->get_tags(
			array(
				'fields' => 'names',
			)
		);

		return $arr;
	}
}
