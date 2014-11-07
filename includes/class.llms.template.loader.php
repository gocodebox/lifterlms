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


		$page_restricted = llms_page_restricted(get_the_id());

		if ( is_single() && $page_restricted['is_restricted'] ) {

				do_action('lifterlms_content_restricted', $page_restricted['id'], $page_restricted['reason']);
				//$template = 'single-no-access.php';
				return $template;
		
		}

		elseif ( is_single() && get_post_type() == 'llms_membership' ) {

				return $template;

		}

		elseif ( is_single() && get_post_type() == 'course' ) {

				return $template;
				//$template = 'single-course.php';


		}

		elseif ( is_single() && get_post_type() == 'lesson' ) {


				return $template;
			

		}

		elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'shop' ) ) ) {

			$template = 'archive-course.php';

		} 

		elseif ( is_post_type_archive( 'llms_membership' ) || is_page( llms_get_page_id( 'memberships' ) ) ) {

			$template = 'archive-membership.php';

		} 

		elseif ( is_single() && get_post_type() == 'llms_certificate' ) {

			$template = 'single-certificate.php';
			
		}

		elseif ( is_single() && get_post_type() == 'llms_my_certificate' ) {

			$template = 'single-certificate.php';
			
		}
			
		else {

			return $template;

		}

		$template_path = ($this->has_theme_override($template)) ? get_stylesheet_directory() . '/lifterlms/' : LLMS()->plugin_path() . '/templates/';

		return $template_path . $template;

	}


	/**
	 * Check to see if the installed theme has an override template
	 *
	 * @param  string  $template  slug to the template file (no .php)
	 * @return boolean
	 */
	private function has_theme_override($template='') {

		return file_exists(get_stylesheet_directory() . '/lifterlms/' .$template);

	}

}

new LLMS_Template_Loader();
