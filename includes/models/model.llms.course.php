<?php
/**
 * LifterLMS Course Model
 *
 * @package LifterLMS/Models
 *
 * @since 1.0.0
 * @version 3.30.3
 *
 * @property $audio_embed  (string)  URL to an oEmbed enable audio URL
 * @property $average_grade  (float)  Calculated value of the overall average grade of all *enrolled* students in the course.
 * @property $average_progress  (float)  Calculated value of the overall average progress of all *enrolled* students in the course.
 * @property $capacity  (int)  Number of students who can be enrolled in the course before enrollment closes
 * @property $capacity_message  (string)  Message displayed when capacity has been reached
 * @property $content_restricted_message  (string)  Message displayed when non-enrolled visitors try to access lessons/quizzes directly
 * @property $course_closed_message  (string)  Message displayed to visitors when the course is accessed after the Course End Date has passed. Only applicable when $time_period is 'yes'
 * @property $course_opens_message  (string)  Message displayed to visitors when the course is accessed before the Course Start Date has passed. Only applicable when $time_period is 'yes'
 * @property $enable_capacity  (string)  Whether capacity restrictions are enabled [yes|no]
 * @property $enrollment_closed_message  (string)  Message displayed to non-enrolled visitors when the course is accessed after the Enrollment End Date has passed. Only applicable when $enrollment_period is 'yes'
 * @property $enrollment_end_date   (string)  After this date, registration closes
 * @property $enrollment_opens_message  (string)  Message displayed to non-enrolled visitors when the course is accessed before the Enrollment Start Date has passed. Only applicable when $enrollment_period is 'yes'
 * @property $enrollment_period  (string)  Whether or not a course time period restriction is enabled [yes|no] (all checks should check for 'yes' as an empty string might be returned)
 * @property $enrollment_start_date  (string)  Before this date, registration is closed
 * @property $end_date   (string)  Date when a course closes. Students may no longer view content or complete lessons / quizzes after this date.
 * @property $has_prerequisite   (string)  Determine if prerequisites are enabled [yes|no]
 * @property $instructors  (array)  Course instructor user information
 * @property $prerequisite   (int)  WP Post ID of a the prerequisite course
 * @property $prerequisite_track   (int)  WP Tax ID of a the prerequisite track
 * @property sales_page_content_page_id  (int)  WP Post ID of the WP page to redirect to when $sales_page_content_type is 'page'
 * @property sales_page_content_type  (string)  Sales page behavior [none,content,page,url]
 * @property sales_page_content_url  (string)  Redirect URL for a sales page, when $sales_page_content_type is 'url'
 * @property $start_date  (string)  Date when a course is opens. Students may register before this date but can only view content and complete lessons or quizzes after this date.
 * @property $length  (string)  User defined course length
 * @property $tile_featured_video (string)  Displays the featured video instead of the featured image on course tiles [yes|no]
 * @property $time_period  (string)  Whether or not a course time period restriction is enabled [yes|no] (all checks should check for 'yes' as an empty string might be returned)
 * @property $video_embed  (string)  URL to an oEmbed enable video URL
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Course model.
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Course
extends LLMS_Post_Model
implements LLMS_Interface_Post_Audio
		 , LLMS_Interface_Post_Instructors
		 , LLMS_Interface_Post_Sales_Page
		 , LLMS_Interface_Post_Video {

	protected $properties = array(

		// public
		'audio_embed'                => 'text',
		'average_grade'              => 'float',
		'average_progress'           => 'float',
		'capacity'                   => 'absint',
		'capacity_message'           => 'text',
		'course_closed_message'      => 'text',
		'course_opens_message'       => 'text',
		'content_restricted_message' => 'text',
		'enable_capacity'            => 'yesno',
		'end_date'                   => 'text',
		'enrollment_closed_message'  => 'text',
		'enrollment_end_date'        => 'text',
		'enrollment_opens_message'   => 'text',
		'enrollment_period'          => 'yesno',
		'enrollment_start_date'      => 'text',
		'has_prerequisite'           => 'yesno',
		'instructors'                => 'array',
		'length'                     => 'text',
		'prerequisite'               => 'absint',
		'prerequisite_track'         => 'absint',
		'sales_page_content_page_id' => 'absint',
		'sales_page_content_type'    => 'string',
		'sales_page_content_url'     => 'string',
		'tile_featured_video'        => 'yesno',
		'time_period'                => 'yesno',
		'start_date'                 => 'text',
		'video_embed'                => 'text',

		// private
		'temp_calc_data'             => 'array',
	);

	protected $db_post_type    = 'course';
	protected $model_post_type = 'course';

	/**
	 * @var array
	 * @since 1.0.0
	 */
	public $sections;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $sku;

	/**
	 * Retrieve an instance of the Post Instructors model
	 *
	 * @return   LLMS_Post_Instructors
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function instructors() {
		return new LLMS_Post_Instructors( $this );
	}

	/**
	 * Retrieve the total points available for the course
	 *
	 * @return   int
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_available_points() {
		$points = 0;
		foreach ( $this->get_lessons() as $lesson ) {
			$points += $lesson->get( 'points' );
		}
		return apply_filters( 'llms_course_get_available_points', $points, $this );
	}

	/**
	 * Get course's prerequisite id based on the type of prerequisite
	 *
	 * @param    string $type  Type of prereq to retrieve id for [course|track]
	 * @return   int|false         Post ID of a course, taxonomy ID of a track, or false if none found
	 * @since    3.0.0
	 * @version  3.7.3
	 */
	public function get_prerequisite_id( $type = 'course' ) {

		if ( $this->has_prerequisite( $type ) ) {

			switch ( $type ) {

				case 'course':
					$key = 'prerequisite';
					break;

				case 'course_track':
					$key = 'prerequisite_track';
					break;

			}

			if ( isset( $key ) ) {
				return $this->get( $key );
			}
		}

		return false;

	}

	/**
	 * Attempt to get oEmbed for an audio provider
	 * Falls back to the [audio] shortcode if the oEmbed fails
	 *
	 * @return string
	 * @since   1.0.0
	 * @version 3.17.0
	 */
	public function get_audio() {
		return $this->get_embed( 'audio' );
	}

	/**
	 * Retrieve course categories
	 *
	 * @param    array $args  array of args passed to wp_get_post_terms
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_categories( $args = array() ) {
		return wp_get_post_terms( $this->get( 'id' ), 'course_cat', $args );
	}

	/**
	 * Get Difficulty
	 *
	 * @param    string $field  which field to return from the available term fields
	 *                          any public variables from a WP_Term object are acceptable
	 *                          term_id, name, slug, and more
	 * @return   string
	 * @since    1.0.0
	 * @version  3.24.0
	 */
	public function get_difficulty( $field = 'name' ) {

		$terms = get_the_terms( $this->get( 'id' ), 'course_difficulty' );

		if ( false === $terms ) {

			return '';

		} else {

			foreach ( $terms as $term ) {

				return $term->$field;

			}
		}

	}

	/**
	 * Retrieve course instructor information
	 *
	 * @param    boolean $exclude_hidden  if true, excludes hidden instructors from the return array
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_instructors( $exclude_hidden = false ) {

		return apply_filters(
			'llms_course_get_instructors',
			$this->instructors()->get_instructors( $exclude_hidden ),
			$this,
			$exclude_hidden
		);

	}

	/**
	 * Get course lessons
	 *
	 * @param    string $return  type of return [ids|posts|lessons]
	 * @return   int[]|WP_Post[]|LLMS_Lesson[] type depends on value of $return
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function get_lessons( $return = 'lessons' ) {

		$lessons = array();
		foreach ( $this->get_sections( 'sections' ) as $section ) {
			$lessons = array_merge( $lessons, $section->get_lessons( 'posts' ) );
		}

		if ( 'ids' === $return ) {
			$ret = wp_list_pluck( $lessons, 'ID' );
		} elseif ( 'posts' === $return ) {
			$ret = $lessons;
		} else {
			$ret = array_map( 'llms_get_post', $lessons );
		}
		return $ret;

	}

	/**
	 * Retrieve an array of quizzes within a course
	 *
	 * @return   array            array of WP_Post IDs of the quizzes
	 * @since    3.12.0
	 * @version  3.16.0
	 */
	public function get_quizzes() {

		$quizzes = array();
		foreach ( $this->get_lessons( 'lessons' ) as $lesson ) {
			if ( $lesson->has_quiz() ) {
				$quizzes[] = $lesson->get( 'quiz' );
			}
		}
		return $quizzes;

	}

	/**
	 * Get the URL to a WP Page or Custom URL when sales page redirection is enabled
	 *
	 * @return   string
	 * @since    3.20.0
	 * @version  3.23.0
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

		return apply_filters( 'llms_course_get_sales_page_url', $url, $this, $type );
	}

	/**
	 * Get course sections
	 *
	 * @param    string $return  type of return [ids|posts|sections]
	 * @return   int[]|WP_Post[]|LLMS_Section[] type depends on value of $return
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function get_sections( $return = 'sections' ) {

		$q = new WP_Query(
			array(
				'meta_key'       => '_llms_order',
				'meta_query'     => array(
					array(
						'key'   => '_llms_parent_course',
						'value' => $this->id,
					),
				),
				'order'          => 'ASC',
				'orderby'        => 'meta_value_num',
				'post_type'      => 'section',
				'posts_per_page' => 500,
			)
		);

		if ( 'ids' === $return ) {
			$r = wp_list_pluck( $q->posts, 'ID' );
		} elseif ( 'posts' === $return ) {
			$r = $q->posts;
		} else {
			$r = array();
			foreach ( $q->posts as $p ) {
				$r[] = new LLMS_Section( $p );
			}
		}

		return $r;

	}

	/**
	 * Retrieve the number of enrolled students in the course
	 *
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
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
	 * Get an array of student IDs based on enrollment status in the course
	 *
	 * @param    string|array $statuses  list of enrollment statuses to query by
	 *                                   status query is an OR relationship
	 * @param    integer      $limit        number of results
	 * @param    integer      $skip         number of results to skip (for pagination)
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_students( $statuses = 'enrolled', $limit = 50, $skip = 0 ) {

		return llms_get_enrolled_students( $this->get( 'id' ), $statuses, $limit, $skip );

	}

	/**
	 * Retrieve course tags
	 *
	 * @param    array $args  array of args passed to wp_get_post_terms
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_tags( $args = array() ) {
		return wp_get_post_terms( $this->get( 'id' ), 'course_tag', $args );
	}

	/**
	 * Retrieve course tracks
	 *
	 * @param    array $args  array of args passed to wp_get_post_terms
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_tracks( $args = array() ) {
		return wp_get_post_terms( $this->get( 'id' ), 'course_track', $args );
	}

	/**
	 * Retrieve an array of students currently enrolled in the course
	 *
	 * @param    integer $limit   number of results
	 * @param    integer $skip    number of results to skip (for pagination)
	 * @return   array
	 * @since    1.0.0
	 * @version  3.0.0 - updated the function to be less complicated
	 */
	public function get_enrolled_students( $limit, $skip ) {

		return $this->get_students( 'enrolled', $limit, $skip );

	}

	/**
	 * Get a user's percentage completion through the course
	 *
	 * @return  float
	 * @since   1.0.0
	 * @version 3.17.2
	 */
	public function get_percent_complete( $user_id = '' ) {

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			return 0;
		}
		return $student->get_progress( $this->get( 'id' ), 'course' );

	}

	/**
	 * Retrieve an instance of the LLMS_Product for this course
	 *
	 * @return   LLMS_Product instance of an LLMS_Product
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_product() {
		return new LLMS_Product( $this->get( 'id' ) );
	}

	/**
	 * Attempt to get oEmbed for a video provider
	 * Falls back to the [video] shortcode if the oEmbed fails
	 *
	 * @return   string
	 * @since    1.0.0
	 * @version  3.17.0
	 */
	public function get_video() {
		return $this->get_embed( 'video' );
	}

	/**
	 * Compare a course meta info date to the current date and get a bool
	 *
	 * @param    string $date_key  property key, eg "start_date" or "enrollment_end_date"
	 * @return   boolean               true when the date is in the past
	 *                                 false when the date is in the future
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_date_passed( $date_key ) {

		$now  = current_time( 'timestamp' );
		$date = $this->get_date( $date_key, 'U' );

		// if there's no date, we can't make a comparison
		// so assume it's unset and unnecessary
		// so return 'false'
		if ( ! $date ) {

			return false;

		} else {

			return $now > $date;

		}

	}

	/**
	 * Determine if the course is at capacity based on course capacity settings
	 *
	 * @return   boolean    true if not at capacity, false if at or over capacity
	 * @since    3.0.0
	 * @version  3.15.0
	 */
	public function has_capacity() {

		// capacity disabled, so there is capacity
		if ( 'yes' !== $this->get( 'enable_capacity' ) ) {
			return true;
		}

		$capacity = $this->get( 'capacity' );
		// no capacity restriction set, so it has capacity
		if ( ! $capacity ) {
			return true;
		}

		// compare results
		return ( $this->get_student_count() < $capacity );

	}

	/**
	 * Determine if prerequisites are enabled and there are prereqs configured
	 *
	 * @param    string $type  determine if a specific type of prereq exists [any|course|track]
	 * @return   boolean         Returns true if prereq is enabled and there is a prerequisite course or track
	 * @since    3.0.0
	 * @version  3.7.5
	 */
	public function has_prerequisite( $type = 'any' ) {

		if ( 'yes' === $this->get( 'has_prerequisite' ) ) {

			if ( 'any' === $type ) {

				return ( $this->get( 'prerequisite' ) || $this->get( 'prerequisite_track' ) );

			} elseif ( 'course' === $type ) {

				return ( $this->get( 'prerequisite' ) ) ? true : false;

			} elseif ( 'course_track' === $type ) {

				return ( $this->get( 'prerequisite_track' ) ) ? true : false;

			}
		}

		return false;

	}

	/**
	 * Determine if sales page redirection is enabled
	 *
	 * @return   string
	 * @since    3.20.0
	 * @version  3.23.0
	 */
	public function has_sales_page_redirect() {
		$type = $this->get( 'sales_page_content_type' );
		return apply_filters( 'llms_course_has_sales_page_redirect', in_array( $type, array( 'page', 'url' ) ), $this, $type );
	}

	/**
	 * Determine if students can access course content based on the current date
	 *
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public function is_enrollment_open() {

		// if no period is set, enrollment is automatically open
		if ( 'yes' !== $this->get( 'enrollment_period' ) ) {

			$ret = true;

		} else {

			$ret = ( $this->has_date_passed( 'enrollment_start_date' ) && ! $this->has_date_passed( 'enrollment_end_date' ) );

		}

		return apply_filters( 'llms_is_course_enrollment_open', $ret, $this );

	}

	/**
	 * Determine if students can access course content based on the current date
	 *
	 * Note that enrollment does not affect the outcome of this check as regardless
	 * of enrollment, once a course closes content is locked
	 *
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public function is_open() {

		// if a course time period is not enabled, just return true (content is accessible)
		if ( 'yes' !== $this->get( 'time_period' ) ) {

			$ret = true;

		} else {

			$ret = ( $this->has_date_passed( 'start_date' ) && ! $this->has_date_passed( 'end_date' ) );

		}

		return apply_filters( 'llms_is_course_open', $ret, $this );

	}

	/**
	 * Determine if a prerequisite is completed for a student
	 *
	 * @param    string $type  type of prereq [course|track]
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_prerequisite_complete( $type = 'course', $student_id = null ) {

		if ( ! $student_id ) {
			$student_id = get_current_user_id();
		}

		// no user or no prereqs so no reason to proceed
		if ( ! $student_id || ! $this->has_prerequisite( $type ) ) {
			return false;
		}

		$prereq_id = $this->get_prerequisite_id( $type );

		// no prereq id of this type, no need to proceed
		if ( ! $prereq_id ) {
			return false;
		}

		// setup student
		$student = new LLMS_Student( $student_id );

		return $student->is_complete( $prereq_id, $type );

	}

	/**
	 * Save instructor information
	 *
	 * @param    array $instructors  array of course instructor information
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function set_instructors( $instructors = array() ) {

		return $this->instructors()->set_instructors( $instructors );

	}

	/**
	 * Add data to the course model when converted to array
	 * Called before data is sorted and returned by $this->jsonSerialize()
	 *
	 * @param    array $arr   data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.8.0
	 */
	public function toArrayAfter( $arr ) {

		$product             = $this->get_product();
		$arr['access_plans'] = array();
		foreach ( $product->get_access_plans( false, false ) as $p ) {
			$arr['access_plans'][] = $p->toArray();
		}

		$arr['sections'] = array();
		foreach ( $this->get_sections() as $s ) {
			$arr['sections'][] = $s->toArray();
		}

		$arr['categories'] = $this->get_categories(
			array(
				'fields' => 'names',
			)
		);
		$arr['tags']       = $this->get_tags(
			array(
				'fields' => 'names',
			)
		);
		$arr['tracks']     = $this->get_tracks(
			array(
				'fields' => 'names',
			)
		);

		$arr['difficulty'] = $this->get_difficulty();

		return $arr;

	}

	/*
			   /$$                                                               /$$                     /$$
			  | $$                                                              | $$                    | $$
		  /$$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		 /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		|  $$$$$$$|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		 \_______/ \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
							| $$
							| $$
							|__/
	*/


	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.0.0
	 *
	 * @return WP_Post[]
	 */
	public function get_children_sections() {
		llms_deprecated_function( 'LLMS_Course::get_children_sections()', '3.0.0', "LLMS_Course::get_sections( 'posts' )" );
		return $this->get_sections( 'posts' );
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.0.0
	 *
	 * @return WP_Post[]
	 */
	public function get_children_lessons() {
		llms_deprecated_function( 'LLMS_Course::get_children_lessons()', '3.0.0', "LLMS_Course::get_lessons( 'posts' )" );
		return $this->get_sections( 'posts' );
	}

	/**
	 * Deprecated.
	 *
	 * @since 3.0.0
	 * @deprecated 3.34.0
	 *
	 * @return WP_User instance of WP_User
	 */
	public function get_author() {
		llms_deprecated_function( 'LLMS_Course::get_author()', '[version]' );
		return new WP_User( $this->get_author_id() );
	}

	/**
	 * Get the course author's WP User ID
	 *
	 * @since  3.0.0
	 * @deprecated 3.34.0
	 *
	 * @return int
	 */
	public function get_author_id() {
		llms_deprecated_function( 'LLMS_Course::get_author_id()', '[version]', 'LLMS_Course::get( "author" )' );
		return $this->post->post_author;
	}

	/**
	 * Deprecated.
	 *
	 * @since  3.0.0
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_author_name() {
		llms_deprecated_function( 'LLMS_Course::get_author_name()', '[version]' );
		$author = $this->get_author();
		return $author->display_name;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_sku() {
		llms_deprecated_function( 'LLMS_Course::get_sku()', '[version]', 'LLMS_Course::get( "sku" )' );
		return $this->sku;
	}

	/**
	 * Deprecated.
	 *
	 * @since 3.0.0
	 * @deprecated 3.34.0
	 *
	 * @return int
	 */
	public function get_id() {
		llms_deprecated_function( 'LLMS_Course::get_id()', '[version]', 'LLMS_Course::get( "id" )' );
		return $this->id;
	}

	/**
	 * Deprecated.
	 *
	 * @since  3.0.0
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_title() {
		llms_deprecated_function( 'LLMS_Course::get_title()', '[version]', 'get_the_title()' );
		return get_the_title( $this->get_id() );
	}

	/**
	 * Deprecated.
	 *
	 * @since 3.0.0
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_permalink() {
		llms_deprecated_function( 'LLMS_Course::get_permalink()', '[version]', 'get_permalink()' );
		return get_permalink( $this->get_id() );
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return array
	 */
	public function get_user_postmeta_data( $post_id ) {
		llms_deprecated_function( 'LLMS_Course::get_user_postmeta_data()', '[version]' );
		global $wpdb;
		$user_id     = get_current_user_id();
		$results     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE post_id = %d", $user_id, $post_id ) );
		$num_results = count( $results );
		for ( $i = 0; $i < $num_results; $i++ ) {
			$results[ $results[ $i ]->meta_key ] = $results[ $i ];
			unset( $results[ $i ] );
		}
		return $results;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return  array
	 */
	public function get_user_postmetas_by_key( $post_id, $meta_key ) {
		llms_deprecated_function( 'LLMS_Course::get_user_postmetas_by_key()', '[version]' );
		global $wpdb;
		$results     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE post_id = %s and meta_key = %s ORDER BY updated_date DESC", $post_id, $meta_key ) );
		$num_results = count( $results );
		for ( $i = 0; $i < $num_results; $i++ ) {
			$results[ $results[ $i ]->post_id ] = $results[ $i ];
			unset( $results[ $i ] );
		}
		return $results;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_checkout_url() {
		llms_deprecated_function( 'LLMS_Course::get_checkout_url()', '[version]' );
		if ( llms_is_alternative_checkout_enabled() || is_user_logged_in() ) {
			$checkout_page_id = llms_get_page_id( 'checkout' );
		} else {
			$checkout_page_id = llms_get_page_id( 'myaccount' );
		}
		$checkout_url = apply_filters( 'lifterlms_get_checkout_url', $checkout_page_id ? get_permalink( $checkout_page_id ) : '' );
		return apply_filters( 'lifterlms_product_purchase_checkout_redirect', add_query_arg( 'product-id', $this->id, $checkout_url ) );
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_start_date() {
		llms_deprecated_function( 'LLMS_Course::get_start_date()', '[version]', 'LLMS_Course::get_date( "start_date" )' );
		if ( $this->start_date ) {
			return $this->start_date;
		} else {
			return $this->post->post_date;
		}
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_end_date() {
		llms_deprecated_function( 'LLMS_Course::get_end_date()', '[version]', 'LLMS_Course::get_date( "end_date" )' );
		return $this->end_date;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return int|false
	 */
	public function get_next_uncompleted_lesson() {
		llms_deprecated_function( 'LLMS_Course::get_next_uncompleted_lesson()', '[version]' );
		$lessons_not_completed = array();
		$lessons               = $this->get_lessons( 'posts' );
		$user                  = new LLMS_Person();
		foreach ( $lessons as $lesson ) {
			$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $lesson->ID );
			if ( ! isset( $user_postmetas['_is_complete'] ) ) {
				array_push( $lessons_not_completed, $lesson->ID );
			}
		}
		if ( $lessons_not_completed ) {
			return $lessons_not_completed[0];
		}

		return false;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return int[]
	 */
	public function get_lesson_ids() {
		llms_deprecated_function( 'LLMS_Course::get_lesson_ids()', '[version]', 'LLMS_Course::get_lessons( "ids" )' );
		$lessons  = array();
		$args     = array(
			'post_type'      => 'section',
			'posts_per_page' => 500,
			'meta_key'       => '_llms_order',
			'order'          => 'ASC',
			'orderby'        => 'meta_value_num',
			'meta_query'     => array(
				array(
					'key'     => '_llms_parent_course',
					'value'   => $this->id,
					'compare' => '=',
				),
			),
		);
		$sections = get_posts( $args );
		foreach ( $sections as $s ) {
			$section   = new LLMS_Section( $s->ID );
			$lessonset = $section->get_children_lessons();
			foreach ( $lessonset as $lessonojb ) {
				$lessons[] = $lessonojb->ID;
			}
		}
		return $lessons;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return array
	 */
	public function get_syllabus_sections() {
		llms_deprecated_function( 'LLMS_Course::get_syllabus_sections()', '[version]', 'LLMS_Course::get_sections()' );
		$syllabus = $this->get_syllabus();
		$sections = array();
		if ( $syllabus ) {
			foreach ( $syllabus as $key => $value ) {
				array_push( $sections, $value['section_id'] );
			}
		}
		return $sections;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_short_description() {
		llms_deprecated_function( 'LLMS_Course::get_short_description()', '[version]', 'LLMS_Course::get( "excerpt" )' );
		$short_description = wpautop( $this->post->post_excerpt );
		return $short_description;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return array
	 */
	public function get_syllabus() {
		llms_deprecated_function( 'LLMS_Course::get_syllabus()', '[version]', 'LLMS_Course::get_sections()' );
		$syllabus = $this->sections;
		return $syllabus;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @param string $user_id WP_User ID.
	 * @return string
	 */
	public function get_user_enroll_date( $user_id = '' ) {
		llms_deprecated_function( 'LLMS_Course::get_user_enroll_date()', '[version]' );
		$enrolled_date = '';
		// if no user get current user
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( $this->is_user_enrolled( $user_id ) ) {
			$user_post_data = self::get_user_post_data( $this->id, $user_id );
			foreach ( $user_post_data as $upd ) {
				if ( 'Enrolled' === $upd->meta_value ) {
					$enrolled_date = $upd->updated_date;
				}
			}
		}
		return $enrolled_date;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @param int    $post_id WP_Post ID.
	 * @param string $user_id WP_User ID.
	 * @return mixed
	 */
	public static function get_user_post_data( $post_id, $user_id = '' ) {
		llms_deprecated_function( 'LLMS_Course::get_user_post_data()', '[version]' );
		global $wpdb;
		$results = false;
		if ( ! empty( $post_id ) ) {
			// if user id is empty get current user id
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			// query user postmeta table
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta
					  WHERE post_id = %s
					    AND user_id = %s",
					$post_id,
					$user_id
				)
			);
		}
		return $results;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @param int    $course_id WP_Post ID.
	 * @param string $user_id WP_User ID.
	 * @return mixed
	 */
	public static function check_enrollment( $course_id, $user_id = '' ) {
		llms_deprecated_function( 'LLMS_Course::check_enrollment()', '[version]' );
		global $wpdb;
		// set enrollment to false
		$enrolled = false;
		// if no course id then nothing we can do
		if ( ! empty( $course_id ) ) {
			// if user id is empty get current user id
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			// query user_postmeta table
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta
						WHERE post_id = %s
						  AND user_id = %s",
					$course_id,
					$user_id
				)
			);
			if ( $results ) {
				foreach ( $results as $result ) {
					if ( '_status' === $result->meta_key && ( 'Enrolled' === $result->meta_value || 'Expired' === $result->meta_value ) ) {
						$enrolled = $results;
					}
				}
			}
		}
		return $enrolled;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @param string $user_id WP_User ID.
	 * @return bool
	 */
	public function is_user_enrolled( $user_id = '' ) {
		llms_deprecated_function( 'LLMS_Course::is_user_enrolled()', '[version]', 'llms_is_user_enrolled()' );
		$enrolled       = false;
		$user_post_data = self::get_user_post_data( $this->id, $user_id );
		if ( $user_post_data ) {
			foreach ( $user_post_data as $upd ) {
				if ( '_status' === $upd->meta_key && 'Enrolled' === $upd->meta_value ) {
					$enrolled = true;
				}
			}
		}
		return $enrolled;
	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @param string $user_id WP_User ID.
	 * @return obj
	 */
	public function get_student_progress( $user_id = '' ) {

		llms_deprecated_function( 'LLMS_Course::get_student_progress()', '[version]', 'LLMS_Student::get_progress()' );

		// if user_id is empty get current user id
		if ( empty( $user_id ) ) {

			$user_id = get_current_user_id();
		}

		// check if user is enrolled
		$enrollment = self::check_enrollment( $this->id, $user_id );

		// set up course details and enrollment information
		$obj     = new stdClass();
		$obj->id = $this->id;

		if ( $enrollment ) {

			$obj->is_enrolled = true;
			$obj->is_complete = false;

			// loop through returned rows and save data to object
			foreach ( $enrollment as $row ) {

				if ( '_start_date' === $row->meta_key ) {

					$obj->start_date = $row->updated_date;

				} elseif ( '_is_complete' === $row->meta_key ) {

					$obj->is_complete    = true;
					$obj->completed_date = $row->updated_date;

				} elseif ( 'status' === $row->meta_key ) {

					$obj->status = $row->meta_value;

				}
			}
		} else {

			$obj->is_enrolled = false;

		}

		// add sections array to object
		$obj->sections = array();
		// add lessons array to object
		$obj->lessons = array();

		$sections = $this->get_sections( 'posts' );

		foreach ( $sections as $child_section ) {
			$section_obj = new LLMS_Section( $child_section->ID );

			$section          = array();
			$section['id']    = $section_obj->id;
			$section['title'] = $section_obj->post->post_title;

			// get any user post meta data
			$section['is_complete'] = false;
			$section_user_data      = self::get_user_post_data( $section_obj->id, $user_id );

			$obj->is_complete = false;
			if ( $section_user_data ) {

				// loop through returned rows and save data to object
				foreach ( $section_user_data as $row ) {

					if ( '_is_complete' === $row->meta_key ) {

						$section['is_complete']    = true;
						$section['completed_date'] = $row->updated_date;

					}
				}
			}

			$obj->sections[] = $section;

			// get lesson data
			$lessons = $section_obj->get_children_lessons();

			if ( $lessons ) {

				foreach ( $lessons as $child_lesson ) {
					$lesson_obj = new LLMS_Lesson( $child_lesson->ID );

					$lesson              = array();
					$lesson['id']        = $lesson_obj->id;
					$lesson['title']     = $lesson_obj->post->post_title;
					$lesson['parent_id'] = $lesson_obj->get_parent_section();

					$lesson['is_complete'] = false;
					$lesson_user_data      = self::get_user_post_data( $lesson_obj->id, $user_id );

					// loop through returned rows and save data to object
					if ( $lesson_user_data ) {

						foreach ( $lesson_user_data as $row ) {

							if ( '_is_complete' === $row->meta_key ) {
								$lesson['is_complete']    = true;
								$lesson['completed_date'] = $row->updated_date;
							}
						}
					}

					$obj->lessons[] = $lesson;

				}
			}
		}// End foreach().

		return $obj;

	}

	/**
	 * Deprecated.
	 *
	 * @since Unknown
	 * @deprecated 3.34.0
	 *
	 * @return string
	 */
	public function get_membership_link() {
		llms_deprecated_function( 'LLMS_Course::get_membership_link()', '[version]' );
		$memberships_required = get_post_meta( $this->id, '_llms_restricted_levels', true );
		if ( count( $memberships_required ) > 1 ) {
			$membership_url = get_permalink( llms_get_page_id( 'memberships' ) );
		} else {
			$membership_url = get_permalink( $memberships_required[0] );
		}
		return apply_filters( 'lifterlms_product_purchase_redirect_membership_required', $membership_url );
	}
}
