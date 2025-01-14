<?php
/**
 * Generate LMS Content from export files or raw arrays of data
 *
 * @package LifterLMS/Classes
 *
 * @since 4.7.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Generator_Courses class
 *
 * @since 4.7.0
 */
class LLMS_Generator_Courses extends LLMS_Abstract_Generator_Posts {

	/**
	 * Exception code: Raw data missing required data.
	 *
	 * @var int
	 */
	const ERROR_GEN_MISSING_REQUIRED = 2000;

	/**
	 * Exception code: Raw data in an invalid format.
	 *
	 * @var int
	 */
	const ERROR_GEN_INVALID_FORMAT = 2001;

	/**
	 * Add taxonomy terms to a course
	 *
	 * @since 3.3.0
	 * @since 3.7.5 Unknown.
	 * @since 4.7.0 Moved from `LLMS_Generator` and made `protected` instead of `private`.
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
	 * Generator called when cloning a course
	 *
	 * @since 4.13.0
	 *
	 * @param array $raw Raw course data array.
	 * @return int|null WP_Post ID of the generated course or `null` on failure.
	 */
	public function clone_course( $raw ) {
		return $this->generate_course( $this->setup_raw_for_clone( $raw ) );
	}

	/**
	 * Generator called when cloning a lesson
	 *
	 * @since 3.14.8
	 * @since 4.7.0 Moved from `LLMS_Generator` and made `public` instead of `private`.
	 * @since 4.13.0 Use `setup_raw_for_clone()` to normalize the
	 *
	 * @param array $raw Raw data array.
	 * @return int|WP_Error WP_Post ID of the created lesson on success and an error object on failure.
	 */
	public function clone_lesson( $raw ) {
		return $this->create_lesson( $this->setup_raw_for_clone( $raw ), 0, '', '' );
	}

	/**
	 * Generator called for single course imports
	 *
	 * Converts the single course into a format that can be handled by the bulk courses generator
	 * and invokes that generator.
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Moved from `LLMS_Generator` and made `public` instead of `private`.
	 *              Returns an int on success.
	 * @param array $raw Raw data array.
	 * @return int|null WP_Post ID of the generated course or `null` on failure.
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
		$courses            = $this->generate_courses( $new_raw );

		return is_array( $courses ) ? $courses[0] : null;
	}

	/**
	 * Generator called for bulk course imports
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Moved from `LLMS_Generator` to `LLMS_Abstract_Generator_Courses`.
	 *               Updated method access from `private` to `public`.
	 *               Throws an exception in favor of returning `null` when an error is encountered.
	 *               Returns an array of generated course IDs on success.
	 *
	 * @param array $raw Raw data array.
	 * @return void
	 *
	 * @throws Exception When invalid `$raw` data is submitted.
	 */
	public function generate_courses( $raw ) {

		if ( empty( $raw['courses'] ) ) {
			throw new Exception( esc_attr__( 'Raw data is missing the required "courses" array.', 'lifterlms' ), intval( self::ERROR_GEN_MISSING_REQUIRED ) );
		} elseif ( ! is_array( $raw['courses'] ) ) {
			throw new Exception( esc_attr__( 'The raw "courses" item must be an array.', 'lifterlms' ), intval( self::ERROR_GEN_INVALID_FORMAT ) );
		}

		$courses = array();

		foreach ( $raw['courses'] as $raw_course ) {
			unset( $raw_course['_generator'], $raw_course['_version'] );
			$courses[] = $this->create_course( $raw_course );
		}

		$this->handle_prerequisites();

		return $courses;
	}

	/**
	 * Create a new access plan
	 *
	 * @since 3.3.0
	 * @since 3.7.3 Unknown.
	 * @since 4.3.3 Use an empty string in favor of `null` for an empty `post_content` field.
	 * @since 4.7.0 Sideload images attached to the post, use `create_post()` from abstract, add hooks.
	 *
	 * @param array $raw                Raw Access Plan Data.
	 * @param int   $course_id          WP Post ID of a LLMS Course to assign the access plan to.
	 * @param int   $fallback_author_id Optional. WP User ID to use for the access plan author if no author is supplied in the raw data. Default is `null`.
	 *                                  When not supplied the fall back will be on the current user ID.
	 * @return int
	 */
	protected function create_access_plan( $raw, $course_id, $fallback_author_id = null ) {

		/**
		 * Filter raw course import data prior to generation
		 *
		 * @since 4.7.0
		 *
		 * @param array          $raw       Raw course data array.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		$raw = apply_filters( 'llms_generator_before_new_access_plan', $raw, $this );

		// Force course relationship.
		$raw['product_id'] = $course_id;

		$plan = $this->create_post( 'access_plan', $raw, $fallback_author_id );
		if ( ! $plan ) {
			return null;
		}

		/**
		 * Action triggered immediately following generation of a new acess plan
		 *
		 * @since 4.7.0
		 *
		 * @param LLMS_Access_Plan $plan      Generated access plan object.
		 * @param array            $raw       Original raw course data array.
		 * @param LLMS_Generator   $generator Generator instance.
		 */
		do_action( 'llms_generator_new_access_plan', $plan, $raw, $this );

		return $plan->get( 'id' );
	}

	/**
	 * Create a new course
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Added hooks.
	 * @since 4.3.3 Use an empty string in favor of `null` for empty `post_content` and `post_excerpt` fields.
	 * @since 4.7.0 Import images and reusable blocks found in the post's content and use `create_post()` from abstract.
	 *
	 * @param array $raw Raw course data.
	 * @return int
	 *
	 * @throws Exception When an error is encountered during course creation.
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

		// Create the course.
		$course = $this->create_post( 'course', $raw, get_current_user_id() );

		// Add terms to our course.
		$terms = array();
		if ( isset( $raw['difficulty'] ) ) {
			$terms['difficulty'] = array( $raw['difficulty'] );
		}
		foreach ( array( 'categories', 'tags', 'tracks' ) as $tax ) {
			if ( isset( $raw[ $tax ] ) ) {
				$terms[ $tax ] = $raw[ $tax ];
			}
		}
		$this->add_course_terms( $course->get( 'id' ), $terms );

		// Create all access plans.
		if ( isset( $raw['access_plans'] ) ) {
			foreach ( $raw['access_plans'] as $plan ) {
				$this->create_access_plan( $plan, $course->get( 'id' ), $course->get( 'author' ) );
			}
		}

		// Create all sections.
		if ( isset( $raw['sections'] ) ) {
			foreach ( $raw['sections'] as $order => $section ) {
				$this->create_section( $section, ++$order, $course->get( 'id' ), $course->get( 'author' ) );
			}
		}

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
	 * @since 4.7.0 Import images and reusable blocks found in the post's content and use `create_post()` from abstract.
	 *
	 * @param array $raw                Raw lesson data.
	 * @param int   $order              Lesson order within the section (starts at 1).
	 * @param int   $section_id         WP Post ID of the lesson's parent section.
	 * @param int   $course_id          WP Post ID of the lesson's parent course.
	 * @param int   $fallback_author_id Optional. Author ID to use as a fallback if no raw author data supplied for the lesson. Default is `null`.
	 *                                  When not supplied the fall back will be on the current user ID.
	 * @return int
	 *
	 * @throws Exception When an error is encountered during post creation.
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

		// Force some data.
		$raw['parent_course']  = $course_id;
		$raw['parent_section'] = $section_id;
		$raw['order']          = $order;

		$raw_quiz = ! empty( $raw['quiz'] ) ? $raw['quiz'] : false;
		unset( $raw['quiz'] );

		$lesson = $this->create_post( 'lesson', $raw, $fallback_author_id );

		if ( $raw_quiz ) {
			$raw_quiz['lesson_id'] = $lesson->get( 'id' );
			$lesson->set( 'quiz', $this->create_quiz( $raw_quiz, $lesson->get( 'author' ) ) );
		}

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
	 * @since 4.7.0 Sideload images attached to the post  and use `create_post()` from abstract.
	 *
	 * @param array $raw                Raw quiz data.
	 * @param int   $fallback_author_id Optional. Author ID to use as a fallback if no raw author data supplied for the quiz. Default is `null`.
	 *                                  When not supplied the fall back will be on the current user ID.
	 * @return int
	 *
	 * @throws Exception When an error is encountered during post creation.
	 */
	protected function create_quiz( $raw, $fallback_author_id = null ) {

		/**
		 * Filter raw quiz import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw                Raw quiz data array.
		 * @param int            $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the quiz.
		 * @param LLMS_Generator $generator          Generator instance.
		 */
		$raw = apply_filters( 'llms_generator_before_new_quiz', $raw, $fallback_author_id, $this );

		$quiz = $this->create_post( 'quiz', $raw, $fallback_author_id );

		if ( isset( $raw['questions'] ) ) {
			$manager = $quiz->questions();
			foreach ( $raw['questions'] as $question ) {
				$this->create_question( $question, $manager, $quiz->get( 'author' ) );
			}
		}

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
	 * @since 4.7.0 Attempt to sideload images found in the imported post's content and image choices.
	 *
	 * @param array $raw       Raw question data.
	 * @param obj   $manager   Question manager instance.
	 * @param int   $author_id Optional. Author ID to use as a fallback if no raw author data supplied for the question. Default is `null`.
	 *                         When not supplied the fall back will be on the current user ID.
	 * @return int
	 *
	 * @throws Exception When an error is encountered during course creation.
	 */
	protected function create_question( $raw, $manager, $author_id ) {

		/**
		 * Filter raw question import data prior to generation
		 *
		 * @since 3.30.2
		 *
		 * @param array          $raw       Raw quiz data array.
		 * @param obj            $manager   Question manager instance.
		 * @param int            $author_id Optional author ID to use as a fallback if no raw author data supplied for the question.
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
			throw new Exception( esc_attr__( 'Error creating the question post object.', 'lifterlms' ), intval( self::ERROR_CREATE_POST ) );
		}

		$question = llms_get_post( $question_id );

		$this->store_temp_id( $raw, $question );

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
	 * @since 4.7.0 Use `create_post()` from abstract.
	 *
	 * @param array $raw                Raw section data.
	 * @param int   $order              Order within the course (starts at 1).
	 * @param int   $course_id          WP Post ID of the parent course.
	 * @param int   $fallback_author_id Optional. Author ID to use as a fallback if no raw author data supplied for the section.
	 *                                  When not supplied the fall back will be on the current user ID.
	 * @return int
	 *
	 * @throws Exception When an error is encountered during course creation.
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
		 * @param int            $fallback_author_id Optional author ID to use as a fallback if no raw author data supplied for the section.
		 * @param LLMS_Generator $generator          Generator instance.
		 */
		$raw = apply_filters( 'llms_generator_before_new_section', $raw, $order, $course_id, $fallback_author_id, $this );

		$raw['parent_course'] = $course_id;
		$raw['order']         = $order;

		$section = $this->create_post( 'section', $raw, $fallback_author_id );

		if ( isset( $raw['lessons'] ) ) {
			foreach ( $raw['lessons'] as $lesson_order => $lesson ) {
				$this->create_lesson( $lesson, ++$lesson_order, $section->get( 'id' ), $course_id, $section->get( 'author' ) );
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

			$ids = ! empty( $this->tempids[ $obj_type ] ) ? $this->tempids[ $obj_type ] : array();

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
	 * @since 4.7.0
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
	 * Modifies incoming raw data when creating a clone of a course or lesson
	 *
	 * When a clone is created, it will automatically have "(Clone)" appended to the existing title
	 * and will be created with the "Draft" status.
	 *
	 * @since 4.13.0
	 *
	 * @param array $raw Raw data array for the course or lesson.
	 * @return array
	 */
	protected function setup_raw_for_clone( $raw ) {

		/**
		 * Filters the suffix appended to the WP_Post title of a duplicated post when cloning a course or lesson
		 *
		 * @since 4.13.0
		 *
		 * @param string         $status    The WP_Post status to use for the duplicate of the post. Default: "draft".
		 * @param array          $raw       Raw data array passed into the generator.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		$raw['title'] .= apply_filters( 'llms_generator_cloned_post_title_suffix', sprintf( ' (%s)', __( 'Clone', 'lifterlms' ) ), $raw, $this );

		/**
		 * Filters the WP_Post status used for the duplicated post when cloning a course or lesson
		 *
		 * @since 4.13.0
		 *
		 * @param string         $status    The WP_Post status to use for the duplicate of the post. Default: "draft".
		 * @param array          $raw       Raw data array passed into the generator.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		$raw['status'] = apply_filters( 'llms_generator_cloned_post_status', 'draft', $raw, $this );

		return $raw;
	}

	/**
	 * Set all metadata for a given post object.
	 *
	 * This method will only set metadata for registered LLMS_Post_Model properties.
	 *
	 * @since 7.1.0
	 *
	 * @param LLMS_Post_Model $post An LLMS post object.
	 * @param array           $raw  Array of raw data.
	 * @return void
	 */
	protected function set_metadata( $post, $raw ) {

		$generated_from_id = $post->get( 'generated_from_id' );

		if ( $generated_from_id ) {
			$replace_id_props = array(
				'course_closed_message',
				'course_opens_message',
				'enrollment_closed_message',
				'enrollment_opens_message',
			);

			$find    = '#(.*id=["\'])' . $generated_from_id . '(["\'].*)#';
			$replace = '${1}' . $post->get( 'id' ) . '${2}';

			/**
			 * Replace old post ID with new cloned post ID in course/enrollment
			 * message shortcodes.
			 */
			foreach ( $replace_id_props as $key ) {
				if ( isset( $raw[ $key ] ) ) {
					$raw[ $key ] = preg_replace( $find, $replace, $raw[ $key ] );
				}
			}
		}

		return parent::set_metadata( $post, $raw );
	}
}
