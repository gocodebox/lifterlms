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
 * This trait should only be used by classes that extend from the {@see LLMS_Admin_Metabox} class.
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
			'model'           => 'LLMS_User_Achievement',
			'engagement_type' => 'achievement',
		),
		'llms_my_certificate' => array(
			'model'           => 'LLMS_User_Certificate',
			'engagement_type' => 'certificate',
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
		$student = empty( $student ) && 'add' !== get_current_screen()->action ? ( new $this->allowed_post_types[ $post_type ]['model']( $this->post->ID ) )->get_user_id() : $student;

		// The `post_author_override` is the same used in WP core for the author selector.
		if ( empty( $student ) ) {
			$fields[] = array(
				'allow_null'      => false,
				'class'           => 'llms-select2-student',
				'data_attributes' => array(
					'allow_clear' => false,
					'placeholder' => __( 'Select a Student', 'lifterlms' ),
				),
				'id'              => 'post_author_override',
				'label'           => __( 'Select a Student', 'lifterlms' ),
				'type'            => 'select',
				'skip_save'       => true,
			);
		} else {
			array_unshift(
				$fields,
				array(
					'id'        => 'post_author_override',
					'type'      => 'hidden',
					'value'     => $student,
					'skip_save' => true,
				)
			);
		}

		return $fields;
	}

	/**
	 * Maybe log engagment awarding
	 *
	 * Called after `$this->save()` during `$this->save_actions()`.
	 *
	 * @since [version]
	 *
	 * @param int $post_id WP Post ID of the post being saved.
	 * @return void
	 */
	protected function save_after( $post_id ) {

		$post      = get_post( $post_id );
		$post_type = get_post_type( $post_id );

		// If we are in the wrong post type, or we're performing just an update, we don't need to award any engagment.
		if ( ! array_key_exists( $post_type, $this->allowed_post_types ) || self::has_user_earned( $post->post_author, $post_id ) ) {
			return;
		}

		// Award the engagement.
		LLMS_Engagement_Handler::create_actions( $this->allowed_post_types[ $post_type ]['engagement_type'], $post->post_author, $post_id );

	}

	/**
	 * Wheter the user has earned the engagement.
	 *
	 * @since [version]
	 * @todo move somewhere else.
	 *
	 * @param int $user_id The student's user id.
	 * @param int $post_id The earned engagement id.
	 * @return bool
	 */
	public static function has_user_earned( $user_id, $post_id ) {
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
		);// no-cache ok.
	}

}
