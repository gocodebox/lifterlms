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
LLMS_log('template loader called');

		$page_restricted = llms_page_restricted(get_the_id());

		if ( is_single() && $page_restricted['is_restricted'] ) {

				do_action('lifterlms_content_restricted', $page_restricted['id'], $page_restricted['reason']);
				$template = 'single-no-access.php';
		
		}

		elseif ( is_single() && get_post_type() == 'llms_membership' ) {

				$template = 'single-membership.php';

		}

		elseif ( is_single() && get_post_type() == 'course' ) {

			//if ( $this->check_user_permissions( 'course' ) ) {

				$template = 'single-course.php';
			
			//}
		//	else {
//
			//	$template = 'single-no-access.php';
			
			//}

		}

		elseif ( is_single() && get_post_type() == 'lesson' ) {

			//if ( lesson_restricted(get_the_id()) ) {

			//	$template = 'single-no-access.php';
				
			//}
		//	elseif ( $this->check_user_permissions( 'lesson' ) ) {

				$template = 'single-lesson.php';
			
		//	}
			//else {

				//$template = 'single-no-access.php';
			
			//}

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

			// not an llms template
			return $template;

		}

		$template_path = ($this->has_theme_override($template)) ? get_stylesheet_directory() . '/llms/' : LLMS()->plugin_path() . '/templates/';

		return $template_path . $template;

	}

	// public function check_user_permissions ( $page ) {
	// 	global $post;

	// 	if ( ! wp_get_current_user() ) {
	// 		return;
	// 	}

	// 	$allow_access = true;

	// 	switch( $page ) {

	// 		case ('course') :

	// 			if ( outstanding_prerequisite_exists(get_current_user_id(),  $post->ID ) )  {

	// 				$allow_access = false;
	// 			}
	// 			break;
	// 		case ('lesson') :

	// 			if ( outstanding_prerequisite_exists(get_current_user_id(), $post->ID ) )  {
	// 				$allow_access = false;
	// 			}
	// 			elseif ( lesson_start_date_in_future(get_current_user_id(), $post->ID ) ) {
	// 				$allow_access = false;
	// 			}
	// 			break;
	// 		default:
	// 			$allow_access = false;
	// 			break;
	// 	}

	// return $allow_access;
	// }

	

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
