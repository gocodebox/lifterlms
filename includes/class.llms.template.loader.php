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

		if ( $page_restricted['is_restricted'] ) {
			do_action('lifterlms_content_restricted', $page_restricted['id'], $page_restricted['reason']);

			//if restriction reason is site_wide_membership then restrict all content 
			if ( $page_restricted['reason'] == 'site_wide_membership' ) {
				$template = 'single-no-access.php';
			}
			//if courses or course display template
			elseif ( is_single() && get_post_type() == 'course' ) {
				return $template;
			}
			elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'shop' ) ) ) {
				$template = 'archive-course.php';
			}
			//else restrict access
			else {
				$template = 'single-no-access.php';
			}
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

			$template = 'archive-llms_membership.php';

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

		// check for an override file
		$override = llms_get_template_override($template);
		$template_path = ($override) ? $override : LLMS()->plugin_path() . '/templates/';

		return $template_path . $template;

	}


	// /**
	//  * Check to see if the installed theme has an override template and return the path to the template directory if found
	//  *
	//  * @param  string  $template  slug to the template file (no .php)
	//  * @return string / boolean
	//  */
	// private function has_theme_override($template='') {

	// 	*
	// 	 * Allow themes and plugins to determine which folders to look in for theme overrides
		 
	// 	$dirs = apply_filters( 'lifterlms_theme_override_directories', array( 
	// 		get_stylesheet_directory() . '/lifterlms',
	// 		get_template_directory() . '/lifterlms'
	// 	) );


	// 	foreach( $dirs as $dir ) {

	// 		$path = $dir . '/';

	// 		if( file_exists($path . $template) ) {
	// 			return $path;
	// 		}

	// 	}

	// 	return false;

	// }

}

new LLMS_Template_Loader();
