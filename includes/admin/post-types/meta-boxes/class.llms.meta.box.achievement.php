<?php
/**
 * Achievements meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Achievements meta box class
 *
 * Generates main meta box and builds forms.
 *
 * @since 1.0.0
 * @since 3.0.0 Unknown.
 * @since 3.37.12 Allow some fields to store values with quotes.
 */
class LLMS_Meta_Box_Achievement extends LLMS_Admin_Metabox {

	use LLMS_Trait_Earned_Engagement_Meta_Box;

	/**
	 * Configure the metabox settings.
	 *
	 * @since 3.0.0
	 * @since [version] Show metabox in `llms_my_achievement` post type as well.
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
	 * @since [version] Handle specific fields for earned engaegments post types.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			array(
				'label'      => __( 'Achievement Title', 'lifterlms' ),
				'desc'       => __( 'Enter a title for your achievement. IE: Achievement of Completion', 'lifterlms' ),
				'id'         => $this->prefix . 'achievement_title',
				'type'       => 'text',
				'section'    => 'achievement_meta_box',
				'class'      => 'code input-full',
				'desc_class' => 'd-all',
				'group'      => '',
				'value'      => '',
				'sanitize'   => 'no_encode_quotes',
			),
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
				'value'      => '',
				'sanitize'   => 'no_encode_quotes',
			),
			// Achievement background image.
			array(
				'label'      => __( 'Background Image', 'lifterlms' ),
				'desc'       => __( 'Select an Image to use for the achievement.', 'lifterlms' ),
				'id'         => $this->prefix . 'achievement_image',
				'type'       => 'image',
				'section'    => 'achievement_meta_box',
				'class'      => 'achievement',
				'desc_class' => 'd-all',
				'group'      => '',
				'value'      => '',
			),
		);

		$fields = $this->add_earned_engagement_fields( $fields );

		return array(
			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => $fields,
			),
		);

	}

}
