<?php
/**
 * Generate LMS Content from export files or raw arrays of data
 *
 * @package LifterLMS/Classes
 *
 * @since 3.3.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Generator class.
 *
 * @since 3.3.0
 * @since 3.30.2 Added hooks and made numerous private functions public to expand extendability.
 * @since 3.36.3 New method: is_generator_valid()
 *               Bugfix: Fix return of `set_generator()`.
 * @since [version] Add sideloading of images found in imported post content.
 */
class LLMS_Generator_Courses extends LLMS_Abstract_Generator_Posts {

	/**
	 * Associate raw tempids with actual created ids
	 *
	 * @var array
	 */
	protected $tempids = array(
		'course' => array(),
		'lesson' => array(),
	);

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
	protected function add_course_terms( $course_id, $raw_terms ) {

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
	 * Generator called when cloning a lesson
	 *
	 * @since 3.14.8
	 *
	 * @return int|WP_Error WP_Post ID of the created lesson on success and an error object on failure.
	 */
	public function clone_lesson( $raw ) {

		$raw['title'] .= sprintf( ' (%s)', __( 'Clone', 'lifterlms' ) );
		return $this->create_lesson( $raw, 0, '', '' );

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
	public function generate_course( $raw ) {

		$new_raw = array();

		foreach ( array( '_generator', '_version', '_source' ) as $meta ) {
			if ( isset( $raw[ $meta ] ) ) {
				$new_raw[ $meta ] = $raw[ $meta ];
				unset( $raw[ $meta ] );
			}
		}

		$new_raw['courses'] = array( $raw );
		$this->generate_courses( $new_raw );

	}

	/**
	 * Generator called for bulk course imports
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	protected function generate_courses( $raw ) {

		if ( empty( $raw['courses'] ) ) {
			$this->error->add( 'required', __( 'Missing required "courses" array', 'lifterlms' ) );
		} elseif ( ! is_array( $raw['courses'] ) ) {
			$this->error->add( 'format', __( '"courses" must be an array', 'lifterlms' ) );
		} else {

			foreach ( $raw['courses'] as $raw_course ) {

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
	 * @since [version] Sideload images attached to the post.
	 *
	 * @param array $raw                Raw Access Plan Data
	 * @param int   $course_id          WP Post ID of a LLMS Course to assign the access plan to
	 * @param int   $fallback_author_id WP User ID to use for the access plan author if no author is supplied in the raw data
	 * @return int
	 */
	protected function create_access_plan( $raw, $course_id, $fallback_author_id = null ) {

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

		// $this->increment( 'plans' );

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

		$this->sideload_images( $plan, $raw );

		return $plan->get( 'id' );

	}

	/**
	 * Create a new course
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since 4.3.3 Use an empty string in favor of `null` for empty `post_content` and `post_excerpt` fields.
	 * @since [version] Import images and reusable blocks found in the post's content.
	 *
	 * @param array $raw Raw course data.
	 * @return void|int
	 */
	protected function create_course( $raw ) {

		/**
		 * Filter raw course import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw       Raw course data array.
		 * @param LLMS_Generator $generator Generator instance.
		 */
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

		// $this->increment( 'courses' );
		// $this->record_generation( $course->get( 'id' ), 'course' );

		// Save the tempid.
		$this->store_temp_id( $raw, $course );

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

		$this->sideload_images( $course, $raw );
		$this->handle_reusable_blocks( $course, $raw );

		/**
		 * Action triggered immediately following generation of a new course
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Course    $course    Generated course object.
		 * @param array          $raw       Original raw course data array.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		do_action( 'llms_generator_new_course', $course, $raw, $this );

		return $course->get( 'id' );

	}

	/**
	 * Create a new lesson
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since 4.3.3 Use an empty string in favor of `null` for empty `post_content` and `post_excerpt` fields.
	 * @since [version] Import images and reusable blocks found in the post's content.
	 *
	 * @param array $raw                Raw lesson data.
	 * @param int   $order              Lesson order within the section (starts at 1).
	 * @param int   $section_id         WP Post ID of the lesson's parent section.
	 * @param int   $course_id          WP Post ID of the lesson's parent course.
	 * @param int   $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int|WP_Error WP_Post ID of the created lesson on success and an error object on failure.
	 */
	protected function create_lesson( $raw, $order, $section_id, $course_id, $fallback_author_id = null ) {

		/**
		 * Filter raw lesson import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw                Raw lesson data array.
		 * @param int            $order              Lesson order within the section (starts at 1).
		 * @param int            $section_id         WP Post ID of the lesson's parent section.
		 * @param int            $course_id          WP Post ID of the lesson's parent course.
		 * @param int            $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
		 * @param LLMS_Generator $generator          Generator instance.
		 */
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

		// $this->increment( 'lessons' );
		// $this->record_generation( $lesson->get( 'id' ), 'lesson' );

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

		$this->sideload_images( $lesson, $raw );
		$this->handle_reusable_blocks( $lesson, $raw );

		/**
		 * Action triggered immediately following generation of a new lesson
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Lesson    $lesson    Generated lesson object.
		 * @param array          $raw       Original raw lesson data array.
		 * @param LLMS_Generator $generator Generator instance.
		 */
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
	 * @since [version] Sideload images attached to the post.
	 *
	 * @param array $raw                Raw quiz data.
	 * @param int   $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int WP_Post ID of the Quiz.
	 */
	protected function create_quiz( $raw, $fallback_author_id = null ) {

		/**
		 * Filter raw quiz import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw                Raw quiz data array.
		 * @param int            $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
		 * @param LLMS_Generator $generator          Generator instance.
		 */
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

		// $this->increment( 'quizzes' );

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

		$this->sideload_images( $quiz, $raw );

		/**
		 * Action triggered immediately following generation of a new quiz
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Quiz      $quiz      Generated quiz object.
		 * @param array          $raw       Original raw quiz data array.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		do_action( 'llms_generator_new_quiz', $quiz, $raw, $this );

		return $quiz->get( 'id' );

	}

	/**
	 * Creates a new question
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since [version] Attempt to sideload images found in the imported post's content and image choices.
	 *
	 * @param array $raw       Raw question data.
	 * @param obj   $manager   Question manager instance.
	 * @param int   $author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
	 * @return int The WP_Post ID of the generated question.
	 */
	protected function create_question( $raw, $manager, $author_id ) {

		/**
		 * Filter raw question import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw       Raw quiz data array.
		 * @param obj            $manager   Question manager instance.
		 * @param int            $author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
		 * @param LLMS_Generator $generator Generator instance.
		 */
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

		// $this->increment( 'questions' );

		$question = llms_get_post( $question_id );

		if ( isset( $raw['choices'] ) ) {
			foreach ( $raw['choices'] as $choice ) {
				unset( $choice['question_id'] );
				$question->create_choice( $this->maybe_sideload_choice_image( $choice, $question_id ) );
			}
		}

		// Set all metadata.
		foreach ( array_keys( $question->get_properties() ) as $key ) {
			if ( isset( $raw[ $key ] ) ) {
				$question->set( $key, $raw[ $key ] );
			}
		}

		$this->sideload_images( $question, $raw );

		/**
		 * Action triggered immediately following generation of a new question
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Question  $question  Generated question object.
		 * @param array          $raw       Original raw question data array.
		 * @param obj            $manager   Question manager instance.
		 * @param LLMS_Generator $generator Generator instance.
		 */
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
	protected function create_section( $raw, $order, $course_id, $fallback_author_id = null ) {

		/**
		 * Filter raw section import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw                Raw quiz data array.
		 * @param int            $order              Order within the course (starts at 1).
		 * @param int            $course_id          WP Post ID of the parent course.
		 * @param int            $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the lesson.
		 * @param LLMS_Generator $generator          Generator instance.
		 */
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

		// $this->increment( 'sections' );

		$section->set( 'parent_course', $course_id );
		$section->set( 'order', $order );

		if ( isset( $raw['lessons'] ) ) {
			foreach ( $raw['lessons'] as $lesson_order => $lesson ) {
				$this->create_lesson( $lesson, $lesson_order + 1, $section->get( 'id' ), $course_id, $author_id );
			}
		}

		/**
		 * Action triggered immediately following generation of a new section
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Section   $section   Generated section object.
		 * @param array          $raw       Original raw section data array.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		do_action( 'llms_generator_new_section', $section, $raw, $this );

		return $section->get( 'id' );

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
	protected function handle_prerequisites() {

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
	 * Determines if a raw question choice object contains image data that should be sideloaded
	 *
	 * @since [version]
	 *
	 * @param array $choice      Raw choice data array.
	 * @param int   $question_id WP_Post ID of the parent question.
	 * @return array Choice data array.
	 */
	protected function maybe_sideload_choice_image( $choice, $question_id ) {

		if ( empty( $choice['choice_type'] ) || 'image' !== $choice['choice_type'] || ! $this->is_image_sideloading_enabled() ) {
			return $choice;
		}

		$id = $this->sideload_image( $question_id, $choice['choice']['src'], 'id' );
		if ( is_wp_error( $id ) ) {
			return $choice;
		}

		$choice['choice']['id']  = $id;
		$choice['choice']['src'] = wp_get_attachment_url( $id );

		return $choice;

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
	protected function store_temp_id( $raw, $obj ) {

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
