<?php
/**
 * Engagements meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Engagements meta box class
 *
 * @since 1.0.0
 * @since 3.35.0 Verify nonce and access $_POST data via `llms_filter_input()`.
 */
class LLMS_Meta_Box_Engagement extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @return   void
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-engagement';
		$this->title    = __( 'Engagement Options', 'lifterlms' );
		$this->screens  = array(
			'llms_engagement',
		);
		$this->priority = 'high';
	}

	/**
	 * Return an empty array because the metabox fields here are completely custom
	 *
	 * @return   array
	 * @since    1.0.0
	 * @version  3.11.0
	 */
	public function get_fields() {

		$triggers = llms_get_engagement_triggers();

		$types = llms_get_engagement_types();

		$fields = array();

		$fields[] = array(
			'allow_null'    => false,
			'class'         => 'llms-select2',
			'desc'          => __( 'This engagement will be triggered when a student completes the selected action', 'lifterlms' ),
			'id'            => $this->prefix . 'trigger_type',
			'is_controller' => true,
			'type'          => 'select',
			'label'         => __( 'Triggering Event', 'lifterlms' ),
			'value'         => $triggers,
		);

		$trigger_post_fields = array(

			'course'           => array(
				'controller_value' => array(
					'course_completed',
					'course_enrollment',
					'course_purchased',
					'course_progress',
					'course_grade_below',
					'course_enrollment_cancelled',
					'course_enrollment_expired',
					'course_inactivity',
					'course_never_started',
					'course_completion_deadline',
					'order_failed',
					'order_refunded',
					'order_cancelled',
					'days_since_login',
				),
				'id'               => '_faux_engagement_trigger_post_course',
				'label'            => __( 'Select a Course', 'lifterlms' ),
				'placeholder'      => __( 'Any Course', 'lifterlms' ),
			),

			'lesson'           => array(
				'controller_value' => array( 'lesson_completed' ),
				'id'               => '_faux_engagement_trigger_post_lesson',
				'label'            => __( 'Select a Lesson', 'lifterlms' ),
				'placeholder'      => __( 'Any Lesson', 'lifterlms' ),
			),

			'llms_access_plan' => array(
				'controller_value' => array(
					'access_plan_purchased',
				),
				'id'               => '_faux_engagement_trigger_post_access_plan',
				'label'            => __( 'Select an Access Plan', 'lifterlms' ),
				'placeholder'      => __( 'Any Access Plan', 'lifterlms' ),
			),

			'llms_membership'  => array(
				'controller_value' => array(
					'membership_enrollment',
					'membership_purchased',
					'membership_enrollment_cancelled',
					'membership_enrollment_expired',
					'order_failed',
					'order_refunded',
					'order_cancelled',
					'days_since_login',
				),
				'id'               => '_faux_engagement_trigger_post_membership',
				'label'            => __( 'Select a Membership', 'lifterlms' ),
				'placeholder'      => __( 'Any Membership', 'lifterlms' ),
			),

			'llms_quiz'        => array(
				'controller_value' => array(
					'quiz_completed',
					'quiz_passed',
					'quiz_failed',
					'quiz_failed_multiple',
					'quiz_attempt_abandoned',
				),
				'id'               => '_faux_engagement_trigger_post_quiz',
				'label'            => __( 'Select a Quiz', 'lifterlms' ),
				'placeholder'      => __( 'Any Quiz', 'lifterlms' ),
			),

			'section'          => array(
				'controller_value' => array( 'section_completed' ),
				'id'               => '_faux_engagement_trigger_post_section',
				'label'            => __( 'Select a Section', 'lifterlms' ),
				'placeholder'      => __( 'Any Section', 'lifterlms' ),
			),

		);

		foreach ( $trigger_post_fields as $post_type => $data ) {

			$data['controller_value'] = apply_filters( 'llms_engagement_controller_values_' . $post_type, $data['controller_value'] );

			$trigger_post_val = get_post_meta( $this->post->ID, $this->prefix . 'engagement_trigger_post', true );
			if ( 'any' === $trigger_post_val || empty( $trigger_post_val ) ) {
				$val = array();
			} elseif ( in_array( get_post_meta( $this->post->ID, $this->prefix . 'trigger_type', true ), $data['controller_value'] ) ) {
				$val = llms_make_select2_post_array( array( $trigger_post_val ) );
			} else {
				$val = array();
			}

			$placeholder = isset( $data['placeholder'] ) ? $data['placeholder'] : $data['label'];

			$fields[] = array(
				'allow_null'       => false,
				'class'            => 'llms-select2-post',
				'controller'       => '#' . $this->prefix . 'trigger_type',
				'controller_value' => implode( ',', $data['controller_value'] ),
				'data_attributes'  => array(
					'allow_clear' => true,
					'placeholder' => $placeholder,
					'post-type'   => $post_type,
				),
				'desc'             => __( 'Leave blank to apply to all.', 'lifterlms' ),
				'id'               => $data['id'],
				'label'            => $data['label'],
				'type'             => 'select',
				'value'            => $val,
			);

		}

		$track_options = array();
		$tracks        = get_terms(
			'course_track',
			array(
				'hide_empty' => '0',
			)
		);
		foreach ( $tracks as $track ) {
			$track_options[] = array(
				'key'   => $track->term_id,
				'title' => $track->name . ' (ID# ' . $track->term_id . ')',
			);
		}

		$track_selected = get_post_meta( $this->post->ID, $this->prefix . 'engagement_trigger_post', true );
		if ( 'any' === $track_selected ) {
			$track_selected = '';
		}

		$fields[] = array(
			'allow_null'       => true,
			'class'            => 'llms-select2',
			'controller'       => '#' . $this->prefix . 'trigger_type',
			'controller_value' => implode( ',', apply_filters( 'llms_engagement_controller_values_track', array( 'course_track_completed' ) ) ),
			'data_attributes'  => array(
				'allow_clear' => true,
				'placeholder' => __( 'Any Course Track', 'lifterlms' ),
			),
			'id'               => '_faux_engagement_trigger_post_track',
			'label'            => __( 'Select a Course Track', 'lifterlms' ),
			'type'             => 'select',
			'selected'         => $track_selected,
			'value'            => $track_options,
		);

		$fields[] = array(
			'allow_null'    => false,
			'class'         => 'llms-select2',
			'desc'          => __( 'Determines the type of engagement', 'lifterlms' ),
			'id'            => $this->prefix . 'engagement_type',
			'is_controller' => true,
			'label'         => __( 'Engagement Type', 'lifterlms' ),
			'type'          => 'select',
			'value'         => $types,
		);

		$type    = get_post_meta( $this->post->ID, $this->prefix . 'engagement_type', true );
		$default = ( ! $type ) ? 'llms_achievement' : 'llms_' . $type;

		$fields[] = array(
			'allow_null'      => false,
			'class'           => 'llms-select2-post',
			'data_attributes' => array(
				'allow_clear' => true,
				'placeholder' => __( 'Select an Engagement', 'lifterlms' ),
				'post-type'   => $default,
				'edit-button' => true,
			),
			'id'              => $this->prefix . 'engagement',
			'label'           => __( 'Select an Engagement', 'lifterlms' ),
			'type'            => 'select',
			'value'           => llms_make_select2_post_array( array( get_post_meta( $this->post->ID, $this->prefix . 'engagement', true ) ) ),
		);

		$fields[] = array(
			'class'            => 'input-full',
			'controller'       => '#' . $this->prefix . 'trigger_type',
			'controller_value' => implode(
				',',
				/**
				 * Filters the list of triggers which display the percentage threshold field.
				 *
				 * @since [version]
				 *
				 * @param string[] $triggers List of trigger type slugs.
				 */
				apply_filters( 'llms_engagement_percentage_controller_values', array( 'course_progress', 'course_grade_below' ) )
			),
			'desc'             => __( 'Enter the percentage threshold for this trigger (1-100).', 'lifterlms' ),
			'id'               => $this->prefix . 'engagement_trigger_percentage',
			'label'            => __( 'Percentage', 'lifterlms' ),
			'min'              => 1,
			'max'              => 100,
			'type'             => 'number',
		);

		$fields[] = array(
			'class'            => 'input-full',
			'controller'       => '#' . $this->prefix . 'trigger_type',
			'controller_value' => implode(
				',',
				/**
				 * Filters the list of triggers which display the inactivity period field.
				 *
				 * Add-ons registering scan-based triggers via `llms_scannable_engagement_triggers`
				 * should also register their trigger slugs here so the period field displays.
				 *
				 * @since [version]
				 *
				 * @param string[] $triggers List of trigger type slugs.
				 */
				apply_filters(
					'llms_engagement_period_controller_values',
					array(
						'days_since_login',
						'course_inactivity',
						'course_never_started',
						'course_completion_deadline',
						'quiz_attempt_abandoned',
					)
				)
			),
			'desc'             => __( 'Enter the number of days of inactivity required to trigger this engagement. This field is required and the trigger is checked once daily.', 'lifterlms' ),
			'id'               => $this->prefix . 'engagement_trigger_period',
			'label'            => __( 'Number of Days', 'lifterlms' ),
			'min'              => 1,
			'type'             => 'number',
		);

		$fields[] = array(
			'class'            => 'llms-datepicker',
			'controller'       => '#' . $this->prefix . 'trigger_type',
			'controller_value' => implode(
				',',
				/**
				 * Filters the list of triggers which display the "Activity on or after" date field.
				 *
				 * Add-ons registering scan-based triggers via `llms_scannable_engagement_triggers`
				 * should also register their trigger slugs here so the date floor field displays.
				 *
				 * @since [version]
				 *
				 * @param string[] $triggers List of trigger type slugs.
				 */
				apply_filters(
					'llms_engagement_since_controller_values',
					array(
						'days_since_login',
						'course_inactivity',
						'course_never_started',
						'course_completion_deadline',
						'quiz_attempt_abandoned',
					)
				)
			),
			'date_format'      => 'yy-mm-dd',
			'default'          => gmdate( 'Y-m-d', strtotime( '-3 months', llms_current_time( 'timestamp' ) ) ),
			'desc'             => __( 'Only students whose last relevant activity (login, course progress, enrollment, or quiz attempt) is on or after this date will be included. Leave blank to include every student who currently exceeds the number of days, including those inactive for months.', 'lifterlms' ),
			'id'               => $this->prefix . 'engagement_trigger_since',
			'label'            => __( 'Activity on or after', 'lifterlms' ),
			'type'             => 'date',
		);

		$fields[] = array(
			'class'            => 'input-full',
			'controller'       => '#' . $this->prefix . 'trigger_type',
			'controller_value' => implode(
				',',
				/**
				 * Filters the list of triggers which display the failure count field.
				 *
				 * @since [version]
				 *
				 * @param string[] $triggers List of trigger type slugs.
				 */
				apply_filters( 'llms_engagement_count_controller_values', array( 'quiz_failed_multiple' ) )
			),
			'desc'             => __( 'Enter the number of failed attempts required to trigger this engagement.', 'lifterlms' ),
			'id'               => $this->prefix . 'engagement_trigger_count',
			'label'            => __( 'Number of Failures', 'lifterlms' ),
			'min'              => 1,
			'type'             => 'number',
		);

		$fields[] = array(
			'class'   => 'input-full',
			'default' => 0,
			'desc'    => __( 'Enter the number of days to wait before triggering this engagement. Enter 0 or leave blank to trigger immediately.', 'lifterlms' ),
			'id'      => $this->prefix . 'engagement_delay',
			'label'   => __( 'Engagement Delay', 'lifterlms' ),
			'min'     => 0,
			'type'    => 'number',
		);

		return array(
			array(
				'title'  => __( 'Engagement Settings', 'lifterlms' ),
				'fields' => $fields,
			),
		);
	}

	/**
	 * Custom save method.
	 *
	 * Ensures that the faux fields are not saved to the postmeta table.
	 *
	 * @since 3.1.0
	 * @since 3.11.0 Unknown.
	 * @since 3.35.0 Verify nonce and access $_POST data via `llms_filter_input()`.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param int $post_id WP Post ID of the engagement.
	 * @return void
	 */
	public function save( $post_id ) {

		if ( ! isset( $_REQUEST['lifterlms_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['lifterlms_meta_nonce'] ) ), 'lifterlms_save_data' ) ) {
			return;
		}

		// Get all defined fields.
		$fields = $this->get_fields();

		if ( ! is_array( $fields ) ) {
			return;
		}

		// Loop through the fields.
		foreach ( $fields as $group => $data ) {

			// Find the fields in each tab.
			if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {

				// Loop through the fields.
				foreach ( $data['fields'] as $field ) {

					// Don't save things that don't have an ID.
					if ( isset( $field['id'] ) ) {

						// Skip our faux fields.
						if ( 0 === strpos( $field['id'], '_faux_engagement_trigger_post_' ) ) {
							continue;
						}

						// Get the posted value.
						if ( isset( $_POST[ $field['id'] ] ) ) {

							$val = llms_filter_input_sanitize_string( INPUT_POST, $field['id'] );

						} elseif ( ! isset( $_POST[ $field['id'] ] ) ) {

							$val = '';

						}

						// Update the value if we have one.
						if ( isset( $val ) ) {

							update_post_meta( $post_id, $field['id'], $val );

						}

						unset( $val );

					}
				}
			}
		}

		// Locate and store the trigger post id.
		$type = llms_filter_input( INPUT_POST, $this->prefix . 'trigger_type' );
		switch ( $type ) {

			case 'access_plan_purchased':
				$var = 'access_plan';
				break;

			case 'course_completed':
			case 'course_purchased':
			case 'course_enrollment':
			case 'course_progress':
			case 'course_grade_below':
			case 'course_enrollment_cancelled':
			case 'course_enrollment_expired':
			case 'course_inactivity':
			case 'course_never_started':
			case 'course_completion_deadline':
				$var = 'course';
				break;

			case 'lesson_completed':
				$var = 'lesson';
				break;

			case 'membership_purchased':
			case 'membership_enrollment':
			case 'membership_enrollment_cancelled':
			case 'membership_enrollment_expired':
				$var = 'membership';
				break;

			case 'quiz_completed':
			case 'quiz_passed':
			case 'quiz_failed':
			case 'quiz_failed_multiple':
			case 'quiz_attempt_abandoned':
				$var = 'quiz';
				break;

			case 'section_completed':
				$var = 'section';
				break;

			case 'course_track_completed':
				$var = 'track';
				break;

			// These triggers can be scoped to either a course or a membership.
			case 'order_failed':
			case 'order_refunded':
			case 'order_cancelled':
			case 'days_since_login':
				$var = llms_filter_input_sanitize_string( INPUT_POST, '_faux_engagement_trigger_post_course' ) ? 'course' : 'membership';
				break;

			default:
				/**
				 * Filters the faux trigger-post field suffix used to store the trigger post for third-party trigger types.
				 *
				 * Allows add-ons registering custom engagement triggers to reuse the existing
				 * trigger-post pickers (e.g. return 'course' to store the course picker's value).
				 *
				 * @since [version]
				 *
				 * @param string|false $var  The field suffix or `false` when the trigger has no related post.
				 * @param string       $type The engagement trigger type slug.
				 */
				$var = apply_filters( 'llms_engagement_trigger_post_field', false, $type );

		}

		if ( $var ) {

			$val = llms_filter_input_sanitize_string( INPUT_POST, '_faux_engagement_trigger_post_' . $var );

			// An empty trigger post means "any" — store explicitly so the intent is clear.
			if ( empty( $val ) ) {
				$val = 'any';
			}
		} else {

			$val = '';

		}

		update_post_meta( $post_id, $this->prefix . 'engagement_trigger_post', $val );
	}
}
