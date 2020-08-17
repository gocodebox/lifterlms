<?php
/**
 * Generate LMS Content from export files or raw arrays of data
 *
 * @package LifterLMS/Classes
 *
 * @since 3.3.0
 * @version 4.3.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Generator class.
 *
 * @since 3.3.0
 * @since 3.30.2 Added hooks and made numerous private functions public to expand extendability.
 * @since 3.36.3 New method: is_generator_valid()
 *               Bugfix: Fix return of `set_generator()`.
 */
class LLMS_Generator {

	/**
	 * Instance of WP_Error
	 *
	 * @var obj
	 */
	public $error;

	/**
	 * Default post status when status isn't set in $raw for a given post
	 *
	 * @var string
	 */
	private $default_post_status = 'draft';

	/**
	 * Name of the Generator to use for generation
	 *
	 * @var string
	 */
	private $generator = '';

	/**
	 * Array of generated posts
	 *
	 * @var array
	 */
	private $posts = array();

	/**
	 * Raw contents passed into the generator's constructor
	 *
	 * @var array
	 */
	private $raw = array();

	/**
	 * Type of data to work from
	 * bulk|single
	 *
	 * @var string
	 */
	private $raw_type = '';

	/**
	 * Associate raw tempids with actual created ids
	 *
	 * @var array
	 */
	private $tempids = array(
		'course' => array(),
		'lesson' => array(),
	);

	/**
	 * Array of Stats
	 *
	 * @var int[]
	 */
	private $stats = array(
		'authors'   => 0,
		'courses'   => 0,
		'sections'  => 0,
		'lessons'   => 0,
		'plans'     => 0,
		'quizzes'   => 0,
		'questions' => 0,
		'terms'     => 0,
	);

	/**
	 * Construct a new generator instance with data
	 *
	 * @since 3.3.0
	 *
	 * @param array|string $raw Array or a JSON string of raw content.
	 * @return void
	 */
	public function __construct( $raw ) {

		if ( ! is_array( $raw ) ) {

			$raw = json_decode( $raw, true );

		}

		$this->error = new WP_Error();
		$this->raw   = $raw;

		// For featured image creation via `media_sideload_image()`.
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

	}

	/**
	 * Add taxonomy terms to a course
	 *
	 * @since 3.3.0
	 * @since 3.7.5 Unknown.
	 *
	 * @param obj   $course_id WP_Post ID of a Course.
	 * @param array $raw_terms Array of raw term arrays.
	 * @return void
	 */
	private function add_course_terms( $course_id, $raw_terms ) {

		$taxes = array(
			'course_cat'        => 'categories',
			'course_difficulty' => 'difficulty',
			'course_tag'        => 'tags',
			'course_track'      => 'tracks',
		);

		foreach ( $taxes as $tax => $key ) {

			if ( ! empty( $raw_terms[ $key ] ) && is_array( $raw_terms[ $key ] ) ) {

				// We can only have one difficulty at a time.
				$append = ( 'difficulty' === $key ) ? false : true;

				$terms = array();

				// Find term id or create it.
				foreach ( $raw_terms[ $key ] as $term_name ) {

					if ( empty( $term_name ) ) {
						continue;
					}

					$term_id = $this->get_term_id( $term_name, $tax );
					if ( $term_id ) {
						$terms[] = $term_id;
					}
				}

				wp_set_post_terms( $course_id, $terms, $tax, $append );

			}
		}

	}

	/**
	 * Add custom data to a post based on the 'custom' array
	 *
	 * @since 3.16.11
	 * @since 3.28.3 Add extra slashes around JSON strings.
	 * @since 3.30.2 Skip JSON evaluation for non-string values; make publicly accessible.
	 *
	 * @param int   $post_id WP Post ID.
	 * @param array $raw raw data.
	 * @return void
	 */
	public function add_custom_values( $post_id, $raw ) {
		if ( isset( $raw['custom'] ) ) {
			foreach ( $raw['custom'] as $custom_key => $custom_vals ) {
				foreach ( $custom_vals as $val ) {
					// If $val is a JSON string, add slashes before saving.
					if ( is_string( $val ) && null !== json_decode( $val, true ) ) {
						$val = wp_slash( $val );
					}
					add_post_meta( $post_id, $custom_key, maybe_unserialize( $val ) );
				}
			}
		}
	}

	/**
	 * When called, generates raw content based on the defined generator
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Add before and after generation hooks.
	 *
	 * @return void
	 */
	public function generate() {

		if ( ! empty( $this->generator ) ) {

			global $wpdb;

			$wpdb->hide_errors();

			$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			do_action( 'llms_generator_before_generate', $this );

			try {

				call_user_func( $this->generator );

			} catch ( Exception $e ) {

				$this->error->add( 'exception', $e->getMessage() );

			}

			do_action( 'llms_generator_after_generate', $this );

			if ( $this->is_error() ) {
				$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			} else {
				$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			}
		} else {

			return $this->error->add( 'missing-generator', __( 'No generator supplied.', 'lifterlms' ) );

		}

	}

	/**
	 * Generator called when cloning a lesson
	 *
	 * @since 3.14.8
	 *
	 * @return void
	 */
	private function clone_lesson() {

		$temp = array();

		$this->raw['title'] .= sprintf( ' (%s)', __( 'Clone', 'lifterlms' ) );

		$this->create_lesson( $this->raw, 0, '', '' );

	}

	/**
	 * Generator called for single course imports
	 *
	 * Converts the single course into a format that can be handled by the bulk courses generator
	 * and invokes that generator.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function generate_course() {

		$temp = array();

		foreach ( array( '_generator', '_version', '_source' ) as $meta ) {
			if ( isset( $this->raw[ $meta ] ) ) {
				$temp[ $meta ] = $this->raw[ $meta ];
				unset( $this->raw[ $meta ] );
			}
		}

		$temp['courses'] = array( $this->raw );

		$this->raw = $temp;

		$this->generate_courses();

	}

	/**
	 * Generator called for bulk course imports
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function generate_courses() {

		if ( empty( $this->raw['courses'] ) ) {
			$this->error->add( 'required', __( 'Missing required "courses" array', 'lifterlms' ) );
		} elseif ( ! is_array( $this->raw['courses'] ) ) {
			$this->error->add( 'format', __( '"courses" must be an array', 'lifterlms' ) );
		} else {

			foreach ( $this->raw['courses'] as $raw_course ) {

				unset( $raw_course['_generator'], $raw_course['_version'] );

				$this->create_course( $raw_course );

			}
		}

		$this->handle_prerequisites();

	}

	/**
	 * Create a new access plan
	 *
	 * @since 3.3.0
	 * @since 3.7.3 Unknown.
	 * @since 4.3.3 Use an empty string in favor of `null` for an empty `post_content` field.
	 *
	 * @param array $raw                Raw Access Plan Data
	 * @param int   $course_id          WP Post ID of a LLMS Course to assign the access plan to
	 * @param int   $fallback_author_id WP User ID to use for the access plan author if no author is supplied in the raw data
	 * @return int
	 */
	private function create_access_plan( $raw, $course_id, $fallback_author_id = null ) {

		$author_id = $this->get_author_id_from_raw( $raw, $fallback_author_id );
		if ( isset( $raw['author'] ) ) {
			unset( $raw['author'] );
		}

		// Insert the plan.
		$plan = new LLMS_Access_Plan(
			'new',
			array(
				'post_author'   => $author_id,
				'post_content'  => isset( $raw['content'] ) ? $raw['content'] : '',
				'post_date'     => isset( $raw['date'] ) ? $this->format_date( $raw['date'] ) : null,
				'post_modified' => isset( $raw['modified'] ) ? $this->format_date( $raw['modified'] ) : null,
				'post_status'   => isset( $raw['status'] ) ? $raw['status'] : $this->get_default_post_status(),
				'post_title'    => $raw['title'],
			)
		);

		$this->increment( 'plans' );

		unset( $raw['content'], $raw['date'], $raw['modified'], $raw['name'], $raw['status'], $raw['title'] );

		unset( $raw['product_id'] );
		$plan->set( 'product_id', $course_id );

		// Store the id from the import if there is one.
		if ( isset( $raw['id'] ) ) {
			$plan->set( 'generated_from_id', $raw['id'] );
			unset( $raw['id'] );
		}

		foreach ( $raw as $key => $val ) {
			$plan->set( $key, $val );
		}

		return $plan->get( 'id' );

	}

	/**
	 * Create a new course
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since 4.3.3 Use an empty string in favor of `null` for empty `post_content` and `post_excerpt` fields.
	 *
	 * @param array $raw Raw course data.
	 * @return void|int
	 */
	private function create_course( $raw ) {

		$raw = apply_filters( 'llms_generator_before_new_course', $raw, $this );

		$author_id = $this->get_author_id_from_raw( $raw );
		if ( isset( $raw['author'] ) ) {
			unset( $raw['author'] );
		}

		// Insert the course.
		$course = new LLMS_Course(
			'new',
			array(
				'post_author'   => $author_id,
				'post_content'  => isset( $raw['content'] ) ? $raw['content'] : '',
				'post_date'     => isset( $raw['date'] ) ? $this->format_date( $raw['date'] ) : null,
				'post_excerpt'  => isset( $raw['excerpt'] ) ? $raw['excerpt'] : '',
				'post_modified' => isset( $raw['modified'] ) ? $this->format_date( $raw['modified'] ) : null,
				'post_status'   => apply_filters( 'llms_generator_course_status', $this->get_default_post_status(), $raw, $this ),
				'post_title'    => $raw['title'],
			)
		);

		if ( ! $course->get( 'id' ) ) {
			return $this->error->add( 'course_creation', __( 'Error creating course', 'lifterlms' ) );
		}

		$this->increment( 'courses' );
		$this->record_generation( $course->get( 'id' ), 'course' );

		// Save the tempid.
		$tempid = $this->store_temp_id( $raw, $course );

		// Set all metadata.
		foreach ( array_keys( $course->get_properties() ) as $key ) {
			if ( isset( $raw[ $key ] ) ) {
				$course->set( $key, $raw[ $key ] );
			}
		}

		// Add custom meta.
		$this->add_custom_values( $course->get( 'id' ), $raw );

		// Set featured image.
		if ( isset( $raw['featured_image'] ) ) {
			$this->set_featured_image( $raw['featured_image'], $course->get( 'id' ) );
		}

		// Add terms to our course.
		$terms = array();
		if ( isset( $raw['difficulty'] ) ) {
			$terms['difficulty'] = array( $raw['difficulty'] );
		}
		foreach ( array( 'categories', 'tags', 'tracks' ) as $t ) {
			if ( isset( $raw[ $t ] ) ) {
				$terms[ $t ] = $raw[ $t ];
			}
		}
		$this->add_course_terms( $course->get( 'id' ), $terms );

		// Create all access plans.
		if ( isset( $raw['access_plans'] ) ) {
			foreach ( $raw['access_plans'] as $plan ) {
				$this->create_access_plan( $plan, $course->get( 'id' ), $author_id );
			}
		}

		// Create all sections.
		if ( isset( $raw['sections'] ) ) {
			foreach ( $raw['sections'] as $order => $section ) {
				$this->create_section( $section, $order + 1, $course->get( 'id' ), $author_id );
			}
		}

		do_action( 'llms_generator_new_course', $course, $raw, $this );

		return $course->get( 'id' );

	}

	/**
	 * Create a new lesson
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since 4.3.3 Use an empty string in favor of `null` for empty `post_content` and `post_excerpt` fields.
	 *
	 * @param array $raw                Raw lesson data.
	 * @param int   $order              Lesson order within the section (starts at 1).
	 * @param int   $section_id         WP Post ID of the lesson's parent section.
	 * @param int   $course_id          WP Post ID of the lesson's parent course.
	 * @param int   $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int|WP_Error WP_Post ID of the created lesson on success and an error object on failure.
	 */
	private function create_lesson( $raw, $order, $section_id, $course_id, $fallback_author_id = null ) {

		$raw = apply_filters( 'llms_generator_before_new_lesson', $raw, $order, $section_id, $course_id, $fallback_author_id, $this );

		$author_id = $this->get_author_id_from_raw( $raw, $fallback_author_id );
		if ( isset( $raw['author'] ) ) {
			unset( $raw['author'] );
		}

		// Insert the course.
		$lesson = new LLMS_lesson(
			'new',
			array(
				'post_author'   => $author_id,
				'post_content'  => isset( $raw['content'] ) ? $raw['content'] : '',
				'post_date'     => isset( $raw['date'] ) ? $this->format_date( $raw['date'] ) : null,
				'post_excerpt'  => isset( $raw['excerpt'] ) ? $raw['excerpt'] : '',
				'post_modified' => isset( $raw['modified'] ) ? $this->format_date( $raw['modified'] ) : null,
				'post_status'   => isset( $raw['status'] ) ? $raw['status'] : $this->get_default_post_status(),
				'post_title'    => $raw['title'],
			)
		);

		if ( ! $lesson->get( 'id' ) ) {
			return $this->error->add( 'lesson_creation', __( 'Error creating lesson', 'lifterlms' ) );
		}

		$this->increment( 'lessons' );
		$this->record_generation( $lesson->get( 'id' ), 'lesson' );

		// Save the tempid.
		$tempid = $this->store_temp_id( $raw, $lesson );

		// Set featured image.
		if ( isset( $raw['featured_image'] ) ) {
			$this->set_featured_image( $raw['featured_image'], $lesson->get( 'id' ) );
		}

		$lesson->set( 'parent_course', $course_id );
		$lesson->set( 'parent_section', $section_id );
		$lesson->set( 'order', $order );

		// Can't trust these if they exist.
		if ( isset( $raw['parent_course'] ) ) {
			unset( $raw['parent_course'] );
		}
		if ( isset( $raw['parent_section'] ) ) {
			unset( $raw['parent_section'] );
		}

		if ( ! empty( $raw['quiz'] ) ) {
			$raw['quiz']['lesson_id'] = $lesson->get( 'id' );
			$raw['quiz']              = $this->create_quiz( $raw['quiz'], $author_id );
		}

		// Set all metadata.
		foreach ( array_keys( $lesson->get_properties() ) as $key ) {
			if ( isset( $raw[ $key ] ) ) {
				$lesson->set( $key, $raw[ $key ] );
			}
		}

		// Add custom meta.
		$this->add_custom_values( $lesson->get( 'id' ), $raw );

		do_action( 'llms_generator_new_lesson', $lesson, $raw, $this );

		return $lesson->get( 'id' );

	}

	/**
	 * Creates a new quiz
	 * Creates all questions within the quiz as well
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since 4.3.3 Use an empty string in favor of `null` for an empty `post_content` field.
	 *
	 * @param array $raw                Raw quiz data.
	 * @param int   $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int WP_Post ID of the Quiz.
	 */
	private function create_quiz( $raw, $fallback_author_id = null ) {

		$raw = apply_filters( 'llms_generator_before_new_quiz', $raw, $fallback_author_id, $this );

		$author_id = $this->get_author_id_from_raw( $raw, $fallback_author_id );
		if ( isset( $raw['author'] ) ) {
			unset( $raw['author'] );
		}

		// Insert the course.
		$quiz = new LLMS_Quiz(
			'new',
			array(
				'post_author'   => $author_id,
				'post_content'  => isset( $raw['content'] ) ? $raw['content'] : '',
				'post_date'     => isset( $raw['date'] ) ? $this->format_date( $raw['date'] ) : null,
				'post_modified' => isset( $raw['modified'] ) ? $this->format_date( $raw['modified'] ) : null,
				'post_status'   => isset( $raw['status'] ) ? $raw['status'] : $this->get_default_post_status(),
				'post_title'    => $raw['title'],
			)
		);

		if ( ! $quiz->get( 'id' ) ) {
			return $this->error->add( 'quiz_creation', __( 'Error creating quiz', 'lifterlms' ) );
		}

		$this->increment( 'quizzes' );

		// Set all metadata.
		foreach ( array_keys( $quiz->get_properties() ) as $key ) {
			if ( isset( $raw[ $key ] ) ) {
				$quiz->set( $key, $raw[ $key ] );
			}
		}

		if ( isset( $raw['questions'] ) ) {
			$manager = $quiz->questions();
			foreach ( $raw['questions'] as $question ) {
				$this->create_question( $question, $manager, $author_id );
			}
		}

		// Add custom meta.
		$this->add_custom_values( $quiz->get( 'id' ), $raw );

		do_action( 'llms_generator_new_quiz', $quiz, $raw, $this );

		return $quiz->get( 'id' );

	}

	/**
	 * Creates a new question
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 *
	 * @param array $raw       Raw question data.
	 * @param int   $author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int The WP_Post ID of the generated question.
	 */
	private function create_question( $raw, $manager, $author_id ) {

		$raw = apply_filters( 'llms_generator_before_new_question', $raw, $manager, $author_id, $this );

		unset( $raw['parent_id'] );

		$question_id = $manager->create_question(
			array_merge(
				array(
					'post_status' => 'publish',
					'post_author' => $author_id,
				),
				$raw
			)
		);

		if ( ! $question_id ) {
			return $this->error->add( 'question_creation', __( 'Error creating question', 'lifterlms' ) );
		}

		$this->increment( 'questions' );

		$question = llms_get_post( $question_id );

		if ( isset( $raw['choices'] ) ) {
			foreach ( $raw['choices'] as $choice ) {
				unset( $choice['question_id'] );
				$question->create_choice( $choice );
			}
		}

		// Set all metadata.
		foreach ( array_keys( $question->get_properties() ) as $key ) {
			if ( isset( $raw[ $key ] ) ) {
				$question->set( $key, $raw[ $key ] );
			}
		}

		do_action( 'llms_generator_new_question', $question, $raw, $manager, $this );

		return $question->get( 'id' );

	}

	/**
	 * Creates a new section
	 *
	 * Creates all lessons within the section data.
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 *
	 * @param array $raw                Raw section data.
	 * @param int   $order              Order within the course (starts at 1).
	 * @param int   $course_id          WP Post ID of the parent course.
	 * @param int   $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int The WP_Post ID of the generated section.
	 */
	private function create_section( $raw, $order, $course_id, $fallback_author_id = null ) {

		$raw = apply_filters( 'llms_generator_before_new_section', $raw, $order, $course_id, $fallback_author_id, $this );

		$author_id = $this->get_author_id_from_raw( $raw, $fallback_author_id );

		// Insert the course.
		$section = new LLMS_Section(
			'new',
			array(
				'post_author'   => $author_id,
				'post_date'     => isset( $raw['date'] ) ? $this->format_date( $raw['date'] ) : null,
				'post_modified' => isset( $raw['modified'] ) ? $this->format_date( $raw['modified'] ) : null,
				'post_status'   => isset( $raw['status'] ) ? $raw['status'] : $this->get_default_post_status(),
				'post_title'    => $raw['title'],
			)
		);

		if ( ! $section->get( 'id' ) ) {
			return $this->error->add( 'section_creation', __( 'Error creating section', 'lifterlms' ) );
		}

		$this->increment( 'sections' );

		$section->set( 'parent_course', $course_id );
		$section->set( 'order', $order );

		if ( isset( $raw['lessons'] ) ) {
			foreach ( $raw['lessons'] as $lesson_order => $lesson ) {
				$this->create_lesson( $lesson, $lesson_order + 1, $section->get( 'id' ), $course_id, $author_id );
			}
		}

		do_action( 'llms_generator_new_section', $section, $raw, $this );

		return $section->get( 'id' );

	}

	/**
	 * Ensure raw dates are correctly formatted to create a post date
	 *
	 * Falls back to current date if no date is supplied.
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Made publicly accessible.
	 *
	 * @param string $raw_date Raw date from raw object.
	 * @return string
	 */
	public function format_date( $raw_date = null ) {

		if ( ! $raw_date ) {
			return current_time( 'mysql' );
		} else {
			return date( 'Y-m-d H:i:s', strtotime( $raw_date ) );
		}

	}

	/**
	 * Accepts raw author data and locates an existing author by email or id or creates one
	 *
	 * @since 3.3.0
	 * @since 4.3.3 Use strict string comparator.
	 *
	 * @param array $raw Author data.
	 *                   If id and email are provided will use id only if it matches the email for user matching that id in the database.
	 *                   If no id found, attempts to locate by email.
	 *                   If no author found and email provided, creates new user using email.
	 *                   Falls back to current user id.
	 *                   First_name, last_name, and description can be optionally provided.
	 *                   When provided will be used only when creating a new user.
	 * @return int|void A WP_User ID or void when error encountered.
	 */
	private function get_author_id( $raw ) {

		$author_id = 0;

		// If raw is missing an ID and Email, use current user id.
		if ( ! isset( $raw['id'] ) && ! isset( $raw['email'] ) ) {
			$author_id = get_current_user_id();
		} else {

			// If id is set, check if the id matches a user in the DB.
			if ( isset( $raw['id'] ) && is_numeric( $raw['id'] ) ) {

				$user = get_user_by( 'ID', $raw['id'] );

				// User exists.
				if ( $user ) {

					// We have a raw email.
					if ( isset( $raw['email'] ) ) {

						// Raw email matches found user's email.
						if ( $user->user_email === $raw['email'] ) {
							$author_id = $user->ID;
						}
					} else {
						$author_id = $user->ID;
					}
				}
			}

			if ( ! $author_id ) {

				if ( isset( $raw['email'] ) ) {

					// See if we have a user that matches by email.
					$user = get_user_by( 'email', $raw['email'] );

					// User exists, use this user.
					if ( $user ) {
						$author_id = $user->ID;
					}
				}
			}

			// No author id, create a new one using the email.
			if ( ! $author_id && isset( $raw['email'] ) ) {

				$data = array(
					'role'       => 'administrator',
					'user_email' => $raw['email'],
					'user_login' => LLMS_Person_Handler::generate_username( $raw['email'] ),
					'user_pass'  => wp_generate_password(),
				);

				if ( isset( $raw['first_name'] ) && isset( $raw['last_name'] ) ) {
					$data['display_name'] = $raw['first_name'] . ' ' . $raw['last_name'];
					$data['first_name']   = $raw['first_name'];
					$data['last_name']    = $raw['last_name'];
				}

				if ( isset( $raw['description'] ) ) {
					$data['description'] = $raw['description'];
				}

				$author_id = wp_insert_user( apply_filters( 'llms_generator_new_author_data', $data ), $raw );

				// Increment stats.
				if ( ! is_wp_error( $author_id ) ) {
					$this->increment( 'authors' );
				}
			}
		}

		if ( is_wp_error( $author_id ) ) {
			return $this->error->add( $author_id->get_error_code(), $author_id->get_error_message() );
		}

		return apply_filters( 'llms_generator_get_author_id', $author_id, $raw );

	}

	/**
	 * Receives a raw array of course, plan, section, lesson, etc data and gets an author id
	 * falls back to optionally supplied fallback id
	 * falls back to current user id
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Made publicly accessible.
	 *
	 * @param array $raw raw data
	 * @param int   $fallback_author_id WP User ID
	 * @return int|WP_Error
	 */
	public function get_author_id_from_raw( $raw, $fallback_author_id = null ) {

		// If author is set, get the author id.
		if ( isset( $raw['author'] ) ) {
			$author_id = $this->get_author_id( $raw['author'] );
		}

		// Fallback to current user.
		if ( empty( $author_id ) ) {
			$author_id = ! empty( $fallback_author_id ) ? $fallback_author_id : get_current_user_id();
		}

		return $author_id;

	}

	/**
	 * Retrieve the default post status for the generated set of posts
	 *
	 * @since 3.7.3
	 * @since 3.30.2 Made publicly accessible.
	 *
	 * @return string
	 */
	public function get_default_post_status() {
		return apply_filters( 'llms_generator_default_post_status', $this->default_post_status, $this );
	}

	/**
	 * Retrieve the array of generated course ids
	 *
	 * @since 3.7.3
	 * @since 3.14.8 Unknown.
	 *
	 * @return array
	 */
	public function get_generated_courses() {
		if ( isset( $this->posts['course'] ) ) {
			return $this->posts['course'];
		}
		return array();
	}

	/**
	 * Retrieve the array of generated post ids
	 *
	 * @since 3.14.8
	 *
	 * @return array
	 */
	public function get_generated_posts() {
		return $this->posts;
	}

	/**
	 * Get an array of valid LifterLMS generators
	 *
	 * @since 3.3.0
	 * @since 3.14.8 Unknown.
	 *
	 * @return array
	 */
	private function get_generators() {
		return apply_filters(
			'llms_generators',
			array(
				'LifterLMS/BulkCourseExporter'    => array( $this, 'generate_courses' ),
				'LifterLMS/BulkCourseGenerator'   => array( $this, 'generate_courses' ),
				'LifterLMS/SingleCourseCloner'    => array( $this, 'generate_course' ),
				'LifterLMS/SingleCourseExporter'  => array( $this, 'generate_course' ),
				'LifterLMS/SingleCourseGenerator' => array( $this, 'generate_course' ),
				'LifterLMS/SingleLessonCloner'    => array( $this, 'clone_lesson' ),
			)
		);
	}

	/**
	 * Get the results of the generate function
	 *
	 * @since 3.3.0
	 *
	 * @return int[]|WP_Error Array of stats on success and an error object on failure.
	 */
	public function get_results() {

		if ( $this->is_error() ) {
			return $this->error;
		} else {
			return $this->stats;
		}

	}

	/**
	 * Get a WP Term ID for a term by taxonomy and term name
	 *
	 * Attempts to find a given term by name first to prevent duplicates during imports.
	 *
	 * @since 3.3.0
	 *
	 * @param string $term_name Term name.
	 * @param string $tax       Taxonomy slug.
	 * @return int|void
	 */
	private function get_term_id( $term_name, $tax ) {

		$term = get_term_by( 'name', $term_name, $tax, ARRAY_A );

		// Not found, create it.
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

	/**
	 * Updates course and lesson prerequisites
	 *
	 * If the prerequisite was included in the import, updates to the new imported version.
	 *
	 * If the prereq is not included but the source matches, leaves the prereq intact as long as the prereq exists.
	 *
	 * Otherwise removes prerequisite data from the new course / lesson.
	 *
	 * Removes prereq track associations if there's no source or source doesn't match
	 * or if the track doesn't exist.
	 *
	 * @since 3.3.0
	 * @since 3.24.0 Unknown.
	 *
	 * @return void
	 */
	private function handle_prerequisites() {

		foreach ( array( 'course', 'lesson' ) as $obj_type ) {

			$ids = $this->tempids[ $obj_type ];

			// Courses have two kinds of prereqs.
			$has_prereq_param = ( 'course' === $obj_type ) ? 'course' : null;

			// Loop through all then created lessons.
			foreach ( $ids as $old_id => $new_id ) {

				// Instantiate the new instance of the object.
				$obj = llms_get_post( $new_id );

				// If this is a course and there isn't a source or the source doesn't match the current site.
				// We should remove the track prerequisites.
				if ( 'course' === $obj_type && ( ! isset( $raw['_source'] ) || get_site_url() !== $raw['_source'] ) ) {

					// Remove prereq track settings.
					if ( $obj->has_prerequisite( 'course_track' ) ) {
						$obj->set( 'prerequisite_track', 0 );
						if ( ! $obj->has_prerequisite( 'course' ) ) {
							$obj->set( 'has_prerequisite', 'no' );
						}
					}
				}

				// If the object has a prereq.
				if ( $obj->has_prerequisite( $has_prereq_param ) ) {

					// Get the old preqeq's id.
					$old_prereq = $obj->get( 'prerequisite' );

					// If the old prereq is a key in the array of created objects.
					// We can replace it with the new id.
					if ( in_array( $old_prereq, array_keys( $ids ) ) ) {

						$obj->set( 'prerequisite', $ids[ $old_prereq ] );

					} elseif ( ! isset( $raw['_source'] ) || get_site_url() !== $raw['_source'] ) {

						$obj->set( 'has_prerequisite', 'no' );
						$obj->set( 'prerequisite', 0 );

					} else {
						$post = get_post( $old_prereq );
						// Post doesn't exist or the post type doesn't match, get rid of it.
						if ( ! $post || $obj_type !== $post->post_type ) {

							$obj->set( 'has_prerequisite', 'no' );
							$obj->set( 'prerequisite', 0 );

						}
					}
				}
			}
		}

	}

	/**
	 * Increments a stat in the stats object
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Made publicly accessible; change to automatically add new items to the stats if they aren't set.
	 *
	 * @param string $type key of the stat to increment.
	 * @return void
	 */
	public function increment( $type ) {
		if ( ! isset( $this->stats[ $type ] ) ) {
			$this->stats[ $type ] = 0;
		}
		$this->stats[ $type ]++;
	}

	/**
	 * Determines if there was an error during the running of the generator
	 *
	 * @since 3.3.0
	 * @since 3.16.11 Unknown.
	 *
	 * @return boolean Returns `true` when there was an error and `false` if there's no errors.
	 */
	public function is_error() {
		return ( 0 !== count( $this->error->get_error_messages() ) );
	}

	/**
	 * Determine if a generator is a valid generator.
	 *
	 * @since 3.36.3
	 *
	 * @param string $generator Generator name.
	 * @return bool
	 */
	protected function is_generator_valid( $generator ) {

		return in_array( $generator, array_keys( $this->get_generators() ), true );

	}

	/**
	 * Records a generated post id
	 *
	 * @since 3.14.8
	 *
	 * @param int    $id   WP Post ID of the generated post.
	 * @param string $type Key of the stat to increment.
	 * @return void
	 */
	private function record_generation( $id, $type ) {

		// Add the id to the type array.
		if ( ! isset( $this->posts[ $type ] ) ) {
			$this->posts[ $type ] = array();
		}

		array_push( $this->posts[ $type ], $id );

	}

	/**
	 * Saves an image (from URL) to the media library and sets it as the featured image for a given post
	 *
	 * @since 3.3.0
	 *
	 * @param string $url_or_raw Array of raw data or URL to an image.
	 * @param int    $post_id    WP Post ID.
	 * @return void
	 */
	private function set_featured_image( $url_or_raw, $post_id ) {

		$image_url = '';

		if ( is_array( $url_or_raw ) && isset( $url_or_raw['featured_image'] ) ) {
			$image_url = $url_or_raw['featured_image'];
		} elseif ( is_string( $url_or_raw ) ) {
			$image_url = $url_or_raw;
		}

		if ( ! empty( $image_url ) ) {

			global $wpdb;

			// Save the image in the media library.
			$img_src = media_sideload_image( $image_url, $post_id, null, 'src' );

			if ( ! is_wp_error( $img_src ) ) {
				$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid = %s", array( $img_src ) ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				set_post_thumbnail( $post_id, $id );
			}
		}

	}

	/**
	 * Configure the default post status for generated posts at runtime
	 *
	 * @since 3.7.3
	 *
	 * @param string $status Any valid WP Post Status.
	 * @return void
	 */
	public function set_default_post_status( $status ) {
		$this->default_post_status = $status;
	}

	/**
	 * Sets the generator to use for the current instance
	 *
	 * @since 3.3.0
	 * @since 3.36.3 Fix error causing `null` to be returned instead of expected `WP_Error`.
	 *               Return the generator name on success instead of void.
	 *
	 * @param string $generator Generator string, eg: "LifterLMS/SingleCourseExporter"
	 * @return string|WP_Error Name of the generator on success, otherwise an error object.
	 */
	public function set_generator( $generator = null ) {

		// Interpret the generator from the raw data.
		if ( empty( $generator ) ) {

			// No generator can be interpreted.
			if ( ! isset( $this->raw['_generator'] ) ) {

				$this->error->add( 'missing-generator', __( 'The supplied file cannot be processed by the importer.', 'lifterlms' ) );
				return $this->error;

			}

			// Set the generator using the interpreted data.
			return $this->set_generator( $this->raw['_generator'] );

		}

		// Invalid generator.
		if ( ! $this->is_generator_valid( $generator ) ) {
			$this->error->add( 'invalid-generator', __( 'The supplied generator is invalid.', 'lifterlms' ) );
			return $this->error;
		}

		// Set the generator.
		$generators      = $this->get_generators();
		$this->generator = $generators[ $generator ];

		// Return the generator name.
		return $generator;

	}

	/**
	 * Accepts a raw object, finds the raw id and stores it
	 *
	 * @since 3.3.0
	 *
	 * @param array $raw Array of raw data.
	 * @param obj   $obj The LLMS Post Object generated from the raw data.
	 * @return int|false Raw id when present or `false` if no raw id was found.
	 */
	private function store_temp_id( $raw, $obj ) {

		if ( isset( $raw['id'] ) ) {

			// Store the id on the meta table.
			$obj->set( 'generated_from_id', $raw['id'] );

			// Store it in the object for prereq handling later.
			$this->tempids[ $obj->get( 'type' ) ][ $raw['id'] ] = $obj->get( 'id' );

			return $raw['id'];

		}

		return false;

	}

}
