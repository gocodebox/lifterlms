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
	 * Type of data to work from
	 * bulk|single
	 * @var  string
	 */
	private $raw_type = '';

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
		'plans' => 0,
		'quizzes' => 0,
		'questions' => 0,
		'terms' => 0,
	);

	/**
	 * Construct a new scaffold instance with data
	 * @param    array|JSON   $raw   array of json of course scaffold content
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function __construct( $raw ) {

		if ( ! is_array( $raw ) ) {

			$raw = json_decode( $raw, true );

		}

		$this->error = new WP_Error();
		$this->raw = $raw;

		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

	}

	/**
	 * Add taxonomy terms to a course
	 * @param    obj      $course_id   WP Post ID of a Course
	 * @param    array    $raw_terms   array of raw term arrays
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function add_course_terms( $course_id, $raw_terms ) {

		$taxes = array(
			'course_cat' => 'categories',
			'course_difficulty' => 'difficulty',
			'course_tag' => 'tags',
			'course_track' => 'tracks',
		);

		foreach ( $taxes as $tax => $key ) {

			if ( ! empty( $raw_terms[ $key ] ) && is_array( $raw_terms[ $key ] ) ) {

				// we can only have one difficulte at a time
				$append = ( 'difficulty' === $key ) ? false : true;

				$terms = array();

				// find term id or create it
				foreach ( $raw_terms[ $key ] as $term_name ) {
					if ( $term_id = $this->get_term_id( $term_name, $tax ) ) {
						$terms[] = $term_id;
					}
				}

				wp_set_post_terms( $course_id, $terms, $tax, $append );

			}

		}

	}




	public function build() {

		if ( ! isset( $this->raw['_generator'] ) ) {
			$this->error->add( 'required', __( 'Invalid raw data supplied', 'lifterlms' ) );
			return;
		}

		$gen = explode( '/', $this->raw['_generator'] );

		// external generator
		if ( 'LifterLMS' !== $gen[0] ) {
			do_action( 'lifterlms_scaffold_build_' . $gen[0], $this );
			return;
		}

		$generators = $this->get_generators();

		// invalid generator
		if ( ! in_array( $gen[1], array_keys( $generators ) ) ) {
			$this->error->add( 'required', __( 'Cannot process data from the supplied generator', 'lifterlms' ) );
			return;
		} else {

			global $wpdb;

			$wpdb->hide_errors();

			$wpdb->query( 'START TRANSACTION' );

			try {

				$func = $generators[ $gen[1] ];
				$this->$func();
				$this->handle_prerequisites();

			} catch ( Exception $e ) {

				$this->error->add( 'exception', $e->getMessage() );

			}

			if ( $this->is_error() ) {
				$wpdb->query( 'ROLLBACK' );
			} else {
				$wpdb->query( 'COMMIT' );
			}

		}

	}





	private function build_course() {

		$temp = array();

		foreach( array( '_generator', '_version' ) as $meta ) {
			$temp[ $meta ] = $this->raw[ $meta ];
			unset( $this->raw[ $meta ] );
		}

		$temp['courses'] = array( $this->raw );

		$this->raw = $temp;

		$this->build_courses();

	}

	private function build_courses() {

		if ( empty( $this->raw['courses'] ) ) {
			$this->error->add( 'required', __( 'Missing required "courses" array', 'lifterlms' ) );
		} elseif ( ! is_array( $this->raw['courses'] ) ) {
			$this->error->add( 'format', __( '"courses" must be an array', 'lifterlms' ) );
		} else {

			foreach ( $this->raw['courses'] as $raw_course ) {

				unset( $raw_course['_generator'], $raw_course['_version'] );

				$course = $this->create_course( $raw_course );

				// $author_id = $this->get_author_id( $raw_course );
				// $course = $this->create_course( $raw_course, $author_id );

				// if ( $course ) {

				// 	// store the tempid if it exists
				// 	if ( ! empty( $raw_course['tempid'] ) ) {
				// 		$this->tempids['courses'][ $raw_course['tempid'] ] = $course->get( 'id' );
				// 	}

				// 	// build sections
				// 	$this->build_sections( $raw_course, $course->get( 'id' ), $author_id );

				// }

			}

		}

	}


	public function create_access_plan( $raw, $course_id, $fallback_author_id = null ) {

		// handle author
		if ( isset( $raw['author'] ) ) {
			$author_id = $this->get_author_id( $raw['author'] );
			if ( ! $author_id ) {
				return $this->error->add( 'author_creation', __( 'Error creating author.', 'lifterlms' ) );
			}
			unset( $raw['author'] );
		}

		if ( empty( $author_id ) ) {
			$author_id = ! empty( $fallback_author_id ) ? $fallback_author_id : get_current_user_id();
		}

		// insert the plan
		$plan = new LLMS_Access_Plan( 'new', array(
			'post_author' => $author_id,
			'post_content' => $raw['content'],
			'post_date' => $this->format_date( $raw['date'] ),
			'post_modified' => $this->format_date( $raw['modified'] ),
			'post_status' => $raw['status'],
			'post_title' => $raw['title'],
		) );

		$this->increment( 'plans' );

		unset( $raw['content'], $raw['date'], $raw['modified'], $raw['name'], $raw['status'], $raw['title'] );

		unset( $raw['product_id'] );
		$plan->set( 'product_id', $course_id );

		// store the from the import if there is one
		if ( isset( $raw['id'] ) ) {
			$plan->set( 'generated_from_id', $raw['id'] );
			unset( $raw['id'] );
		}

		foreach( $raw as $key => $val ) {
			$plan->set( $key, $val );
		}


	}



	public function create_course( $raw ) {

		// handle author
		if ( isset( $raw['author'] ) ) {
			$author_id = $this->get_author_id( $raw['author'] );
			if ( ! $author_id ) {
				return $this->error->add( 'author_creation', __( 'Error creating author.', 'lifterlms' ) );
			}
			unset( $raw['author'] );
		} else {
			$author_id = get_current_user_id();
		}


		if ( isset( $raw['access_plans'] ) ) {
			// save access plans for later
			$plans = $raw['access_plans'];
			unset( $raw['access_plans'] );
		} else {
			$plans = false;
		}


		// save sections for later
		$sections = $raw['sections'];
		unset( $raw['sections'] );

		// save terms for later
		$terms = array();
		if ( isset( $raw['difficulty'] ) ) {
			$terms['difficulty'] = array( $raw['difficulty'] );
		}
		foreach( array( 'categories', 'tags', 'tracks' ) as $t ) {
			if ( isset( $raw[ $t ] ) ) {
				$terms[ $t ] = $raw[ $t ];
				unset( $raw[ $t ] );
			}
		}

		// insert the course
		$course = new LLMS_Course( 'new', array(
			'post_author' => $author_id,
			'post_content' => $raw['content'],
			'post_date' => $this->format_date( $raw['date'] ),
			'post_excerpt' => $raw['excerpt'],
			'post_modified' => $this->format_date( $raw['modified'] ),
			'post_status' => $raw['status'],
			'post_title' => $raw['title'],
		) );

		if ( ! $course->get( 'id' ) ) {
			return $this->error->add( 'course_creation', __( 'Error creating course', 'lifterlms' ) );
		}

		$this->increment( 'courses' );

		// unset the post props we've already added via the creation args
		unset( $raw['content'], $raw['date'], $raw['excerpt'], $raw['name'], $raw['modified'], $raw['modified'], $raw['status'], $raw['title'], $raw['type'] );

		// store the from the import if there is one
		if ( isset( $raw['id'] ) ) {
			$course->set( 'generated_from_id', $raw['id'] );
			$this->tempids['courses'][ $raw['id'] ] = $course->get( 'id' );
			unset( $raw['id'] );
		}

		// set featured image
		if ( isset( $raw['featured_image'] ) ) {
			$this->set_featured_image( $raw['featured_image'], $course->get( 'id' ) );
			unset( $raw['featured_image'] );
		}

		// set all metadata
		foreach( $raw as $key => $val ) {
			$course->set( $key, $val );
		}

		// add terms to our course
		$this->add_course_terms( $course->get( 'id' ), $terms );

		// do access plans
		if ( $plans ) {
			foreach ( $plans as $plan ) {

				$this->create_access_plan( $plan, $course->get( 'id' ), $author_id );

			}
		}

	}

	/**
	 * Ensure raw dates are correctly formatted to create a post date
	 * falls back to current date if no date is supplied
	 * @param    string     $raw_date  raw date from raw object
	 * @return   string
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function format_date( $raw_date = null ) {

		if ( ! $raw_date ) {
			return current_time( 'mysql' );
		} else {
			return date( 'Y-m-d H:i:s' , strtotime( $raw_date ) );
		}

	}
	/**
	 * Get an array of valid LifterLMS generators
	 * External Generators shouldn't be using this filter
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	private function get_generators() {
		return apply_filters( 'llms_scaffolding_generators', array(
			'BulkCourseExporter' => 'build_courses',
			'SingleCourseExporter' => 'build_course',
		) );
	}

	public function get_results() {

		if ( $this->is_error() ) {
			return $this->error;
		} else {
			return $this->stats;
		}

	}

	/**
	 * Accepts raw author data and locates an existing author by email or id or creates one
	 * @param    array     $raw  author data
	 *                           if id and email are provided will use id only if it matches the email for user matching that id in the database
	 *                           if no id found, attempts to locate by email
	 *                           if no author found and email provided, creates new user using email
	 *                           falls back to current user id
	 *                           first_name, last_name, and description can be optionally provided
	 *                           when provided will be used only when creating a new user
	 *
	 * @return   int|void        WP User ID or void when error encountered
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_author_id( $raw ) {

		$author_id = 0;

		// if raw is missing an ID and Email, use current user id
		if ( ! isset( $raw['id'] ) && ! isset( $raw['email'] ) ) {
			$author_id = get_current_user_id();
		} else {

			// if id is set, check if the id matches a user in the DB
			if ( isset( $raw['id'] ) && is_numeric( $raw['id'] ) ) {

				$user = get_user_by( 'ID', $raw['id'] );

				// user exists
				if ( $user ) {

					// we have a raw email
					if ( isset( $raw['email'] ) ) {

						// raw email matches found user's email
						if ( $user->user_email == $raw['email'] ) {
							$author_id = $user->ID;
						}

					} // use the author id
					else {
						$author_id = $user->ID;
					}

				}

			}

			if ( ! $author_id ) {

				if ( isset( $raw['email'] ) ) {

					// see if we have a user that matches by email
					$user = get_user_by( 'email', $raw['email'] );

					// user exists, use this user
					if ( $user ) {
						$author_id = $user->ID;
					}

				}

			}

			// no author id, create a new one using the email
			if ( ! $author_id && isset( $raw['email'] ) ) {

				$data = array(
					'role' => 'administrator',
					'user_email' => $raw['email'],
					'user_login' => LLMS_Person_Handler::generate_username( $raw['email'] ),
					'user_pass' => wp_generate_password(),
				);

				if ( isset( $raw['first_name'] ) && isset( $raw['last_name'] ) ) {
					$data['display_name'] = $raw['first_name'] . ' ' . $raw['last_name'];
					$data['first_name'] = $raw['first_name'];
					$data['last_name'] = $raw['last_name'];
				}

				if ( isset( $raw['description'] ) ) {
					$data['description'] = $raw['description'];
				}

				$author_id = wp_insert_user( apply_filters( 'llms_scaffolding_new_author_data', $data ), $raw );

				// increment stats
				if ( ! is_wp_error( $author_id ) ) {
					$this->increment( 'authors' );
				}

			}

		}

		if ( is_wp_error( $author_id ) ) {
			return $this->error->add( $author_id->get_error_code(), $author_id->get_error_message() );
		}

		return apply_filters( 'llms_scaffolding_get_author_id', $author_id, $raw );

	}

	/**
	 * Get a WP Term ID for a term by taxonomy and term name
	 * attempts to find a given term by name first to pervent duplicates during imports
	 * @param    string     $term_name  term name
	 * @param    string     $tax        taxonomy slug
	 * @return   int|void              term id or void when error
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_term_id( $term_name, $tax ) {

		$term = get_term_by( 'name', $term_name, $tax, ARRAY_A );

		// not found, create it
		if ( ! $term ) {

			$term = wp_insert_term( $term_name, $tax );

			if ( is_wp_error( $term ) ) {
				return $this->error->add( 'term-creation', sprintf( __( 'Error creating new term "%s"', 'lifterlms' ), $term_name ) );
			} else {
				$this->increment( 'terms' );
			}

		}

		return $term['term_id'];

	}

	private function increment( $type ) {
		if ( isset( $this->stats[ $type ] ) ) {
			$this->stats[ $type ]++;
		}
	}

	public function is_error() {
		return ( $this->error->get_error_messages() );
	}

	/**
	 * Saves an image (from URL) to the media library and sets it as the featured image for a given post
	 * @param    string     $image_url  URL to an image
	 * @param    int        $post_id    WP Post ID
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function set_featured_image( $image_url, $post_id ) {

		global $wpdb;

		// save the image in the medialib
		$img_src = media_sideload_image( $image_url, $post_id, null, 'src' );

		if ( ! is_wp_error( $img_src ) ) {
	    	$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid = %s", array( $img_src ) ) );
			set_post_thumbnail( $post_id, $id );
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
	 * Get an LLMS Course from a raw course array
	 * always creates a course, runs no checks for potential duplicates
	 * @param    array     $raw_course  array of raw course data
	 * @return   obj|false              Instance of the new LLMS_Course or false on error
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	// public function create_course( $raw_course, $author_id ) {

	// 	// create the new course
	// 	$course = new LLMS_Course( 'new', array(
	// 		'post_author' => $author_id,
	// 		'post_content' => $raw_course['description'],
	// 		'post_date' => $this->format_date( $raw_course['date_created'] ),
	// 		'post_excerpt' => $raw_course['public_description'],
	// 		'post_modified' => $this->format_date( $raw_course['date_updated'] ),
	// 		'post_status' => $raw_course['status'],
	// 		'post_title' => $raw_course['title'],
	// 	) );

	// 	// course wasen't created
	// 	if ( ! $course->get( 'id' ) ) {
	// 		$this->error->add( 'course-creation', sprintf( __( 'Failed during creation of course "%s"', 'lifterlms' ), $raw_course['title'] ) );
	// 		return false;
	// 	}
	// 	// add all meta info
	// 	elseif ( ! empty( $raw_course['settings'] ) && is_array( $raw_course['settings'] ) ) {

	// 		$this->increment( 'courses' );

	// 		// handle prerequities
	// 		// rename the prereq to a temp field to be renamed later
	// 		if ( $raw_course['settings']['prerequisite'] ) {
	// 			$raw_course['settings']['temp_prerequisite'] = $raw_course['settings']['prerequisite'];
	// 			unset( $raw_course['settings']['prerequisite'] );
	// 		}

	// 		foreach ( $raw_course['settings'] as $key => $val ) {
	// 			$course->set( $key, $val );
	// 		}

	// 	}

	// 	$this->add_course_terms( $course, $raw_course );

	// 	return $course;

	// }

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
	 * @since    3.3.0
	 * @version  3.3.0
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




}
