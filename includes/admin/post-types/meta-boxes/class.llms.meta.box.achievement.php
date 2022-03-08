<?php
/**
 * LLMS_Meta_Box_Achievement class file.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Achievements meta box class.
 *
 * Generates the main metabox for the `llms_achievement` and `llms_my_achievement` post types.
 *
 * @since 1.0.0
 */
class LLMS_Meta_Box_Achievement extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings.
	 *
	 * @since 3.0.0
	 * @since 6.0.0 Added support for the `llms_my_achievement` post type.
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-achievement';
		$this->title    = __( 'Achievement Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_achievement',
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
	 * @since 3.0.0
	 * @since 3.37.12 Allow some fields to store values with quotes.
	 * @since 6.0.0 Removed the deprecated achievement background image meta field.
	 *              Made the title field conditional based on viewed post type.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array();

		if ( 'llms_achievement' === $this->post->post_type ) {

			$fields[] = array(
				'label'    => __( 'Achievement Title', 'lifterlms' ),
				'desc'     => __( 'The name of the achievement which will be shown to users', 'lifterlms' ),
				'id'       => $this->prefix . 'achievement_title',
				'type'     => 'text',
				'class'    => 'input-full',
				'sanitize' => 'no_encode_quotes',
			);

		}

		$fields[] = array(
			'label'    => __( 'Achievement Content', 'lifterlms' ),
			'desc'     => __( 'An optional short description of the achievement which will be shown to users', 'lifterlms' ),
			'id'       => $this->prefix . 'achievement_content',
			'type'     => 'textarea_w_tags',
			'sanitize' => 'no_encode_quotes',
			'cols'     => 80,
			'rows'     => 8,
			'meta'     => $this->post->post_content,
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
	 * Stores the `achievement_content` field as `post_content` in favor of storing it in the postmeta table.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $post_id  The WP Post ID.
	 * @param int   $field_id The field identifier.
	 * @param mixed $val      Value to save.
	 * @return bool
	 */
	protected function save_field_db( $post_id, $field_id, $val ) {
		// Save to the post content field.
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
