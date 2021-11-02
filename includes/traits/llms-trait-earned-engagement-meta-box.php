<?php
/**
 * LifterLMS Eearned Engagements (Certificate/Achievement) Meta Box trait.
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Eearned Engagements (Certificate/Achievement) Meta Box trait.
 *
 * **This trait should only be used by classes that extend from the {@see LLMS_Admin_Metabox} class.**
 *
 * @since [version]
 */
trait LLMS_Trait_Earned_Engagement_Meta_Box {

	/**
	 * Allowed post types.
	 *
	 * @since [version]
	 *
	 * @var string[]
	 */
	private $allowed_post_types = array(
		'llms_my_achievement',
		'llms_my_certificate',
	);

	/**
	 * Add earned engagement fields.
	 *
	 * @since [version]
	 *
	 * @param array $fields  Array of metabox fields.
	 * @return array
	 */
	protected function add_earned_engagement_fields( $fields = array() ) {

		if ( ! in_array( get_post_type(), $this->allowed_post_types, true ) ) {
			return $fields;
		}

		$student = ! empty( $_GET['sid'] ) ? llms_filter_input( INPUT_GET, 'sid', FILTER_SANITIZE_NUMBER_INT ) : false; // phpcs:ignore
		$student = empty( $student ) ? ( new LLMS_User_Certificate( $this->post->ID ) )->get_user_id() : $student;

		if ( empty( $student ) ) {
			$fields[] = array(
				'allow_null'      => false,
				'class'           => 'llms-select2-student',
				'data_attributes' => array(
					'allow_clear' => false,
					'placeholder' => __( 'Select a Student', 'lifterlms' ),
				),
				'id'              => $this->prefix . 'student',
				'label'           => __( 'Select a Student', 'lifterlms' ),
				'type'            => 'select',
			);
		} else {
			array_unshift(
				$fields,
				array(
					'id'    => $this->prefix . 'student',
					'type'  => 'hidden',
					'value' => $student,
				)
			);
		}

		return $fields;
	}

}
