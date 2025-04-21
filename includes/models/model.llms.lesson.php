<?php
/**
 * LifterLMS Lesson Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 1.0.0
 * @version 6.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Lesson model class
 *
 * @since 1.0.0
 * @since 3.29.0 Unknown.
 * @since 3.36.2 When getting the lesson's available date: add available number of days to the course start date only if there's a course start date.
 * @since 4.0.0 Remove deprecated methods.
 * @since 4.4.0 Improve the query used to retrieve the previous/next so that we don't miss sibling lessons within the same section
 *              if the previous/next one(s) status is (are) not published. Make sure to always return `false` if no previous lesson is found.
 *              Use strict comparisons where needed.
 * @since 5.3.0 Move audio and video embed methods to `LLMS_Trait_Audio_Video_Embed`.
 * @since 5.7.0 Deprecated the `LLMS_Lesson::get_order()` method in favor of the `LLMS_Lesson::get( 'order' )` method.
 *              Deprecated the `LLMS_Lesson::get_parent_course()` method in favor of the `LLMS_Lesson::get( 'parent_course' )` method.
 *              Deprecated the `LLMS_Lesson::set_parent_course()` method in favor of the `LLMS_Lesson::set( 'parent_course', $course_id )` method.
 *
 * @property string $audio_embed                      URL to an oEmbed enable audio URL.
 * @property string $date_available                   Date when lesson becomes available, applies when $drip_method is "date".
 * @property int    $days_before_available            The number of days before the lesson is available, applies when $drip_method is "enrollment" or "start".
 * @property string $drip_method                      What sort of drip method to utilize [''(none)|date|enrollment|start|prerequisite].
 * @property string $free_lesson                      Yes if the lesson is free [yes|no].
 * @property string $has_prerequisite                 Yes if the lesson has a prereq lesson [yes|no].
 * @property int    $order                            Lesson's order within its parent section.
 * @property int    $points                           Number of points assigned to the lesson, used to calculate the weight of the lesson when grading courses.
 * @property int    $prerequisite                     WP Post ID of the prerequisite lesson, only if $has_prerequisite is 'yes'.
 * @property int    $parent_course                    WP Post ID of the course the lesson belongs to.
 * @property int    $parent_section                   WP Post ID of the section the lesson belongs to.
 * @property int    $quiz                             WP Post ID of the llms_quiz.
 * @property string $quiz_enabled                     Whether or not the attached quiz is enabled for students [yes|no].
 * @property string $require_passing_grade            Whether of not students have to pass the quiz to advance to the next lesson [yes|no].
 * @property string $require_assignment_passing_grade Whether of not students have to pass the assignment to advance to the next lesson [yes|no].
 * @property string $time_available                   Optional time to make lesson available on $date_available when $drip_method is "date".
 * @property string $video_embed                      URL to an oEmbed enable video URL.
 */
class LLMS_Lesson extends LLMS_Post_Model {

	use LLMS_Trait_Audio_Video_Embed;

	protected $properties = array(

		'order'                            => 'absint',

		// Drippable.
		'days_before_available'            => 'absint',
		'date_available'                   => 'text',
		'drip_method'                      => 'text',
		'time_available'                   => 'text',

		// Parent element.
		'parent_course'                    => 'absint',
		'parent_section'                   => 'absint',

		'free_lesson'                      => 'yesno',
		'has_prerequisite'                 => 'yesno',
		'prerequisite'                     => 'absint',
		'require_passing_grade'            => 'yesno',
		'require_assignment_passing_grade' => 'yesno',
		'points'                           => 'absint',

		// Quizzes.
		'quiz'                             => 'absint',
		'quiz_enabled'                     => 'yesno',

	);

	/**
	 * Associative array of default property values
	 *
	 * @since 3.24.0
	 * @var array
	 */
	protected $property_defaults = array(
		'points' => 1,
	);

	/**
	 * Name of the post type as stored in the database
	 *
	 * @since unknown
	 * @var string
	 */
	protected $db_post_type = 'lesson';

	/**
	 * Post type name
	 *
	 * To use unprefixed post type names for filters and more.
	 *
	 * @since unknown
	 * @var string
	 */
	protected $model_post_type = 'lesson';

	/**
	 * Constructor for this class and the traits it uses.
	 *
	 * @since 5.3.0
	 *
	 * @param string|int|LLMS_Post_Model|WP_Post $model 'new', WP post id, instance of an extending class, instance of WP_Post.
	 * @param array                              $args  Args to create the post, only applies when $model is 'new'.
	 */
	public function __construct( $model, $args = array() ) {

		$this->construct_audio_video_embed();
		parent::__construct( $model, $args );
	}

	/**
	 * Get the date a lesson became or will become available according to element drip settings
	 *
	 * If there are no drip settings, the published date of the element will be returned.
	 *
	 * @since 3.16.0
	 * @since 3.36.2 Add available number of days to the course start date only if there's a course start date.
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 *
	 * @param string $format Optional. Date format (passed to date_i18n()). Default is empty string.
	 *                       When not specified the WP Core date + time formats will be used.
	 * @return string
	 */
	public function get_available_date( $format = '' ) {

		if ( ! $format ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		$drip_method = $this->get( 'drip_method' );

		$days = $this->get( 'days_before_available' ) * DAY_IN_SECONDS;

		// Default availability is the element's post date.
		$available = $this->get_date( 'date', 'U' );

		// get the course setting first, if any.
		$course = $this->get_course();
		if ( $course && 'yes' === $course->get( 'lesson_drip' ) ) {
			$course_drip_method = $course->get( 'drip_method' );

			switch ( $course_drip_method ) {
				case 'start':
					$ignore_lessons = intval( $course->get( 'ignore_lessons' ) );
					$course_lessons = $course->get_lessons( 'ids' );
					$lesson_number  = array_search( $this->get( 'id' ), $course_lessons ) + 1;

					$course_days            = $course->get( 'days_before_available' ) * DAY_IN_SECONDS;
					$course_start_date      = $course->get_date( 'start_date', 'U' );
					$course_enrollment_date = llms_get_student() ? llms_get_student()->get_enrollment_date( $course->get( 'id' ), 'enrolled', 'U' ) : false;

					// If it's one of the first X lessons in a course, return availability based on published date.
					if ( $lesson_number <= $ignore_lessons ) {
						return date_i18n( $format, $available );
					}

					if ( $course_start_date || $course_enrollment_date ) {
						$available = ( ( $lesson_number - $ignore_lessons ) * $course_days ) + ( $course_start_date ? $course_start_date : $course_enrollment_date );

						return date_i18n( $format, $available );
					}
					break;
			}
		}

		switch ( $drip_method ) {

			// Available on a specific date / time.
			case 'date':
				$date = $this->get( 'date_available' );
				$time = $this->get( 'time_available' );

				if ( ! $time ) {
					$time = '12:00 AM';
				}

				$available = strtotime( $date . ' ' . $time );

				break;

			// Available # of days after enrollment in course.
			case 'enrollment':
				$student = llms_get_student();
				if ( $student ) {
					$available = $days + $student->get_enrollment_date( $this->get( 'parent_course' ), 'enrolled', 'U' );
				}
				break;

			case 'prerequisite':
				if ( $this->has_prerequisite() ) {
					$student = llms_get_student();
					if ( $student ) {
						$date = $student->get_completion_date( $this->get( 'prerequisite' ), 'U' );
						if ( $date ) {
							$available = $days + $date;
						}
					}
				}

				break;

			// Available # of days after course start date.
			case 'start':
				$course            = $this->get_course();
				$course_start_date = $course ? $course->get_date( 'start_date', 'U' ) : '';

				if ( $course_start_date ) {
					$available = $days + $course_start_date;
				}

				break;

		}

		return date_i18n( $format, $available );
	}

	/**
	 * Retrieve an instance of LLMS_Course for the element's parent course
	 *
	 * @since 3.16.0
	 *
	 * @return LLMS_Course|null Returns `null` if the lesson is not attached to any courses.
	 */
	public function get_course() {

		$course_id = $this->get( 'parent_course' );
		if ( ! $course_id ) {
			return null;
		}

		return llms_get_post( $course_id );
	}

	/**
	 * An array of default arguments to pass to $this->create() when creating a new post.
	 *
	 * @since 3.13.0
	 * @since 6.3.0 Retrieve `comment_status` parameter value from the global discussion settings.
	 *
	 * @param array $args Optional. Args of data to be passed to `wp_insert_post()`. Default `null`.
	 * @return array
	 */
	protected function get_creation_args( $args = null ) {

		// Allow nothing to be passed in.
		if ( empty( $args ) ) {
			$args = array();
		}

		// Backwards compat to original 3.0.0 format when just a title was passed in.
		if ( is_string( $args ) ) {
			$args = array(
				'post_title' => $args,
			);
		}

		$post_type = $this->get( 'db_post_type' );
		$args      = wp_parse_args(
			$args,
			array(
				'comment_status' => get_default_comment_status( $post_type ),
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
				'post_content'   => '',
				'post_excerpt'   => '',
				'post_status'    => 'publish',
				'post_title'     => '',
				'post_type'      => $post_type,
			)
		);

		/**
		 * Filter the model creation args
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to model post type.
		 *
		 * @since unknown
		 *
		 * @param array       $args   Args of data to be passed to `wp_insert_post()`.
		 * @param LLMS_Lesson $lesson Instance of the LLMS_Lesson.
		 */
		return apply_filters( "llms_{$this->model_post_type}_get_creation_args", $args, $this );
	}

	/**
	 * Retrieves the lesson's order within its parent section
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 * @deprecated 5.7.0 Use `LLMS_Lesson::get( 'order' )`, via {@see LLMS_Post_Model::get()}, instead.
	 *
	 * @return int
	 */
	public function get_order() {

		llms_deprecated_function( __METHOD__, '5.7.0', __CLASS__ . '::get( \'order\' )' );

		return $this->get( 'order' );
	}

	/**
	 * Get parent course id
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 * @deprecated 5.7.0 Use `LLMS_Lesson::get( 'parent_course' )`, via {@see LLMS_Post_Model::get()}, instead.
	 *
	 * @return int
	 */
	public function get_parent_course() {

		llms_deprecated_function( __METHOD__, '5.7.0', __CLASS__ . '::get( \'parent_course\' )' );

		return absint( get_post_meta( $this->get( 'id' ), '_llms_parent_course', true ) );
	}

	/**
	 * Get parent section id
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 *
	 * @return int
	 */
	public function get_parent_section() {
		return absint( get_post_meta( $this->get( 'id' ), '_llms_parent_section', true ) );
	}

	/**
	 * Get CSS classes to display on the course syllabus .llms-lesson-preview element
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_preview_classes() {

		$classes = '';

		if ( $this->is_complete() ) {
			$classes = ' is-complete has-icon';
		} elseif ( apply_filters( 'lifterlms_display_lesson_complete_placeholders', true ) && llms_is_user_enrolled( get_current_user_id(), $this->get( 'id' ) ) ) {
			$classes = ' is-incomplete has-icon';
		} elseif ( $this->is_free() ) {
			$classes = ' is-free has-icon';
		} else {
			$classes = ' is-incomplete';
		}

		if ( get_queried_object_id() === intval( $this->get( 'id' ) ) ) {
			$classes .= ' current-lesson';
		}

		return apply_filters( 'llms_get_preview_classes', $classes );
	}

	/**
	 * Get HTML of the icon to display in the .llms-lesson-preview element on the syllabus
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_preview_icon_html() {

		$html = '';

		if ( llms_is_user_enrolled( get_current_user_id(), $this->get( 'id' ) ) ) {

			if ( $this->is_complete() || apply_filters( 'lifterlms_display_lesson_complete_placeholders', true ) ) {

				$html = '<span class="llms-lesson-complete"><i class="fa fa-' . apply_filters( 'lifterlms_lesson_complete_icon', 'check-circle' ) . '"></i></span>';

			}
		} elseif ( $this->is_free() ) {

			$html = '<span class="llms-icon-free">' . __( 'FREE', 'lifterlms' ) . '</span>';

		}

		return apply_filters( 'llms_get_preview_icon_html', $html );
	}

	/**
	 * Retrieve an instance of LLMS_Course for the elements's parent section
	 *
	 * @since 3.16.0
	 *
	 * @return LLMS_Section|null Returns `null` it the lesson is not attached to any sections.
	 */
	public function get_section() {

		$section_id = $this->get( 'parent_section' );
		if ( ! $section_id ) {
			return null;
		}

		return llms_get_post( $section_id );
	}

	/**
	 * Retrieve an object for the assigned quiz (if a quiz is assigned)
	 *
	 * @since 3.3.0
	 * @since 3.16.0 Unknown.
	 *
	 * @return LLMS_Quiz|false Returns `false` if the lesson has no existing quiz assigned.
	 */
	public function get_quiz() {
		if ( $this->has_quiz() ) {
			$quiz = llms_get_post( $this->get( 'quiz' ) );
			if ( $quiz ) {
				return $quiz;
			}
		}
		return false;
	}

	/**
	 * Determine if lesson prereq is enabled and a prereq lesson is selected
	 *
	 * @since 3.0.0
	 * @since 4.4.0 Use strict comparison.
	 *
	 * @return boolean
	 */
	public function has_prerequisite() {

		return ( 'yes' === $this->get( 'has_prerequisite' ) && $this->get( 'prerequisite' ) );
	}

	/**
	 * Determine if the slug (post name) of a lesson has been modified
	 *
	 * Ensures that lessons created via the builder with "New Lesson" as the title (default slug "new-lesson-{$num}")
	 * have their slug renamed when the title is renamed for the first time.
	 *
	 * @since 3.14.8
	 *
	 * @return bool
	 */
	public function has_modified_slug() {

		$default = sanitize_title( __( 'New Lesson', 'lifterlms' ) );
		return ( false === strpos( $this->get( 'name' ), $default ) );
	}

	/**
	 * Determine if a quiz is assigned to this lesson
	 *
	 * @since 3.3.0
	 * @since 3.29.0 Unknown.
	 *
	 * @return boolean
	 */
	public function has_quiz() {
		return $this->get( 'quiz' ) ? true : false;
	}

	/**
	 * Determine if an element is available based on drip settings
	 *
	 * If no settings, this will return true if the posts's published
	 * date is in the past.
	 *
	 * @since 3.16.0
	 *
	 * @return boolean
	 */
	public function is_available() {

		$drip_method        = $this->get( 'drip_method' );
		$course_drip_method = $this->get_course() ? 'yes' === $this->get_course()->get( 'lesson_drip' ) && $this->get_course()->get( 'drip_method' ) : '';

		// Drip is not enabled, so the element is available.
		if ( ! $drip_method && ! $course_drip_method ) {
			return true;
		}

		$available = $this->get_available_date( 'U' );
		$now       = llms_current_time( 'timestamp' );

		return ( $now >= $available );
	}

	/**
	 * Determine if the lesson has been completed by a specific user
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Refactored to utilize LLMS_Student->is_complete().
	 *              Added $user_id param.
	 *
	 * @param int $user_id Optional. WP_User ID of a student. Default `null`.
	 *                     If not provided, or a falsy is provided, will fall back on the current user id.
	 * @return bool
	 */
	public function is_complete( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();

		// Incomplete b/c no user.
		if ( ! $user_id ) {
			return false;
		}

		$student = new LLMS_Student( $user_id );

		return $student->is_complete( $this->get( 'id' ), 'lesson' );
	}


	/**
	 * Determine if a the lesson is marked as "free"
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_free() {
		return ( 'yes' === $this->get( 'free_lesson' ) );
	}

	/**
	 * Determine if the lesson is an orphan
	 *
	 * @since 3.14.8
	 * @since 4.4.0 Use `in_array()` with strict comparison to decide whether the parent course/section post status
	 *                  is in a set of allowed statuses.
	 * @return bool
	 */
	public function is_orphan() {

		$statuses = array( 'publish', 'future', 'draft', 'pending', 'private', 'auto-draft' );

		foreach ( array( 'course', 'section' ) as $parent ) {

			$parent_id = $this->get( sprintf( 'parent_%s', $parent ) );

			if ( ! $parent_id ) {
				return true;
			} elseif ( ! in_array( get_post_status( $parent_id ), $statuses, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if a quiz is enabled for the lesson
	 *
	 * Lesson must have a quiz and the quiz must be enabled.
	 *
	 * @since 3.16.0
	 * @since 3.18.0
	 *
	 * @return bool
	 */
	public function is_quiz_enabled() {
		return ( $this->has_quiz() && llms_parse_bool( $this->get( 'quiz_enabled' ) ) && 'publish' === get_post_status( $this->get( 'quiz' ) ) );
	}

	/**
	 * Add data to the course model when converted to array
	 *
	 * Called before data is sorted and returned by $this->jsonSerialize().
	 *
	 * @since 3.3.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param array $arr Data to be serialized.
	 * @return array
	 */
	public function toArrayAfter( $arr ) {

		if ( $this->has_quiz() ) {

			$quiz = $this->get_quiz();
			if ( $quiz ) {
				$arr['quiz'] = $quiz->toArray();
			}
		}

		return $arr;
	}

	/**
	 * Update object data
	 *
	 * @since unknown.
	 *
	 * @param array $data Data to update as key=>val.
	 * @return array
	 */
	public function update( $data ) {

		$updated_values = array();

		foreach ( $data as $key => $value ) {
			$method = 'set_' . $key;

			if ( method_exists( $this, $method ) ) {
				$updated_value = $this->$method( $value );

				$updated_values[ $key ] = $updated_value;

			}
		}

		return $updated_values;
	}

	/**
	 * Set lesson title
	 *
	 * @since unknown
	 *
	 * @param string $title The lesson title.
	 * @return false|array False if the title couldn't be updated. An array of the type
	 *                     array(
	 *                         'id'    => lesson id,
	 *                         'title' => the new title,
	 *                     )
	 *                     otherwise.
	 */
	public function set_title( $title ) {

		return LLMS_Post_Handler::update_title( $this->id, $title );
	}

	/**
	 * Set lesson's excerpt
	 *
	 * @since unknown
	 *
	 * @param string $excerpt The lesson excerpt.
	 * @return false|array False if the title couldn't be updated. An array of the type
	 *                     array(
	 *                         'id'           => lesson id,
	 *                         'post_excerpt' => the new excerpt,
	 *                     )
	 *                     otherwise.
	 */
	public function set_excerpt( $excerpt ) {

		return LLMS_Post_Handler::update_excerpt( $this->id, $excerpt );
	}

	/**
	 * Set parent section
	 *
	 * Sets parent section in database.
	 *
	 * @since unknown
	 *
	 * @param int $section_id The WP Post ID of the section to be set as parent.
	 * @return mixed $meta If meta didn't exist returns the meta_id else t/f if update success.
	 *                     Returns `false` if the provided section id value was already set.
	 */
	public function set_parent_section( $section_id ) {

		return update_post_meta( $this->id, '_llms_parent_section', $section_id );
	}

	/**
	 * Set order
	 *
	 * Sets lesson order within the parent sectionin database
	 *
	 * @since unknown
	 *
	 * @param int $order The new order
	 * @return mixed $meta If meta didn't exist returns the meta_id else t/f if update success.
	 *                     Returns `false` if the provided order value was already set.
	 */
	public function set_order( $order ) {

		return update_post_meta( $this->id, '_llms_order', $order );
	}

	/**
	 * Set parent course
	 *
	 * Sets parent course in database
	 *
	 * @since Unknown Introduced.
	 * @deprecated 5.7.0 Use `LLMS_Lesson::set( 'parent_course', $course_id )`, via {@see LLMS_Post_Model::set()}, instead.
	 *
	 * @param int $course_id The WP Post ID of the course to be set as parent.
	 * @return int|bool If meta didn't exist returns the meta_id else t/f if update success.
	 *                  Returns `false` if the course id value was already set.
	 */
	public function set_parent_course( $course_id ) {

		llms_deprecated_function( __METHOD__, '5.7.0', __CLASS__ . '::set( \'parent_course\', $course_id )' );

		return update_post_meta( $this->id, '_llms_parent_course', $course_id );
	}

	/**
	 * Get the lesson prerequisite
	 *
	 * @since unknown
	 *
	 * @return int ID of the prerequisite post.
	 */
	public function get_prerequisite() {

		if ( $this->has_prerequisite ) {

			return $this->prerequisite;
		} else {
			return false;
		}
	}

	/**
	 * Get whether the lesson has a content set
	 *
	 * @since unknown
	 *
	 * @return boolean
	 */
	public function has_content() {
		if ( ! empty( $this->post->post_content ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get next lesson ID
	 *
	 * @since 1.0.0
	 * @since 3.24.0
	 * @since 4.4.0 Improve query so that unpublished siblings do not break expected results.
	 * @since 4.4.2 Use a numeric comparison for the previous position meta query.
	 * @since 4.10.2 Refactor to use helper method `get_sibling()`.
	 *
	 * @return false|int ID of the next lesson, if any, `false` otherwise.
	 */
	public function get_next_lesson() {

		return $this->get_sibling( 'next' );
	}

	/**
	 * Get previous lesson ID
	 *
	 * @since 1.0.0
	 * @since 3.24.0 Unknown.
	 * @since 4.4.0 Improve query so that unpublished siblings do not break expected results.
	 *              Use strict comparisons where needed.
	 *              Make sure to always return `false` if no previous lesson is found.
	 * @since 4.4.2 Use a numeric comparison for the previous position meta query.
	 * @since 4.10.2 Refactor to use helper method `get_sibling()`.
	 *
	 * @return false|int WP_Post ID of the previous lesson or `false` if one doesn't exist.
	 */
	public function get_previous_lesson() {

		return $this->get_sibling( 'prev' );
	}

	/**
	 * Retrieve the sibling lesson in a specified direction
	 *
	 * @since 4.10.2
	 *
	 * @param string $direction Direction of navigation. Accepts either "prev" or "next".
	 * @return false|int WP_Post ID of the sibling lesson or `false` if one doesn't exist.
	 */
	protected function get_sibling( $direction ) {

		$lesson = $this->get_sibling_lesson_query( $direction );

		// No lesson found within the section, look within the sibling section.
		if ( ! $lesson ) {
			$lesson = $this->get_sibling_section_query( $direction );
		}

		return $lesson;
	}

	/**
	 * Performs a query to retrieve a sibling lesson in the specified direction
	 *
	 * This method tries to locate a sibling lesson in the next or previous position.
	 *
	 * It *does not* account for lessons in a sibling section. For example, if the lesson
	 * is the last lesson in a section this function will *not* locate the first lesson
	 * in the course's next section. For this reason this function should not be relied upon
	 * alone.
	 *
	 * @since 4.10.2
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_order()` method with `LLMS_Lesson::get( 'order' )`.
	 *
	 * @param string $direction Direction of navigation. Accepts either "prev" or "next".
	 * @return false|int WP_Post ID of the sibling lesson or `false` if one doesn't exist.
	 */
	protected function get_sibling_lesson_query( $direction ) {

		$curr_position = $this->get( 'order' );

		// First cannot have a previous.
		if ( 1 === $curr_position && 'prev' === $direction ) {
			return false;
		}

		if ( 'next' === $direction ) {
			$sibling_position = $curr_position + 1;
			$order            = 'ASC';
			$comparator       = '>=';
		} elseif ( 'prev' === $direction ) {
			$sibling_position = $curr_position - 1;
			$order            = 'DESC';
			$comparator       = '<=';
		}

		$args = array(
			'posts_per_page' => 1,
			'post_type'      => 'lesson',
			'nopaging'       => true,
			'post_status'    => 'publish',
			'meta_key'       => '_llms_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => 'meta_value_num',
			'order'          => $order,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => '_llms_parent_section',
					'value'   => $this->get_parent_section(),
					'compare' => '=',
				),
				array(
					'key'     => '_llms_order',
					'value'   => $sibling_position,
					'compare' => $comparator,
					'type'    => 'numeric',
				),
			),
		);

		/**
		 * Filter the WP_Query arguments used to locate a sibling lesson for the specified lesson.
		 *
		 * @since 4.10.2
		 *
		 * @param array       $args      WP_Query arguments array.
		 * @param string      $direction Navigation direction. Either "prev" or "next".
		 * @param LLMS_Lesson $lesson    Current lesson object.
		 */
		$args = apply_filters( 'llms_lesson_get_sibling_lesson_query_args', $args, $direction );

		$lessons = get_posts( $args );

		return empty( $lessons ) ? false : $lessons[0]->ID;
	}

	/**
	 * Performs a query to retrieve sibling lessons from the lesson's adjacent section
	 *
	 * This will retrieve either the first lesson from the course's next section or the last
	 * lesson from the course's previous section.
	 *
	 * @since 4.10.2
	 * @since 4.11.0 Fix PHP Notice when trying to retrieve next lesson from an empty section.
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Section::get_order()` method with `LLMS_Section::get( 'order' )`.
	 *
	 * @param string $direction Direction of navigation. Accepts either "prev" or "next".
	 * @return false|int WP_Post ID of the sibling lesson or `false` if one doesn't exist.
	 */
	protected function get_sibling_section_query( $direction ) {

		$sibling_lesson = false;
		$curr_section   = $this->get_section();

		// Ensure we're not working with an orphan.
		if ( $curr_section ) {

			$curr_position = $curr_section->get( 'order' );

			// First cannot have a previous.
			if ( 1 === $curr_position && 'prev' === $direction ) {
				return false;
			}

			if ( 'next' === $direction ) {
				$sibling_position = $curr_position + 1;
				$order            = 'ASC';
			} elseif ( 'prev' === $direction ) {
				$sibling_position = $curr_position - 1;
				$order            = 'DESC';
			}

			$args = array(
				'post_type'      => 'section',
				'posts_per_page' => 1,
				'nopaging'       => true,
				'meta_key'       => '_llms_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value_num',
				'order'          => $order,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => '_llms_parent_course',
						'value'   => $this->get( 'parent_course' ),
						'compare' => '=',
					),
					array(
						'key'     => '_llms_order',
						'value'   => $sibling_position,
						'compare' => '=',
					),
				),
			);

			/**
			 * Filter the WP_Query arguments used to locate a sibling lesson from a sibling section for the specified lesson.
			 *
			 * @since 4.10.2
			 *
			 * @param array       $args      WP_Query arguments array.
			 * @param string      $direction Navigation direction. Either "prev" or "next".
			 * @param LLMS_Lesson $lesson    Current lesson object.
			 */
			$args = apply_filters( 'llms_lesson_get_sibling_section_query_args', $args, $direction, $this );

			$sections = get_posts( $args );

			if ( ! empty( $sections ) ) {
				$sibling_section = llms_get_post( $sections[0]->ID );
				$lessons         = $sibling_section ? $sibling_section->get_lessons( 'posts' ) : array( false );
				$sibling_lesson  = 'next' === $direction ? reset( $lessons ) : end( $lessons );
			}
		}

		return $sibling_lesson instanceof WP_Post ? $sibling_lesson->ID : $sibling_lesson;
	}
}
