<?php
/**
 * LifterLMS Due Date trait.
 *
 * Shared due date logic for gradable course content, such as quizzes (core) and assignments (add-on).
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Due Date trait.
 *
 * **Classes that use this trait must call {@see LLMS_Trait_Due_Date::construct_due_date()}
 * in their constructor.**
 *
 * Timestamps follow the same "site local" convention as lesson drip: they are created with `strtotime()`
 * on dates in the site's timezone and compared to `llms_current_time( 'timestamp' )`.
 *
 * @since [version]
 *
 * @property string $due_method Due date method [''(none)|date|enrollment|start].
 * @property string $due_date   Due date (Y-m-d), applies when $due_method is "date".
 * @property string $due_time   Optional time of day (e.g. 11:59 PM) the item is due. Defaults to the end of the day.
 * @property int    $due_days   Number of days after course enrollment or course start, applies when $due_method is "enrollment" or "start".
 */
trait LLMS_Trait_Due_Date {

	/**
	 * Add properties to the model.
	 *
	 * @since [version]
	 *
	 * @param array $props Array of properties.
	 * @return void
	 */
	abstract protected function add_properties( $props = array() );

	/**
	 * Get a property's value.
	 *
	 * @since [version]
	 *
	 * @param string $key The property key.
	 * @param bool   $raw Whether or not to retrieve the raw value.
	 * @return mixed
	 */
	abstract public function get( $key, $raw = false );

	/**
	 * Setup properties used by this trait.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function construct_due_date() {
		$this->add_properties(
			array(
				'due_method' => 'text',
				'due_date'   => 'text',
				'due_time'   => 'text',
				'due_days'   => 'absint',
			)
		);
	}

	/**
	 * Retrieve the course the item belongs to.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Course|false|null
	 */
	protected function get_due_date_course() {
		return method_exists( $this, 'get_course' ) ? $this->get_course() : false;
	}

	/**
	 * Determine if the item has a due date (before any student override is applied).
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function has_due_date() {
		return in_array( $this->get( 'due_method' ), array( 'date', 'enrollment', 'start' ), true );
	}

	/**
	 * Retrieve the default (course-wide) due date timestamp for a student.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID. Required for enrollment-based due dates.
	 * @return int|false Site local timestamp or `false` when there is no due date.
	 */
	public function get_default_due_timestamp( $student_id = null ) {

		$time = $this->get( 'due_time' );
		$time = $time ? $time : '11:59:59 PM';
		$days = $this->get( 'due_days' );
		$day  = false;

		switch ( $this->get( 'due_method' ) ) {

			case 'date':
				$day = $this->get( 'due_date' );
				break;

			case 'enrollment':
				$course  = $this->get_due_date_course();
				$student = $student_id ? llms_get_student( $student_id ) : false;
				$start   = ( $course && $student ) ? $student->get_enrollment_date( $course->get( 'id' ), 'enrolled', 'U' ) : false;
				$day     = $start ? gmdate( 'Y-m-d', $start + ( $days * DAY_IN_SECONDS ) ) : false;
				break;

			case 'start':
				$course = $this->get_due_date_course();
				$start  = $course ? $course->get_date( 'start_date', 'U' ) : false;
				$day    = $start ? gmdate( 'Y-m-d', $start + ( $days * DAY_IN_SECONDS ) ) : false;
				break;

		}

		$timestamp = $day ? strtotime( $day . ' ' . $time ) : false;

		/**
		 * Filters the default due date timestamp of an item, before student overrides are applied.
		 *
		 * @since [version]
		 *
		 * @param int|false       $timestamp  Site local timestamp or `false` for no due date.
		 * @param LLMS_Post_Model $item       Quiz, assignment, etc.
		 * @param int|null        $student_id WP_User ID.
		 */
		return apply_filters( 'llms_default_due_timestamp', $timestamp, $this, $student_id );
	}

	/**
	 * Retrieve a student's due date override.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID.
	 * @return array|false {
	 *     Override data or `false` when there is no override.
	 *
	 *     @type string $due    Local date (Y-m-d H:i:s) or `none` to remove the due date for this student.
	 *     @type string $reason Reason for the override, visible only to instructors.
	 *     @type int    $by     WP_User ID of the user who created the override.
	 *     @type string $date   Local date (Y-m-d H:i:s) when the override was created.
	 * }
	 */
	public function get_due_date_override( $student_id ) {
		$override = $student_id ? llms_get_user_postmeta( $student_id, $this->get( 'id' ), '_due_date_override' ) : false;
		return is_array( $override ) && ! empty( $override['due'] ) ? $override : false;
	}

	/**
	 * Set (or clear) a student's due date override.
	 *
	 * @since [version]
	 *
	 * @param int    $student_id WP_User ID.
	 * @param string $due        Local date (Y-m-d H:i:s), `none` to remove the due date for the student, or empty to delete the override.
	 * @param string $reason     Optional reason, visible only to instructors.
	 * @return bool
	 */
	public function set_due_date_override( $student_id, $due, $reason = '' ) {

		if ( ! $due ) {
			return (bool) llms_delete_user_postmeta( $student_id, $this->get( 'id' ), '_due_date_override' );
		}

		$override = array(
			'due'    => 'none' === $due ? 'none' : gmdate( 'Y-m-d H:i:s', strtotime( $due ) ),
			'reason' => sanitize_text_field( $reason ),
			'by'     => get_current_user_id(),
			'date'   => llms_current_time( 'mysql' ),
		);

		$res = llms_update_user_postmeta( $student_id, $this->get( 'id' ), '_due_date_override', $override );

		/**
		 * Action run after a student's due date override is saved.
		 *
		 * @since [version]
		 *
		 * @param LLMS_Post_Model $item       Quiz, assignment, etc.
		 * @param int             $student_id WP_User ID.
		 * @param array           $override   Override data.
		 */
		do_action( 'llms_due_date_override_saved', $this, $student_id, $override );

		return (bool) $res;
	}

	/**
	 * Retrieve the due date timestamp that applies to a student.
	 *
	 * In order of precedence: the student's due date for this item, the due date accommodation on
	 * the student's profile, then the course due date.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID.
	 * @return int|false Site local timestamp or `false` when there is no due date for the student.
	 */
	public function get_due_timestamp( $student_id = null ) {

		$override = $this->get_due_date_override( $student_id );
		if ( $override ) {
			return 'none' === $override['due'] ? false : strtotime( $override['due'] );
		}

		$default       = $this->get_default_due_timestamp( $student_id );
		$accommodation = $default ? $this->get_due_date_accommodation( $student_id ) : false;
		if ( $accommodation ) {
			return 'none' === $accommodation['type'] ? false : $default + ( $accommodation['days'] * DAY_IN_SECONDS );
		}

		return $default;
	}

	/**
	 * Retrieve the due date accommodation from the student's profile, if any.
	 *
	 * A per-student due date on the item takes precedence over the accommodation.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID.
	 * @return array|false See {@see LLMS_Student::get_due_date_accommodation()}.
	 */
	public function get_due_date_accommodation( $student_id ) {
		$student = $student_id ? llms_get_student( $student_id ) : false;
		return $student ? $student->get_due_date_accommodation() : false;
	}

	/**
	 * Retrieve the formatted due date that applies to a student.
	 *
	 * @since [version]
	 *
	 * @param int    $student_id WP_User ID.
	 * @param string $format     Date format. Defaults to the WordPress date and time formats.
	 * @return string Empty string when there is no due date.
	 */
	public function get_due_date_string( $student_id = null, $format = '' ) {
		$timestamp = $this->get_due_timestamp( $student_id );
		if ( ! $timestamp ) {
			return '';
		}
		if ( 'short' === $format ) {
			$format = self::get_short_date_format( $timestamp );
		}
		// Full date and time, with the site's time zone abbreviation (e.g. EST) for students in other zones.
		$format = $format ? $format : get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' T';
		return date_i18n( $format, $timestamp );
	}

	/**
	 * Retrieve a notice for students whose item is overdue but can still be submitted.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID.
	 * @return string Empty string unless the item is overdue for the student.
	 */
	public function get_late_submission_notice( $student_id ) {
		if ( 'overdue' !== $this->get_student_due_status( $student_id ) ) {
			return '';
		}
		return sprintf(
			'<span class="llms-due-notice">%s</span>',
			esc_html__( 'This is past due. You can still submit it, but it will be marked late.', 'lifterlms' )
		);
	}

	/**
	 * Retrieve a compact version of the site's date format, for lists such as the course syllabus.
	 *
	 * Keeps the site's date order and separators, abbreviates month and weekday names, and drops the
	 * year when the date is in the current year. For example, "F j, Y" becomes "M j" (Nov 20) and
	 * "j \d\e F \d\e Y" becomes "j \d\e M" (20 de nov).
	 *
	 * @since [version]
	 *
	 * @param int $timestamp Site local timestamp of the date being formatted.
	 * @return string
	 */
	public static function get_short_date_format( $timestamp ) {

		$format = get_option( 'date_format' );

		// Abbreviate unescaped full month (F) and weekday (l) names.
		$format = preg_replace( array( '/(?<!\\\\)F/', '/(?<!\\\\)l/' ), array( 'M', 'D' ), $format );

		// Drop the year, with the separators and escaped literal text that join it to the rest of the date.
		if ( gmdate( 'Y', $timestamp ) === current_time( 'Y' ) ) {
			$format = preg_replace( '/^\s*[Yyo](?:[\s,.\/\-]|\\\\.)*|(?:[\s,.\/\-]|\\\\.)*[Yyo]\s*$/', '', $format );
		}

		/**
		 * Filters the compact date format used for due dates in lists, such as the course syllabus.
		 *
		 * @since [version]
		 *
		 * @param string $format    Date format.
		 * @param int    $timestamp Site local timestamp of the date being formatted.
		 */
		return apply_filters( 'llms_short_date_format', trim( $format ), $timestamp );
	}

	/**
	 * Determine if a submission made at a given time is late for a student.
	 *
	 * Lateness is always computed against the student's current due date, so an extension granted after
	 * a late submission removes the "late" flag.
	 *
	 * @since [version]
	 *
	 * @param int        $student_id WP_User ID.
	 * @param int|string $submitted  Site local timestamp or date string of the submission.
	 * @return bool
	 */
	public function is_submission_late( $student_id, $submitted ) {
		$due       = $this->get_due_timestamp( $student_id );
		$submitted = is_numeric( $submitted ) ? (int) $submitted : strtotime( $submitted );
		return $due && $submitted && $submitted > $due;
	}

	/**
	 * Retrieve the due status for a student.
	 *
	 * @since [version]
	 *
	 * @param int             $student_id WP_User ID.
	 * @param int|string|null $submitted  Site local timestamp or date of the student's (first) submission, if any.
	 * @return string One of: none, upcoming, due-soon, overdue, on-time, late.
	 */
	public function get_due_status( $student_id, $submitted = null ) {

		$due = $this->get_due_timestamp( $student_id );
		if ( ! $due ) {
			return 'none';
		}

		if ( $submitted ) {
			return $this->is_submission_late( $student_id, $submitted ) ? 'late' : 'on-time';
		}

		$now = llms_current_time( 'timestamp' );
		if ( $now > $due ) {
			return 'overdue';
		}

		/**
		 * Filters the number of days before the due date when an item is considered "due soon".
		 *
		 * @since [version]
		 *
		 * @param int             $days Number of days.
		 * @param LLMS_Post_Model $item Quiz, assignment, etc.
		 */
		$soon = apply_filters( 'llms_due_soon_days', 3, $this );

		return ( $due - $now ) <= ( $soon * DAY_IN_SECONDS ) ? 'due-soon' : 'upcoming';
	}

	/**
	 * Retrieve the time of the student's first (completed) submission.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID.
	 * @return int|false Site local timestamp or `false` when the student hasn't submitted.
	 */
	abstract public function get_first_submission_time( $student_id );

	/**
	 * Retrieve the due status for a student based on their first submission.
	 *
	 * @since [version]
	 *
	 * @param int $student_id WP_User ID.
	 * @return string See {@see LLMS_Trait_Due_Date::get_due_status()}.
	 */
	public function get_student_due_status( $student_id ) {
		return $this->get_due_status( $student_id, $this->get_first_submission_time( $student_id ) );
	}

	/**
	 * Retrieve a translated label for a due status.
	 *
	 * @since [version]
	 *
	 * @param string $status Due status.
	 * @return string
	 */
	public static function get_due_status_label( $status ) {
		$labels = array(
			'none'     => __( 'No due date', 'lifterlms' ),
			'upcoming' => __( 'Upcoming', 'lifterlms' ),
			'due-soon' => __( 'Due soon', 'lifterlms' ),
			'overdue'  => __( 'Overdue', 'lifterlms' ),
			'on-time'  => __( 'On time', 'lifterlms' ),
			'late'     => __( 'Late', 'lifterlms' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * Retrieve the student-facing due date HTML: the due date plus a status badge.
	 *
	 * @since [version]
	 *
	 * @param int    $student_id WP_User ID.
	 * @param string $format     Date format. Defaults to the WordPress date and time formats.
	 * @return string Empty string when there is no due date for the student.
	 */
	public function get_due_date_html( $student_id, $format = '' ) {

		$date = $this->get_due_date_string( $student_id, $format );
		if ( ! $date ) {
			return '';
		}

		$status = $this->get_student_due_status( $student_id );

		// Due timestamps are "site local", so read the wall-clock time back in the site's timezone to get the offset.
		$datetime = date_create( gmdate( 'Y-m-d H:i:s', $this->get_due_timestamp( $student_id ) ), wp_timezone() );

		// The separator reads well when a theme doesn't load LifterLMS styles; styled badges hide it.
		$badge = 'upcoming' === $status ? '' : sprintf(
			'<span class="llms-due-sep" aria-hidden="true"> &middot; </span><span class="llms-due-badge llms-due-badge--%1$s">%2$s</span>',
			esc_attr( $status ),
			esc_html( self::get_due_status_label( $status ) )
		);

		return sprintf(
			'<span class="llms-due-date llms-due-date--%1$s">%2$s</span>%3$s',
			esc_attr( $status ),
			sprintf(
				// Translators: %s = Formatted due date.
				esc_html__( 'Due: %s', 'lifterlms' ),
				sprintf( '<time datetime="%1$s">%2$s</time>', esc_attr( $datetime->format( DATE_W3C ) ), esc_html( $date ) )
			),
			$badge
		);
	}
}
