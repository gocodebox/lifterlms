<?php
/**
 * Scaffold a Course
 * @since    3.3.0
 * @version  3.3.0
 *
 * @todo  track prerequisites?
 * @todo  quizzes & quiz questions
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
	private $tempids = array(
		'courses' => array(),
		'lessons' => array(),
	);

	private $stats = array(
		'authors' => 0,
		'courses' => 0,
		'sections' => 0,
		'lessons' => 0,
		'quizzes' => 0,
		'questions' => 0,
		'terms' => 0,
	);

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

	private function increment( $type ) {
		if ( isset( $this->stats[ $type ] ) ) {
			$this->stats[ $type ]++;
		}
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
			$this->handle_prerequisites();

			if ( $this->is_error() ) {
				$wpdb->query( 'ROLLBACK' );
			} else {
				$wpdb->query( 'COMMIT' );
			}


		}

	}

	public function is_error() {
		return ( $this->error->get_error_messages() );
	}

	public function get_results() {

		if ( $this->is_error() ) {
			return $this->error;
		} else {
			return $this->stats;
		}

	}



	private function build_courses() {

		foreach ( $this->raw['courses'] as $raw_course ) {

			$author_id = $this->get_author_id( $raw_course );
			$course = $this->create_course( $raw_course, $author_id );

			if ( $course ) {

				// store the tempid if it exists
				if ( ! empty( $raw_course['tempid'] ) ) {
					$this->tempids['courses'][ $raw_course['tempid'] ] = $course->get( 'id' );
				}

				// build sections
				$this->build_sections( $raw_course, $course->get( 'id' ), $author_id );

			}

		}

	}


	private function build_sections( $raw_course, $course_id, $author_id ) {

		if ( ! empty( $raw_course['sections'] ) && is_array( $raw_course['sections'] ) ) {

			foreach ( $raw_course['sections'] as $order => $raw_section ) {

				$order = $order + 1; // start at 1 not 0

				$section_id = $this->create_section( $raw_section, $order, $course_id, $author_id );

				if ( is_wp_error( $section_id ) ) {

					$this->error->add( 'section-creation', sprintf( __( 'Error creating section "%s"', 'lifterlms' ), $raw_section['title'] ) );
					return;

				} else {

					$this->build_lessons( $raw_section, $section_id, $course_id, $author_id );

				}

			}

		}
	}

	private function build_lessons( $raw_section, $section_id, $course_id, $author_id ) {

		if ( ! empty( $raw_section['lessons'] ) && is_array( $raw_section['lessons'] ) ) {

			foreach ( $raw_section['lessons'] as $order => $raw_lesson ) {

				$order = $order + 1; // start at 1 not 0

				$lesson = $this->create_lesson( $raw_lesson, $order, $section_id, $course_id, $author_id );

				if ( ! $lesson ) {

					$this->error->add( 'lesson-creation', sprintf( __( 'Error creating lesson "%s"', 'lifterlms' ), $raw_lesson['title'] ) );
					return;

				} else {

					// store the tempid if it exists
					if ( ! empty( $raw_lesson['tempid'] ) ) {
						$this->tempids['lessons'][ $raw_lesson['tempid'] ] = $lesson->get( 'id' );
					}

					// quizzes?
					// $this->build_lessons( $raw_lesson, $section_id, $course_id, $author_id );

				}

			}

		}
	}


	private function handle_prerequisites() {

		global $wpdb;

		$temps = array_merge( $this->tempids['courses'], $this->tempids['lessons'] );

		foreach ( $temps as $temp => $real ) {

			$wpdb->update(
				$wpdb->postmeta,
				array(
					'meta_key' => '_llms_prerequisite',
					'meta_value' => $real,
				),
				array(
					'meta_key' => '_llms_temp_prerequisite',
					'meta_value' => $temp,
				),
				array( '%s', '%d' ),
				array( '%s', '%s' )
			);

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

			$this->increment( 'courses' );

			// handle prerequities
			// rename the prereq to a temp field to be renamed later
			if ( $raw_course['settings']['prerequisite'] ) {
				$raw_course['settings']['temp_prerequisite'] = $raw_course['settings']['prerequisite'];
				unset( $raw_course['settings']['prerequisite'] );
			}

			foreach ( $raw_course['settings'] as $key => $val ) {
				$course->set( $key, $val );
			}

		}

		$this->add_course_terms( $course, $raw_course );

		return $course;

	}

	public function create_lesson( $raw_lesson, $order, $section_id, $course_id, $author_id ) {

		// create the lesson
		$lesson = new LLMS_Lesson( 'new', array(
			'post_author' => $author_id,
			'post_content' => $raw_lesson['description'],
			'post_date' => $this->format_date( $raw_lesson['date_created'] ),
			'post_modified' => $this->format_date( $raw_lesson['date_updated'] ),
			'post_status' => $raw_lesson['status'],
			'post_title' => $raw_lesson['title'],
			'meta_input' => array(
				'_llms_order' => $order,
				'_llms_parent_course' => $course_id,
				'_llms_parent_section' => $section_id,
			)
		) );

		// lesson wasen't created
		if ( ! $lesson->get( 'id' ) ) {
			$this->error->add( 'lesson-creation', sprintf( __( 'Failed during creation of lesson "%s"', 'lifterlms' ), $raw_lesson['title'] ) );
			return false;
		}
		// add all meta info
		elseif ( ! empty( $raw_lesson['settings'] ) && is_array( $raw_lesson['settings'] ) ) {

			$this->increment( 'lessons' );

			// handle prerequities
			// rename the prereq to a temp field to be renamed later
			if ( $raw_lesson['settings']['prerequisite'] ) {
				$raw_lesson['settings']['temp_prerequisite'] = $raw_lesson['settings']['prerequisite'];
				unset( $raw_lesson['settings']['prerequisite'] );
			}

			foreach ( $raw_lesson['settings'] as $key => $val ) {
				$lesson->set( $key, $val );
			}

		}

		return $lesson;

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

		$section = wp_insert_post( array(
			'post_author' => $author_id,
			'post_title' => $raw_section['title'],
			'post_type' => 'section',
			'meta_input' => array(
				'_llms_order' => $order,
				'_llms_parent_course' => $course_id,
			),
		) );

		if ( ! is_wp_error( $section ) ) {

			$this->increment( 'sections' );

		}

		return $section;
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

					// increment stats
					$this->increment( 'authors' );

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
			} else {
				$this->increment( 'terms' );
			}

		}

		return $term['term_id'];

	}

}
