<?php
/**
 * Scaffold a Course
 * @since  3.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'init', function() {

	$raw_example = array(

		'courses' => array(

			array(

				// author information
				'author' => array(
					'email' => 'help123@lifterlms.com',
					'first_name' => 'Thomas',
					'last_name' => 'Levy',
				),

				// alternate author style
				// 'author' => 123,

				// access plans
				'access_plans' => array(
				),

				// basic information
				'date_created' => '',
				'date_updated' => '',
				'description' => '<strong>Default</strong><br>Lorem Ipsum Dolor Sit',
				'public_description' => '<strong>Public</strong><br>Lorem Ipsum Dolor Sit',
				'status' => 'publish',
				'title' => 'This Course Came From a Scaffold!',

				// advanced settings
				'settings' => array(
					'capacity' => '', // absint
					'prerequisite' => '', // absint
					'prerequisite_track' => '', // absint
					'enable_capacity' => 'no', // yesno
					'enrollment_period' => 'no', // yesno
					'has_prerequisite' => 'no', // yesno
					'time_period' => 'no', // yesno
					'audio_embed' => '', // 'text'
					'capacity_message' => '', // 'text'
					'content_restricted_message' => '', // 'text'
					'course_closed_message' => '', // 'text'
					'course_opens_message' => '', // 'text'
					'enrollment_closed_message' => '', // 'text'
					'enrollment_end_date' => '', // 'text'
					'enrollment_opens_message' => '', // 'text'
					'enrollment_start_date' => '', // 'text'
					'end_date' => '', // 'text'
					'length' => '', // 'text'
					'start_date' => '', // 'text'
					'video_embed' => '', // 'text'
				),

				// taxonomies
				'categories' => array(
					'First Category',
					'Second Category',
				),

				'difficulty' => 'Beginner', // string since we only allow one difficulty at a time

				'tags' => array(
					'Featured',
					'Popular',
				),

				'tracks' => array(
					'Beginner',
				),

				// temporary id used for prerequisite associations
				'tempid' => 'course123',

				// the course content
				'sections' => array(

					// section order is literal by array index
					array(
						'title' => 'Section # 1',
						// lesson order is literal by array index
						'lessons' => array(
							array(
								'title' => 'Lesson Number One',

								'settings' => array(
									'assigned_quiz' => '',  // int
									'audio_embed' => '',  // string
									'date_available' => '',  // string
									'days_before_available' => '',  // int
									'drip_method' => '',  // string
									'free_lesson' => 'no',  // yesno
									'has_prerequisite' => '',  // yesno
									'order' => '', // int
									'prerequisite' => '',  // int
									'require_passing_grade' => 'no',  // yesno
									'time_available' => '',  // string
									'video_embed' => '',  // string
								),
							),
						),

					),
				),


			),

		),

	);

	$test = new LLMS_Scaffold( $raw_example );
	$test->build();
	// var_dump( $test );

}, 10 );

class LLMS_Scaffold {


	/**
	 * Raw contents passed into the scaffold's constructor
	 * @var  array
	 */
	private $raw = array();

	/**
	 * Associate raw tempids with actual created ids
	 * @var  array
	 */
	private $tempids = array();

	/**
	 * Construct a new scaffold instance with data
	 * @param    array|JSON   $raw   array of json of course scaffold content
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct( $raw ) {

		if ( ! is_array( $raw ) ) {

			$raw = json_decode( $raw, true );

		}

		$this->error = new WP_Error();
		$this->raw = $raw;

	}


	public function build() {

		if ( empty( $this->raw['courses'] ) ) {
			$this->error->add( 'required', __( 'Missing required "courses" array', 'lifterlms' ) );
		} elseif ( ! is_array( $this->raw['courses'] ) ) {
			$this->error->add( 'format', __( '"courses" must be an array', 'lifterlms' ) );
		} else {

			global $wpdb;

			$wpdb->hide_errors();

			$wpdb->query( 'START TRANSACTION' );

			$this->build_courses();

			$wpdb->query( 'ROLLBACK' );
			// $wpdb->query( 'COMMIT' );
		}


			var_dump( $this );

	}



	private function build_courses() {

		foreach ( $this->raw['courses'] as $raw_course ) {

			$author_id = $this->get_author_id( $raw_course );
			$course = $this->create_course( $raw_course, $author_id );

			if ( $course ) {

				// store the tempid if it exists
				if ( $raw_course['tempid'] ) {
					$this->tempids[ $raw_course['tempid'] ] = $course->get( 'id' );
				}

				// build sections
				$this->build_sections( $raw_course, $course_id, $author_id );

			}

		}

	}


	private function build_sections( $raw_course, $course_id, $author_id ) {

		if ( ! empty( $raw_course['sections'] ) && is_array( $raw_course['sections'] ) ) {

			foreach ( $raw_course['sections'] as $order => $raw_section ) {

				$order = $order + 1; // start at 1 not 0

				$section_id = $this->create_section( $raw_section, $order, $course_id, $author_id );

				if ( ! $section_id ) {

					$this->error->add( 'section-creation', sprintf( __( 'Error creating section "%s"', 'lifterlms' ), $raw_section['title'] ) );
					return;

				} else {

					$this->build_lessons( $raw_section, $section_id, $course_id, $author_id );

				}

			}

		}
	}


	/**
	 * Add taxonomy terms to a course
	 * @param    obj      $course      instance of an LLMS Course
	 * @param    array    $raw_course  array of raw course data
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_course_terms( $course, $raw_course ) {

		$taxes = array(
			'course_cat' => 'categories',
			'course_difficulty' => 'difficulty',
			'course_tag' => 'tags',
			'course_track' => 'tracks',
		);

		// convert difficulty to an array
		$raw_course['difficulty'] = array( $raw_course['difficulty'] );

		foreach ( $taxes as $tax => $key ) {

			if ( ! empty( $raw_course[ $key ] ) && is_array( $raw_course[ $key ] ) ) {

				// we can only have one difficulte at a time
				$append = ( 'difficulty' === $key ) ? false : true;

				$terms = array();

				// find term id or create it
				foreach ( $raw_course[ $key ] as $term_name ) {
					if ( $term_id = $this->get_term_id( $term_name, $tax ) ) {
						$terms[] = $term_id;
					}
				}

				wp_set_post_terms( $course->get( 'id' ), $terms, $tax, $append );

			}

		}

	}

	/**
	 * Get an LLMS Course from a raw course array
	 * always creates a course, runs no checks for potential duplicates
	 * @param    array     $raw_course  array of raw course data
	 * @return   obj|false              Instance of the new LLMS_Course or false on error
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function create_course( $raw_course, $author_id ) {

		// create the new course
		$course = new LLMS_Course( 'new', array(
			'post_author' => $author_id,
			'post_content' => $raw_course['description'],
			'post_date' => $this->format_date( $raw_course['date_created'] ),
			'post_excerpt' => $raw_course['public_description'],
			'post_modified' => $this->format_date( $raw_course['date_updated'] ),
			'post_status' => $raw_course['status'],
			'post_title' => $raw_course['title'],
		) );

		// course wasen't created
		if ( ! $course->get( 'id' ) ) {
			$this->error->add( 'course-creation', sprintf( __( 'Failed during creation of course "%s"', 'lifterlms' ), $raw_course['title'] ) );
			return false;
		}
		// add all meta info
		elseif ( ! empty( $raw_course['settings'] ) && is_array( $raw_course['settings'] ) ) {

			foreach ( $raw_course['settings'] as $key => $val ) {
				$course->set( $key, $val );
			}

		}

		$this->add_course_terms( $course, $raw_course );

		return $course;

	}

	/**
	 * Create a new section
	 * @param    array     $raw_section  raw section data
	 * @param    int       $order        order of the section within the course
	 * @param    int       $course_id    parent course id
	 * @param    int       $author_id    wp user author id
	 * @return   int|false               WP Post ID of the section or false on failure
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function create_section( $raw_section, $order, $course_id, $author_id ) {

		return wp_insert_post( array(
			'post_author' => $author_id,
			'post_title' => $raw_section['title'],
			'post_type' => 'section',
			'meta_input' => array(
				'_llms_order' => $order,
				'_parent_course' => $course_id,
			),
		) );

	}

	/**
	 * Ensure raw dates are correctly formatted to create a post date
	 * falls back to current date if no date is supplied
	 * @param    string     $raw_date  raw date from raw object
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function format_date( $raw_date = null ) {

		if ( ! $raw_date ) {
			return current_time( 'mysql' );
		} else {
			return date( 'Y-m-d H:i:s' , strtotime( $raw_date ) );
		}

	}

	/**
	 * Gets the author id based on data provided by a course array
	 * will locate or create a new user
	 * falls back to curreng user if none provided
	 * fail safe to 1
	 * @return   int     a WP User ID
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_author_id( $course ) {

		if ( ! isset( $course['author'] ) ) {
			$author_id = get_current_user_id();
		} else {
			$author = $course['author'];
		}

		// if we have an author object, get the user id
		if ( isset( $author ) )  {

			// uid passed in, use the ID
			if ( is_numeric( $author ) ) {

				// validate the user id is associated with an actual user
				$find_by_id = get_user_by( 'ID', $author );

				if ( $find_by_id ) {

					$author_id = $author;

				}

			} else {

				// check if the email already exists
				$user = get_user_by( 'email', $author['email'] );

				if ( $user ) {

					$author_id = $user->ID;

				}
				// create a new user
				else {

					$author_id = wp_insert_user( array(
						'display_name' => $author['first_name'] . ' ' . $author['last_name'],
						'first_name' => $author['first_name'],
						'last_name' => $author['last_name'],
						'role' => apply_filters( 'llms_scaffold_new_author_role', 'administrator' ),
						'user_email' => $author['email'],
						'user_login' => LLMS_Person_Handler::generate_username( $author['email'] ),
						'user_pass' => null,
					) );

				}


			}

		}

		// fallback to 1 if we still don't have an id for some reason...
		if ( ! $author_id ) {

			$author_id = 1;

		}

		return $author_id;

	}

	/**
	 * Get a WP Term ID for a term by taxonomy and term name
	 * attempts to find a given term by name first to pervent duplicates during imports
	 * @param    string     $term_name  term name
	 * @param    string     $tax        taxonomy slug
	 * @return   int|false              term id or false when error
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_term_id( $term_name, $tax ) {

		$term = get_term_by( 'name', $term_name, $tax, ARRAY_A );

		// not found, create it
		if ( ! $term ) {

			$term = wp_insert_term( $term_name, $tax );

			if ( is_wp_error( $term ) ) {
				$this->error->add( 'term-creation', sprintf( __( 'Error creating new term "%s"', 'lifterlms' ), $term_name ) );
				return false;
			}

		}

		return $term['term_id'];

	}




}
