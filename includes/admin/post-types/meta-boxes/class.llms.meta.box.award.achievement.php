<?php
/**
 * Awarded Achievements meta box.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Award achievements meta box class.
 *
 * Generates main meta box and builds forms.
 *
 * @since [version].
 */
class LLMS_Meta_Box_Award_Achievement extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-award-achievement';
		$this->title    = __( 'Achievement Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_my_achievement',
		);
		$this->priority = 'high';

	}

	/**
	 * Builds array of metabox options.
	 *
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @since [version] Remove deprecated achievement background image meta field.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			// Achievement content textarea.
			array(
				'label'      => __( 'Achievement Content', 'lifterlms' ),
				'desc'       => __( 'Enter any information you would like to display on the achievement.', 'lifterlms' ),
				'id'         => $this->prefix . 'achievement_content',
				'type'       => 'textarea_w_tags',
				'section'    => 'achievement_meta_box',
				'class'      => 'code input-full',
				'desc_class' => 'd-all',
				'group'      => '',
				'value'      => $this->post->post_content,
				'sanitize'   => 'no_encode_quotes',
			),
		);

		return array(
			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => $fields,
			),
		);

	}

	/**
	 * Save field in the db.
	 *
	 * Expects an already sanitized value.
	 *
	 * @since [version]
	 *
	 * @param int   $post_id  The WP Post ID.
	 * @param int   $field_id The field identifier.
	 * @param mixed $val      Value to save.
	 * @return bool
	 */
	protected function save_field_db( $post_id, $field_id, $val ) {

		// Save the achievement_content editor field in the llms_my_achievement WP_Post post_content field.
		if ( $this->prefix . 'achievement_content' === $field_id && $this->post->ID === $post_id ) {
			return wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $val,
				)
			) ? true : false;
		}

		return parent::save_field_db( $post_id, $field_id, $val );

	}

}
