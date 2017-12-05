<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shared functionality for Course Elements (posts)
 *
 * Lessons & Quizzes
 *
 * @since    [version]
 * @version  [version]
 */
abstract class LLMS_Abstract_Course_Element_Post extends LLMS_Post_Model {

	private $abstract_properties = array(

		'order' => 'absint',

		// drippable
		'days_before_available' => 'absint',
		'date_available' => 'text',
		'drip_method' => 'text',
		'time_available' => 'text',

		// parent element
		'parent_course' => 'absint',
		'parent_section' => 'absint',

	);

	/**
	 * Constructor
	 * Setup ID and related post property
	 *
	 * @param     int|obj    $model   WP post id, instance of an extending class, instance of WP_Post
	 * @param     array     $args    args to create the post, only applies when $model is 'new'
	 * @return    void
	 * @since     [version]
	 * @version   [version]
	 */
	public function __construct( $model, $args = array() ) {

		parent::__construct( $model, $args );
		$this->add_properties( $this->abstract_properties );

	}

	/**
	 * Get the date a course became or will become available according to element drip settings
	 * If there are no drip settings, the published date of the element will be returned
	 *
	 * @param    string     $format  date format (passed to date_i18n()) (defaults to WP Core date + time formats)
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_available_date( $format = '' ) {

		if ( ! $format ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		$drip_method = $this->get( 'drip_method' );

		$days = $this->get( 'days_before_available' ) * DAY_IN_SECONDS;

		// default availability is the element's post date
		$available = $this->get_date( 'date', 'U' );

		switch ( $drip_method ) {

			case 'complete':



			break;

			// available on a specific date / time
			case 'date':

				$date = $this->get( 'date_available' );
				$time = $this->get( 'time_available' );

				if ( ! $time ) {
					$time = '12:00 AM';
				}

				$available = strtotime( $date . ' ' . $time );

			break;

			// available # of days after enrollment in course
			case 'enrollment':
				$student = llms_get_student();
				if ( $student ) {
					$available = $days + $student->get_enrollment_date( $this->get_parent_course(), 'enrolled', 'U' );
				}
			break;

			// available # of days after course start date
			case 'start':
				$course = $this->get_course();
				$available = $days + $course->get_date( 'start_date', 'U' );
			break;

		}

		return date_i18n( $format, $available );

	}

	/**
	 * Retrieve an instance of LLMS_Course for the element's parent course
	 * @return   obj|null
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_course() {

		$course_id = $this->get( 'parent_course' );
		if ( ! $course_id ) {
			return null;
		}

		return llms_get_post( $course_id );

	}

	/**
	 * Retrieve an instance of LLMS_Course for the elements's parent section
	 * @return   obj|null
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_section() {

		$section_id = $this->get( 'parent_section' );
		if ( ! $section_id ) {
			return null;
		}

		return llms_get_post( $section_id );

	}

	public function get_previous_element() {

		if ( $this->is_first( 'course' ) ) {
			return false;
		}

		$section = $this->get_section();
		if ( $this->is_first( 'section' ) ) {
			$section = $section->get_previous();
		}

		$elements = $section->get_lessons();


	}

	/**
	 * Determine if an element is available based on drip settings
	 * If no settings, this will return true if the posts's published
	 * date is in the past
	 *
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function is_available() {

		$drip_method = $this->get( 'drip_method' );

		// drip is no enabled, so the element is available
		if ( ! $drip_method ) {
			return true;
		}

		$available = $this->get_available_date( 'U' );
		$now = llms_current_time( 'timestamp' );

		return ( $now > $available );

	}

	/**
	 * Determine if the element is the first element in a course
	 * @param    string    $relative_to   which parent to check against [course|section]
	 *                                    first in course will be first element in first section
	 *                                    first in section will be the first element in it's section
	 * @return   bool
	 * @since    [version]
	 * @version  [version]
	 */
	public function is_first( $relative_to ) {

		$section = $this->get_section();
		if ( ! $section ) {
			return false;
		}

		// in first section & is the first in the section
		if ( 'course' === $relative_to ) {
			return ( 1 === $section->get( 'order' ) && $this->is_first( 'section' ) );
		}

		// first in the section
		return ( 1 === $this->get( 'order' ) );

	}

	public function is_last( $relative_to ) {

		$section = $this->get_section();
		if ( ! $section ) {
			return false;
		}

		// in first section & is the first in the section
		if ( 'course' === $relative_to ) {
			return ( 1 === $section->get( 'order' ) && $this->is_first( 'section' ) );
		}

		// first in the section
		return ( 1 === $this->get( 'order' ) );


	}

}
