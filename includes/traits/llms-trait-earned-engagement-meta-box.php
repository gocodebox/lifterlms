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
		'llms_my_achievement' => array(
			'model'                => 'LLMS_User_Achievement',
			'user_postmeta_prefix' => '_achievement',
		),
		'llms_my_certificate' => array(
			'model'                => 'LLMS_User_Certificate',
			'user_postmeta_prefix' => '_certificate',
		),
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

		$post_type = get_post_type();
		if ( ! array_key_exists( $post_type, $this->allowed_post_types ) ) {
			return $fields;
		}

		$student = ! empty( $_GET['sid'] ) ? llms_filter_input( INPUT_GET, 'sid', FILTER_SANITIZE_NUMBER_INT ) : false; // phpcs:ignore
		$student = empty( $student ) ? ( new $this->allowed_post_types[ $post_type ]['model']( $this->post->ID ) )->get_user_id() : $student;

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

	/**
	 * Save a metabox field.
	 *
	 * @since [version]
	 *
	 * @param int   $post_id WP_Post ID.
	 * @param array $field   Metabox field array.
	 * @return boolean
	 */
	protected function save_field( $post_id, $field ) {

		if ( $this->prefix . 'student' === $field['id'] && isset( $_POST[ $field['id'] ] ) ) { //phpcs:ignore -- nonce already verified in `LLMS_Admin_Metabox::save`.
			$this->log_earned_engament( llms_filter_input( INPUT_POST, $field['id'], FILTER_SANITIZE_NUMBER_INT ), $post_id );
		}

		/**
		 * Skip saving _llms_student field, only used to award an engagement, it's not a post field.
		 */
		if ( isset( $field['id'] ) && $this->prefix . 'student' === $field['id'] ) {
			return true;
		}

		parent::save_field( $post_id, $field );

	}

	/**
	 * Wheter the user has earned the engagement.
	 *
	 * @since [version]
	 *
	 * @param int $user_id The student's user id.
	 * @param int $post_id The earned engagement id.
	 */
	private function has_user_earned( $user_id, $post_id ) {
		global $wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT( meta_id )
				FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE user_id=%d
				AND meta_value=%d
				",
				array(
					$user_id,
					$post_id,
				)
			)
		);
	}

	/**
	 * Wheter the user has earned the engagement.
	 *
	 * @since [version]
	 *
	 * @param int $user_id The student's user id.
	 * @param int $post_id The earned engagement id.
	 * @return void
	 */
	private function log_earned_engament( $user_id, $post_id ) {

		// Log earned engagement.
		if ( ! $this->has_user_earned( $user_id, $post_id ) ) { // We need a better LLMS_Achievement(Certificate)_User::has_user_earned() method.
			$post_type = get_post_type();
			$prefix    = $this->allowed_post_types[ $post_type ]['user_postmeta_prefix'];

			// We need a better LLMS_User_Achievement(Certificate)::create() method.
			global $wpdb;
			$wpdb->insert(
				"{$wpdb->prefix}lifterlms_user_postmeta",
				array(
					'user_id'      => $user_id,
					'post_id'      => 0,
					'meta_key'     => "{$prefix}_earned",
					'meta_value'   => $post_id,
					'updated_date' => current_time( 'mysql' ),
				)
			);

			/**
			 * Allow 3rd parties to hook into the generation of an achievement
			 * Notifications uses this
			 * note 3rd param $this->lesson_id is actually the related post id (but misnamed)
			 */
			do_action( "llms_user_earned$prefix", $user_id, $post_id, 0 );

		}

	}

}
