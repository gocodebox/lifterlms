<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! defined( 'LLMS_Admin_Metabox' ) ) {
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Achievement extends LLMS_Admin_Metabox{

	public static $prefix = '_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post;

		$meta_fields_achievement = array(
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'label' 	=> 'Achievement Title',
						'desc' 		=> 'Enter a title for your achievement. IE: Achievement of Completion',
						'id' 		=> self::$prefix . 'llms_achievement_title',
						'type'  	=> 'text',
						'section' 	=> 'achievement_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					//Achievment content textarea
					array(
						'label' 	=> 'Achievement Content',
						'desc' 		=> 'Enter any information you would like to display on the achievement.',
						'id' 		=> self::$prefix . 'llms_achievement_content',
						'type'  	=> 'textarea_w_tags',
						'section' 	=> 'achievement_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					//Achievement background image
					array(
						'label'  	=> 'Background Image',
						'desc'  	=> 'Select an Image to use for the achievement.',
						'id'    	=> self::$prefix . 'llms_achievement_image',
						'type'  	=> 'image',
						'section' 	=> 'achievement_meta_box',
						'class' 	=> 'achievement',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_achievement' )) {
			//Add Fields to the achievement Meta Box
			$meta_fields_achievement = apply_filters( 'llms_meta_fields_achievement', $meta_fields_achievement );
		}

		return $meta_fields_achievement;
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

	}

}
