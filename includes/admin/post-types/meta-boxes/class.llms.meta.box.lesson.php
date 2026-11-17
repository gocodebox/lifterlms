<?php
/**
 * Lesson Settings meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 7.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lesson Settings meta box class
 *
 * @since 1.0.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.36.2 'start' drip method made avialble only if the parent course has a start date set.
 */
class LLMS_Meta_Box_Lesson extends LLMS_Admin_Metabox {

	/**
	 * This function allows extending classes to configure required class properties
	 * $this->id, $this->title, and $this->screens should be configured in this function
	 *
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-lesson';
		$this->title    = __( 'Lesson Settings', 'lifterlms' );
		$this->screens  = array(
			'lesson',
		);
		$this->priority = 'high';
	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox.
	 *
	 * The function must return an array which can be consumed by the "output" function.
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Fixed spelling errors.
	 * @since 3.36.2 'start' drip method made available only if the parent course has a start date set.
	 * @since 7.1.3 Replace outdated URLs to WordPress' documentation about the list of sites you can embed from.
	 *
	 * @return array
	 */
	public function get_fields() {

		$lesson = llms_get_post( $this->post );

		$total_seconds  = $lesson ? absint( $lesson->get( 'minimum_time' ) ) : 0;
		$min_time_hours = floor( $total_seconds / 3600 );
		$min_time_mins  = floor( ( $total_seconds % 3600 ) / 60 );
		$min_time_secs  = $total_seconds % 60;

		$methods = array(
			'date'       => __( 'On a specific date', 'lifterlms' ),
			'enrollment' => __( 'After course enrollment', 'lifterlms' ),
			'start'      => __( 'After course start date', 'lifterlms' ),
		);

		$section = $lesson->get_section();

		// Tf the lesson isn't first, add previous completion method.
		if ( 1 !== $lesson->get( 'order' ) || ( $section && 1 !== $section->get( 'order' ) ) ) {
			$methods['prerequisite'] = __( 'After prerequisite completion', 'lifterlms' );
		}

		// If the parent course has no start date set, unset the 'start' drip method.
		$course = $lesson->get_course();
		if ( ! $course || ! $course->get_date( 'start_date' ) ) {
			unset( $methods['start'] );
		}

		$fields = array(
			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => array(
					array(
						'class'      => 'code input-full',
						'desc'       => sprintf( __( 'Paste the url for a Wistia, Vimeo or Youtube video or a hosted video file. For a full list of supported providers see %s.', 'lifterlms' ), '<a href="https://wordpress.org/documentation/article/embeds/#list-of-sites-you-can-embed-from" target="_blank">WordPress oEmbeds</a>' ),
						'desc_class' => 'd-all',
						'id'         => $this->prefix . 'video_embed',
						'label'      => __( 'Video Embed Url', 'lifterlms' ),
						'type'       => 'text',
					),
					array(
						'class'      => 'code input-full',
						'desc'       => sprintf( __( 'Paste the url for a SoundCloud or Spotify song or a hosted audio file. For a full list of supported providers see %s.', 'lifterlms' ), '<a href="https://wordpress.org/documentation/article/embeds/#list-of-sites-you-can-embed-from" target="_blank">WordPress oEmbeds</a>' ),
						'desc_class' => 'd-all',
						'id'         => $this->prefix . 'audio_embed',
						'type'       => 'text',
						'label'      => __( 'Audio Embed Url', 'lifterlms' ),
					),
					array(
						'class'         => '',
						'desc'          => __( 'Checking this box will allow guests to view the content of this lesson without registering or signing up for the course.', 'lifterlms' ),
						'desc_class'    => 'd-3of4 t-3of4 m-1of2',
						'id'            => $this->prefix . 'free_lesson',
						'is_controller' => true,
						'label'         => __( 'Free Lesson', 'lifterlms' ),
						'type'          => 'checkbox',
						'value'         => 'yes',
					),
					array(
						'class'            => '',
						'controls'         => '#' . $this->prefix . 'minimum_time_hours, #' . $this->prefix . 'minimum_time_minutes, #' . $this->prefix . 'minimum_time_seconds',
						'controller'       => '#' . $this->prefix . 'free_lesson',
						'controller_value' => 'false',
						'desc'             => __( 'Require students to spend a minimum amount of time on this lesson before they can mark it complete.', 'lifterlms' ),
						'desc_class'       => 'd-3of4 t-3of4 m-1of2',
						'id'               => $this->prefix . 'has_minimum_time',
						'is_controller'    => true,
						'label'            => __( 'Enable Minimum Time', 'lifterlms' ),
						'type'             => 'checkbox',
						'value'            => 'yes',
					),
					array(
						'class'            => 'input-full',
						'controller'       => '#' . $this->prefix . 'has_minimum_time',
						'controller_value' => 'yes',
						'desc'             => __( 'Hours', 'lifterlms' ),
						'desc_class'       => 'd-all',
						'id'               => $this->prefix . 'minimum_time_hours',
						'label'            => __( 'Hours', 'lifterlms' ),
						'meta'             => $min_time_hours,
						'min'              => 0,
						'max'              => 999,
						'type'             => 'number',
					),
					array(
						'class'            => 'input-full',
						'controller'       => '#' . $this->prefix . 'has_minimum_time',
						'controller_value' => 'yes',
						'desc'             => __( 'Minutes (0-59)', 'lifterlms' ),
						'desc_class'       => 'd-all',
						'id'               => $this->prefix . 'minimum_time_minutes',
						'label'            => __( 'Minutes', 'lifterlms' ),
						'meta'             => $min_time_mins,
						'min'              => 0,
						'max'              => 59,
						'type'             => 'number',
					),
					array(
						'class'            => 'input-full',
						'controller'       => '#' . $this->prefix . 'has_minimum_time',
						'controller_value' => 'yes',
						'desc'             => __( 'Seconds (0-59)', 'lifterlms' ),
						'desc_class'       => 'd-all',
						'id'               => $this->prefix . 'minimum_time_seconds',
						'label'            => __( 'Seconds', 'lifterlms' ),
						'meta'             => $min_time_secs,
						'min'              => 0,
						'max'              => 59,
						'type'             => 'number',
					),
				),
			),
			array(
				'title'  => __( 'Prerequisites', 'lifterlms' ),
				'fields' => array(
					array(
						'class'      => '',
						'controls'   => '#' . $this->prefix . 'prerequisite',
						'desc'       => __( 'Enable to choose a prerequisite Lesson', 'lifterlms' ),
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'id'         => $this->prefix . 'has_prerequisite',
						'label'      => __( 'Enable Prerequisite', 'lifterlms' ),
						'type'       => 'checkbox',
						'value'      => 'yes',
					),
					array(
						'class'           => 'llms-select2-post',
						'data_attributes' => array(
							'allow-clear' => true,
							'placeholder' => __( 'Select a Prerequisite Lesson', 'lifterlms' ),
							'post-type'   => 'lesson',
						),
						'desc'            => __( 'Select the prerequisite lesson', 'lifterlms' ),
						'desc_class'      => 'd-all',
						'id'              => $this->prefix . 'prerequisite',
						'label'           => __( 'Choose Prerequisite', 'lifterlms' ),
						'type'            => 'select',
						'value'           => llms_make_select2_post_array( array( get_post_meta( $this->post->ID, $this->prefix . 'prerequisite', true ) ) ),
					),
				),
			),
			'drip' => array(
				'title'  => __( 'Drip Settings', 'lifterlms' ),
				'fields' => array(
					array(
						'type'  => 'custom-html',
						'id'    => $this->prefix . 'drip_course_settings_info',
						'value' => $this->get_drip_course_settings_info_html( $course ),
					),
				),
			),
			array(
				'title'  => __( 'Quiz', 'lifterlms' ),
				'fields' => array(
					array(
						'controller'       => '#' . $this->prefix . 'free_lesson',
						'controller_value' => 'false',
						'desc'             => __( 'Checking this box will require students to get a passing score on the above quiz to complete the lesson.', 'lifterlms' ),
						'desc_class'       => 'd-3of4 t-3of4 m-1of2',
						'id'               => $this->prefix . 'require_passing_grade',
						'label'            => __( 'Require Passing Grade', 'lifterlms' ),
						'type'             => 'checkbox',
						'value'            => 'yes',
					),
				),
			),
		);

		if ( ! $course || 'yes' !== $course->get( 'lesson_drip' ) || ! $course->get( 'drip_method' ) ) {
			$fields['drip']['fields'][] = array(
				'class'         => 'llms-select2',
				'desc_class'    => 'd-all',
				'id'            => $this->prefix . 'drip_method',
				'is_controller' => true,
				'label'         => __( 'Method', 'lifterlms' ),
				'type'          => 'select',
				'value'         => $methods,
			);
			$fields['drip']['fields'][] = array(
				'controller'       => '#' . $this->prefix . 'drip_method',
				'controller_value' => 'lesson,enrollment,start,prerequisite',
				'class'            => 'input-full',
				'id'               => $this->prefix . 'days_before_available',
				'label'            => __( 'Delay (in days) ', 'lifterlms' ),
				'type'             => 'number',
				'step'             => 1,
				'min'              => 0,
			);
			$fields['drip']['fields'][] = array(
				'controller'       => '#' . $this->prefix . 'drip_method',
				'controller_value' => 'date',
				'class'            => '',
				'handler'          => 'drip_date_time',
				'id'               => $this->prefix . 'date_available',
				'input_type'       => 'date',
				'label'            => __( 'Date Available', 'lifterlms' ),
				// Without a max, browsers accept years with up to 6 digits.
				'max'              => '9999-12-31',
				'meta'             => $this->get_drip_input_value( 'date', $lesson->get( 'date_available' ) ),
				'type'             => 'text',
			);
			$fields['drip']['fields'][] = array(
				'controller'       => '#' . $this->prefix . 'drip_method',
				'controller_value' => 'date',
				'class'            => '',
				'desc'             => __( 'Optionally enter a time when the lesson should become available. If no time is supplied, the lesson will be available at 12:00 AM.', 'lifterlms' ),
				'handler'          => 'drip_date_time',
				'id'               => $this->prefix . 'time_available',
				'input_type'       => 'time',
				'label'            => __( 'Time Available', 'lifterlms' ),
				'meta'             => $this->get_drip_input_value( 'time', $lesson->get( 'time_available' ) ),
				'type'             => 'text',
			);
		}

		return $fields;
	}

	/**
	 * Convert a stored drip date or time into the value a native date or time input accepts.
	 *
	 * Dates are stored as `Y-m-d`, or `m/d/Y` when saved by this metabox before 8.0.7. Times are stored as `h:i A`
	 * (Course Builder and this metabox), `H:i:s` (REST API), or free text typed into this metabox before it used a
	 * time input. Values are read the way `strtotime()` reads them in `LLMS_Lesson::get_available_date()`, and a
	 * value that can't be read returns an empty string. Matches `get_input_value()` in the Course Builder.
	 *
	 * @since [version]
	 *
	 * @param string $type  Input type, `date` or `time`.
	 * @param string $value Stored value.
	 * @return string `Y-m-d` date, 24-hour `H:i` time, or an empty string.
	 */
	protected function get_drip_input_value( $type, $value ) {

		$value = trim( (string) $value );

		if ( 'date' === $type ) {
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
				return $value;
			}
			return preg_match( '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $parts ) ? sprintf( '%s-%02d-%02d', $parts[3], $parts[1], $parts[2] ) : '';
		}

		if ( ! preg_match( '/^(\d{1,2})(?::(\d{2})(?::\d{2})?)?\s*(?:([ap])\.?m\.?)?$/i', $value, $parts ) || ( empty( $parts[2] ) && empty( $parts[3] ) ) ) {
			return '';
		}

		$hour = (int) $parts[1];
		$min  = empty( $parts[2] ) ? 0 : (int) $parts[2];

		if ( ! empty( $parts[3] ) ) {
			if ( $hour < 1 || $hour > 12 ) {
				return '';
			}
			$hour = ( $hour % 12 ) + ( 'p' === strtolower( $parts[3] ) ? 12 : 0 );
		}

		return ( $hour > 23 || $min > 59 ) ? '' : sprintf( '%02d:%02d', $hour, $min );
	}

	/**
	 * Save the drip date or time from its native input.
	 *
	 * The date is stored as the input's `Y-m-d` value. The time input's 24-hour `H:i` value is stored as `h:i A`,
	 * the format the Course Builder saves. Anything else, including an empty input, is stored as an empty string;
	 * an empty time makes the lesson available at 12:00 AM.
	 *
	 * @since [version]
	 *
	 * @param int   $post_id WP Post ID of the lesson.
	 * @param array $field   Metabox field array.
	 * @param array $request Posted data.
	 * @return bool
	 */
	protected function handler_drip_date_time( $post_id, $field, $request ) {

		$val    = isset( $request[ $field['id'] ] ) ? sanitize_text_field( wp_unslash( $request[ $field['id'] ] ) ) : '';
		$format = 'time' === $field['input_type'] ? 'H:i' : 'Y-m-d';
		$date   = DateTime::createFromFormat( '!' . $format, $val );

		if ( ! $date || $date->format( $format ) !== $val ) {
			$val = '';
		} elseif ( 'time' === $field['input_type'] ) {
			$val = $date->format( 'h:i A' );
		}

		return $this->save_field_db( $post_id, $field['id'], $val );
	}

	/**
	 * Compute minimum_time (total seconds) from H/M/S fields after save.
	 *
	 * @since 10.1.0
	 *
	 * @param int $post_id WP Post ID of the post being saved.
	 * @return void
	 */
	protected function save_after( $post_id ) {

		$has_minimum_time = get_post_meta( $post_id, $this->prefix . 'has_minimum_time', true );

		if ( 'yes' !== $has_minimum_time ) {
			update_post_meta( $post_id, $this->prefix . 'minimum_time', 0 );
			return;
		}

		$hours   = absint( get_post_meta( $post_id, $this->prefix . 'minimum_time_hours', true ) );
		$minutes = absint( get_post_meta( $post_id, $this->prefix . 'minimum_time_minutes', true ) );
		$seconds = absint( get_post_meta( $post_id, $this->prefix . 'minimum_time_seconds', true ) );

		$total = ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;

		update_post_meta( $post_id, $this->prefix . 'minimum_time', $total );
	}

	/**
	 * Helpful messaging depending on whether the course for this lesson has drip settings enabled or not.
	 *
	 * @since 7.6.0
	 *
	 * @param LLMS_Course $course Course object.
	 * @return string
	 */
	public function get_drip_course_settings_info_html( $course ) {
		if ( ! $course ) {
			return '';
		}

		$output = 'yes' === $course->get( 'lesson_drip' ) && $course->get( 'drip_method' ) ?
			esc_html__( 'Drip settings are currently set at the course level, under the Restrictions settings tab. If you would like to set individual drip settings for each lesson, you must disable the course level drip settings first.', 'lifterlms' )
		:
			esc_html__( 'Drip settings can be set at the course level to release course content at a specified interval, in the Restrictions settings tab.', 'lifterlms' );

		$output .= ' <a href="' . esc_url( admin_url( 'post.php?post=' . $course->get( 'id' ) . '&action=edit#lifterlms-course-options' ) ) . '">' . esc_html__( 'Edit Course', 'lifterlms' ) . '</a>';

		return $output;
	}
}

new LLMS_Meta_Box_Lesson();
