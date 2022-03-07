<?php
/**
 * LifterLMS Membership Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Membership model class
 *
 * @since 3.0.0
 * @since 3.30.0 Added optional argument to `add_auto_enroll_courses()` method.
 * @since 3.32.0 Added `get_student_count()` method.
 * @since 3.36.3 Added `get_categories()`, `get_tags()` and `toArrayAfter()` methods.
 * @since 3.38.1 Added methods for retrieving posts associated with the membership.
 * @since 4.0.0 Added MySQL 8.0 compatibility.
 * @since 5.2.1 Check for an empty sales page URL or ID.
 * @since 5.3.0 Move sales page methods to `LLMS_Trait_Sales_Page`.
 *
 * @property int[]  $auto_enroll                Array of course IDs that users will be autoenrolled in upon successful enrollment in this membership.
 * @property array  $instructors                Course instructor user information.
 * @property string $restriction_redirect_type  What type of redirect action to take when content is restricted by this membership [none|membership|page|custom].
 * @property int    $redirect_page_id           WP Post ID of a page to redirect users to when $restriction_redirect_type is 'page'.
 * @property string $redirect_custom_url        Arbitrary URL to redirect users to when $restriction_redirect_type is 'custom'.
 * @property string $restriction_add_notice     Whether or not to add an on screen message when content is restricted by this membership [yes|no].
 * @property string $restriction_notice         Notice to display when $restriction_add_notice is 'yes'.
 * @property int    $sales_page_content_page_id WP Post ID of the WP page to redirect to when $sales_page_content_type is 'page'.
 * @property string $sales_page_content_type    Sales page behavior [none,content,page,url].
 * @property string $sales_page_content_url     Redirect URL for a sales page, when $sales_page_content_type is 'url'.
 */
class LLMS_Membership extends LLMS_Post_Model implements LLMS_Interface_Post_Instructors {

	use LLMS_Trait_Sales_Page;

	/**
	 * Membership post meta.
	 *
	 * @var array
	 */
	protected $properties = array(
		'auto_enroll'               => 'array',
		'instructors'               => 'array',
		'redirect_page_id'          => 'absint',
		'restriction_add_notice'    => 'yesno',
		'restriction_notice'        => 'html',
		'restriction_redirect_type' => 'text',
		'redirect_custom_url'       => 'text',
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
	 * Constructor for this class and the traits it uses.
	 *
	 * @since 5.3.0
	 *
	 * @param string|int|LLMS_Post_Model|WP_Post $model 'new', WP post id, instance of an extending class, instance of WP_Post.
	 * @param array                              $args  Args to create the post, only applies when $model is 'new'.
	 */
	public function __construct( $model, $args = array() ) {

		$this->construct_sales_page();
		parent::__construct( $model, $args );
	}

	/**
	 * Add courses to autoenrollment by id
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Added optional `$replace` argument.
	 *
	 * @param array|int $course_ids Array of course id or course id as int.
	 * @param bool      $replace    Optional. When `true`, replaces all existing courses with `$course_ids`, when false merges `$course_ids` with existing courses. Default `false`.
	 * @return boolean Returns `true` on success, and `false` on error or if the value in the db is unchanged.
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
	 * Retrieve a list of posts associated with the membership
	 *
	 * An associated post is:
	 * + A post, page, or custom post type which supports `llms-membership-restrictions` and has restrictions enabled to this membership
	 * + A course that exists in the memberships list of auto-enroll courses
	 * + A course that has at least one access plan with members-only availability linked to this membership
	 *
	 * @since 3.38.1
	 * @since 4.15.0 Minor restructuring to only query post type data when it's needed.
	 *
	 * @param string $post_type If supplied, returns only associations of this post type, otherwise returns an associative array of all associations.
	 * @return array[]|int[] An array of arrays of post IDs. The array keys are the post type and the array values are arrays of integers.
	 *                       If `$post_type` is supplied returns an array of associated post ids as integers.
	 */
	public function get_associated_posts( $post_type = null ) {

		// If we're querying only posts, we can skip these associations entirely because courses don't support them.
		$post_types = 'course' !== $post_type ? get_post_types_by_support( 'llms-membership-restrictions' ) : array();

		// If we're looking at a single post type we only have to query associations for that post type.
		$post_types = $post_type ? array_intersect( $post_types, array( $post_type ) ) : $post_types;

		// Our return array.
		$posts = array();

		// Retrieve all posts that are restricted to a membership via a LifterLMS Membership Restriction setting.
		foreach ( $post_types as $type ) {
			$posts[ $type ] = $this->query_associated_posts( $type, '_llms_is_restricted', 'yes', '_llms_restricted_levels' );
		}

		// Include courses if courses were requested or if no specific post type was requested.
		if ( ! $post_type || 'course' === $post_type ) {
			$posts['course'] = $this->query_associated_courses();
		}

		/**
		 * Filter the list of posts associated with the membership.
		 *
		 * @since 3.38.1
		 *
		 * @param array[]         $posts     An array of arrays of post IDs. The array keys are the post type and the array values are arrays of integers.
		 * @param string|null     $post_type The requested post type if only a specific post type was requested, otherwise `null` to indicate all associated post types.
		 * @param LLMS_Membership $this      Membership object.
		 */
		$posts = apply_filters( 'llms_membership_get_associated_posts', $posts, $post_type, $this );

		// If a single post type was requested, return only that.
		if ( $post_type ) {
			// Return the request post type array and fallback to an empty array if that post type doesn't exist.
			return isset( $posts[ $post_type ] ) ? $posts[ $post_type ] : array();
		}

		// Remove empty arrays and return the rest.
		return array_filter( $posts );

	}

	/**
	 * Get an array of the auto enrollment course ids
	 *
	 * Uses a custom function due to the default "get_array" returning an array with an empty string
	 *
	 * @since 3.0.0
	 * @since 4.15.0 Exclude unpublished courses from the return array.
	 *
	 * @return array
	 */
	public function get_auto_enroll_courses() {

		// Ensure an array when metadata is not set.
		$courses = isset( $this->auto_enroll ) ? $this->get( 'auto_enroll' ) : array();

		// Exclude unpublished courses.
		$courses = array_values(
			array_filter(
				$courses,
				function( $id ) {
					return 'publish' === get_post_status( $id );
				}
			)
		);

		/**
		 * Filters the list of the membership's auto enroll courses
		 *
		 * @since 3.0.0
		 *
		 * @param int[]           $courses    List of LLMS_Course IDs.
		 * @param LLMS_Membership $membership Membership post object.
		 */
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
	 * Retrieve the number of enrolled students in the membership.
	 *
	 * @since 3.32.0
	 * @since 6.0.0 Don't access `LLMS_Student_Query` properties directly.
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

		return $query->get_found_results();

	}

	/**
	 * Get an array of student IDs based on enrollment status in the membership
	 *
	 * @since 3.0.0
	 *
	 * @param string|string[] $statuses Optional. List of enrollment statuses to query by status query is an OR relationship. Default is 'enrolled'.
	 * @param int             $limit    Optional. Number of results. Default is `50`.
	 * @param int             $skip     Optional. Number of results to skip (for pagination). Default is `0`.
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
	 * Retrieve courses associated with the membership
	 *
	 * @since 3.38.1
	 * @since 4.15.0 Exclude unpublished courses.
	 *
	 * @see LLMS_Membership::get_associated_posts()
	 *
	 * @return int[]
	 */
	protected function query_associated_courses() {

		// Start with autoenroll courses.
		$courses = $this->get_auto_enroll_courses();

		// Retrieve all access plans with a members-only availability restriction for this membership.
		foreach ( $this->query_associated_posts( 'llms_access_plan', '_llms_availability', 'members', '_llms_availability_restrictions' ) as $plan_id ) {
			$plan = llms_get_post( $plan_id );
			if ( $plan ) {
				$id = $plan->get( 'product_id' );
				if ( 'publish' === get_post_status( $id ) ) {
					$courses[] = $id;
				}
			}
		}

		return array_unique( $courses );

	}

	/**
	 * Performs a WPDB query to retrieve posts associated with the membership
	 *
	 * @since 3.38.1
	 * @since 4.0.0 Escape `{` character in SQL query to add MySQL 8.0 support.
	 *
	 * @see LLMS_Membesrhip::get_associated_posts()
	 *
	 * @param string $post_type     Post type to query for an association with.
	 * @param string $enabled_key   A meta key name, used to check if the association is enabled for the associated post. For example: "_llms_is_restricted"
	 * @param string $enabled_value The meta value of the `$enabled_key` when the association is enabled. For example "yes" when checking "_llms_is_restricted"..
	 * @param string $list_key      The meta key name where associations are stored as a serialized array of WP_Post IDs. For example "_llms_restricted_levels".
	 * @return int[]
	 */
	protected function query_associated_posts( $post_type, $enabled_key, $enabled_value, $list_key ) {

		global $wpdb;

		// See if we have a cached result first.
		$cache = sprintf( 'membership_%1$d_associated_%2$s', $this->get( 'id' ), $post_type );
		$found = null;
		$ids   = wp_cache_get( $cache, '', false, $found );

		// We don't, perform a query.
		if ( ! $found ) {

			$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT metas.post_id
				 FROM {$wpdb->postmeta} AS metas
				 JOIN {$wpdb->postmeta} AS metas2 ON metas2.post_id = metas.post_id
				 JOIN {$wpdb->posts} AS posts ON posts.ID = metas.post_id
				 WHERE 1
				   AND posts.post_status = 'publish'
				   AND posts.post_type = %s
				   AND metas2.meta_key = %s
				   AND metas2.meta_value = %s
				   AND metas.meta_key = %s
				   AND metas.meta_value REGEXP %s;",
					$post_type,
					$enabled_key,
					$enabled_value,
					$list_key,
					'a:[0-9][0-9]*:\{(i:[0-9][0-9]*;(i|s:[0-9][0-9]*):"?[0-9][0-9]*"?;)*(i:[0-9][0-9]*;(i|s:[0-9][0-9]*):"?' . $this->get( 'id' ) . '"?;)'
				)
			);

			// Only return ints.
			$ids = array_map( 'absint', $ids );

			// Cache the result.
			wp_cache_set( $cache, $ids );

		}

		return $ids;

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
