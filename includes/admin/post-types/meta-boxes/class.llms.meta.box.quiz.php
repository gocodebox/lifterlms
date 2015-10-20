<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'LLMS_Admin_Metabox' ) ) 
{
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
* 
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Quiz extends LLMS_Admin_Metabox{

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

		$meta_fields_quiz = array(
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'type'  	=> 'text',
						'label' 	=> 'Allowed Attempts',
						'desc' 		=> 'Number of allowed attempts. Leave blank for unlimited attempts.',
						'id' 		=> self::$prefix . 'llms_allowed_attempts',						
						'section' 	=> 'quiz_meta_box',
						'class' 	=> 'code input-full',
						'desc_class'=> 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'text',
						'label'  	=> 'Passing Percentage',
						'desc'  	=> 'Enter the percent required to pass quiz. DO NOT USE % (IE: enter 50 to have a passing requirement of 50%.)',
						'id'    	=> self::$prefix . 'llms_passing_percent',						
						'section' 	=> 'quiz_meta_box',
						'class' 	=> 'code input-full',
						'desc_class'=> 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'min'		=> '0',
						'label'  	=> 'Time Limit',
						'desc'  	=> 'Enter a time limit for quiz completion in minutes. Leave empty if no time limit.',
						'id'    	=> self::$prefix . 'llms_time_limit',						
						'section' 	=> 'quiz_meta_box',
						'class' 	=> 'code input-full',
						'desc_class'=> 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> 'Randomize Answers',
						'desc'  	=> 'Select to randomize quiz answers',
						'id'    	=> self::$prefix . 'llms_random_answers',
						'section' 	=> 'quiz_meta_box',
						'class' 	=> '',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> 'Show Results',
						'desc'  	=> 'Display Last Quiz Results to User',
						'id'    	=> self::$prefix . 'llms_show_results',
						'section' 	=> 'quiz_meta_box',
						'class' 	=> '',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> 'Show Correct Answer',
						'desc'  	=> 'Display Correct Answer on Incorrect Questions',
						'id'    	=> self::$prefix . 'llms_show_correct_answer',
						'section' 	=> 'quiz_meta_box',
						'class' 	=> '',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group'		=> 'hidden',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> 'Show Description Wrong Answer',
						'desc'  	=> 'Display Picked Option Description on Wrong Questions',
						'id'    	=> self::$prefix . 'llms_show_options_description_wrong_answer',
						'section' 	=> 'quiz_meta_box',
						'class' 	=> '',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group'		=> 'hidden',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> 'Show Description Right Answer',
						'desc'  	=> 'Display Picked Option Description on Right Questions',
						'id'    	=> self::$prefix . 'llms_show_options_description_right_answer',
						'section' 	=> 'quiz_meta_box',
						'class' 	=> '',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group'		=> 'hidden',
						'value' 	=> '',
					),
				)
			),						
		);

		if(has_filter('llms_meta_fields_quiz')) {
			$meta_fields_quiz = apply_filters('llms_meta_fields_quiz', $meta_fields_quiz);
		} 
		
		return $meta_fields_quiz;
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