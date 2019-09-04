<?php
/**
 * Engagements Metabox
 *
 * @since 1.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Engagements Metabox
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
				),
				'id'               => '_faux_engagement_trigger_post_course',
				'label'            => __( 'Select a Course', 'lifterlms' ),
			),

			'lesson'           => array(
				'controller_value' => array( 'lesson_completed' ),
				'id'               => '_faux_engagement_trigger_post_lesson',
				'label'            => __( 'Select a Lesson', 'lifterlms' ),
			),

			'llms_access_plan' => array(
				'controller_value' => array(
					'access_plan_purchased',
				),
				'id'               => '_faux_engagement_trigger_post_access_plan',
				'label'            => __( 'Select an Access Plan', 'lifterlms' ),
			),

			'llms_membership'  => array(
				'controller_value' => array(
					'membership_enrollment',
					'membership_purchased',
				),
				'id'               => '_faux_engagement_trigger_post_membership',
				'label'            => __( 'Select a Membership', 'lifterlms' ),
			),

			'llms_quiz'        => array(
				'controller_value' => array(
					'quiz_completed',
					'quiz_passed',
					'quiz_failed',
				),
				'id'               => '_faux_engagement_trigger_post_quiz',
				'label'            => __( 'Select a Quiz', 'lifterlms' ),
			),

			'section'          => array(
				'controller_value' => array( 'section_completed' ),
				'id'               => '_faux_engagement_trigger_post_section',
				'label'            => __( 'Select a Section', 'lifterlms' ),
			),

		);

		foreach ( $trigger_post_fields as $post_type => $data ) {

			$data['controller_value'] = apply_filters( 'llms_engagement_controller_values_' . $post_type, $data['controller_value'] );

			if ( in_array( get_post_meta( $this->post->ID, $this->prefix . 'trigger_type', true ), $data['controller_value'] ) ) {
				$val = llms_make_select2_post_array( array( get_post_meta( $this->post->ID, $this->prefix . 'engagement_trigger_post', true ) ) );
			} else {
				$val = array();
			}

			$fields[] = array(
				'allow_null'       => false,
				'class'            => 'llms-select2-post',
				'controller'       => '#' . $this->prefix . 'trigger_type',
				'controller_value' => implode( ',', $data['controller_value'] ),
				'data_attributes'  => array(
					'allow_clear' => true,
					'placeholder' => $data['label'],
					'post-type'   => $post_type,
				),
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

		$fields[] = array(
			'allow_null'       => false,
			'class'            => 'llms-select2',
			'controller'       => '#' . $this->prefix . 'trigger_type',
			'controller_value' => implode( ',', apply_filters( 'llms_engagement_controller_values_track', array( 'course_track_completed' ) ) ),
			'data_attributes'  => array(
				'allow_clear' => true,
				'placeholder' => __( 'Select a Course Track', 'lifterlms' ),
			),
			'id'               => '_faux_engagement_trigger_post_track',
			'label'            => __( 'Select a Course Track', 'lifterlms' ),
			'type'             => 'select',
			'selected'         => get_post_meta( $this->post->ID, $this->prefix . 'engagement_trigger_post', true ),
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
			),
			'id'              => $this->prefix . 'engagement',
			'label'           => __( 'Select an Engagement', 'lifterlms' ),
			'type'            => 'select',
			'value'           => llms_make_select2_post_array( array( get_post_meta( $this->post->ID, $this->prefix . 'engagement', true ) ) ),
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
	 * Custom save method
	 * ensures that the faux fields are not saved to the postmeta table
	 *
	 * @since 3.1.0
	 * @since 3.11.0 Unknown.
	 * @since 3.35.0 Verify nonce and access $_POST data via `llms_filter_input()`.
	 *
	 * @param    int $post_id  WP Post ID of the engagement
	 * @return   void
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		// get all defined fields
		$fields = $this->get_fields();

		if ( ! is_array( $fields ) ) {
			return;
		}

		// loop through the fields
		foreach ( $fields as $group => $data ) {

			// find the fields in each tab
			if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {

				// loop through the fields
				foreach ( $data['fields'] as $field ) {

					// don't save things that don't have an ID
					if ( isset( $field['id'] ) ) {

						// skip our faux fields
						if ( 0 === strpos( $field['id'], '_faux_engagement_trigger_post_' ) ) {
							continue;
						}

						// get the posted value
						if ( isset( $_POST[ $field['id'] ] ) ) {

							$val = llms_filter_input( INPUT_POST, $field['id'], FILTER_SANITIZE_STRING );

						} elseif ( ! isset( $_POST[ $field['id'] ] ) ) {

							$val = '';

						}

						// update the value if we have one
						if ( isset( $val ) ) {

							update_post_meta( $post_id, $field['id'], $val );

						}

						unset( $val );

					}
				}
			}// End if().
		}// End foreach().

		// locate and store the trigger post id
		$type = llms_filter_input( INPUT_POST, $this->prefix . 'trigger_type', FILTER_SANITIZE_STRING );
		switch ( $type ) {

			case 'access_plan_purchased':
				$var = 'access_plan';
				break;

			case 'course_completed':
			case 'course_purchased':
			case 'course_enrollment':
				$var = 'course';
				break;

			case 'lesson_completed':
				$var = 'lesson';
				break;

			case 'membership_purchased':
			case 'membership_enrollment':
				$var = 'membership';
				break;

			case 'quiz_completed':
			case 'quiz_passed':
			case 'quiz_failed':
				$var = 'quiz';
				break;

			case 'section_completed':
				$var = 'section';
				break;

			case 'course_track_completed':
				$var = 'track';
				break;

			default:
				$var = false;

		}

		if ( $var ) {

			$val = llms_filter_input( INPUT_POST, '_faux_engagement_trigger_post_' . $var, FILTER_SANITIZE_STRING );

		} else {

			$val = '';

		}

		update_post_meta( $post_id, $this->prefix . 'engagement_trigger_post', $val );

	}

}
