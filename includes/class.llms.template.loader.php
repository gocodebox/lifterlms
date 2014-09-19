<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Classes
 * @version     0.1
 */

class LLMS_Template_Loader {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
	}

	/**
	 * TODO: find the correct template to load for taxonomies and post types. 
	 *
	 * @param mixed $template
	 * @return void
	 */
	public function template_loader( $template ) {
		$file = '';

		if ( is_single() && get_post_type() == 'course' ) {

			$template = LLMS()->plugin_path() . '/templates/single-course.php';
		}

		if ( is_single() && get_post_type() == 'lesson' ) {

			$template = LLMS()->plugin_path() . '/templates/single-lesson.php';
		}

		return $template;
	}

}

new LLMS_Template_Loader();