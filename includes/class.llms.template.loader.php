<?php

/**
* Template loader class
*
* Shortcode logic
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Template_Loader {

	/**
	* Constructor
	*
	* Hooks template loader to template include
	*/
	public function __construct() {

		add_filter( 'template_include', array( $this, 'template_loader' ) );

	}

	/**
	 * Get Lesson Length
	 *
	 * @param string (html) $template
	 * @return string (html)
	 */
	public function template_loader( $template ) {

		$find = array( 'lifterlms.php' );
		$file = '';

		if ( is_single() && get_post_type() == 'course' ) {

			$template = LLMS()->plugin_path() . '/templates/single-course.php';

		}

		if ( is_single() && get_post_type() == 'lesson' ) {

			$template = LLMS()->plugin_path() . '/templates/single-lesson.php';

		} 

		elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'shop' ) ) ) {

			$template = LLMS()->plugin_path() . '/templates/archive-course.php';

		}

		return $template;
	}

}

new LLMS_Template_Loader();
