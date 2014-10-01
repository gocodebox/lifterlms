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

			$template = 'single-course.php';

		}

		elseif ( is_single() && get_post_type() == 'lesson' ) {

			$template = 'single-lesson.php';

		}

		elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'shop' ) ) ) {

			$template = 'archive-course.php';

		} 

		else {

			// not an llms template
			return $template;

		}

		$template_path = ($this->has_theme_override($template)) ? get_stylesheet_directory() . '/llms/' : LLMS()->plugin_path() . '/templates/';

		return $template_path . $template;

	}


	/**
	 * Check to see if the installed theme has an override template
	 *
	 * @param  string  $template  slug to the template file (no .php)
	 * @return boolean
	 */
	private function has_theme_override($template='') {

		return file_exists(get_stylesheet_directory() . '/llms/' .$template);

	}

}

new LLMS_Template_Loader();
