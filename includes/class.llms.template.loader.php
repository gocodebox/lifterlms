<?php

/**
* Template loader class
*
* Shortcode logic
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
	 * Template Loader
	 *
	 * @param string (html) $template
	 * @return string (html)
	 */
	public function template_loader( $template ) {

		$find = array( 'lifterlms.php' );
		$file = '';

		$page_restricted = llms_page_restricted(get_the_id());

		if ( $page_restricted['is_restricted'] && (is_single() || is_page())) {
			do_action('lifterlms_content_restricted', $page_restricted['id'], $page_restricted['reason']);

			//if restriction reason is site_wide_membership then restrict all content 
			if ( $page_restricted['reason'] == 'site_wide_membership' ) {
				$template = 'single-no-access.php';
			}
			//if courses or course display template
			elseif ( is_single() && get_post_type() == 'course' ) {
				return $template;
			}
			elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'llms_shop' ) ) ) {

				$template = 'archive-course.php';
			}
			elseif ( is_single() && get_post_type() == 'llms_membership' ) {
				return $template;
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

		elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'llms_shop' ) ) ) {

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

}

new LLMS_Template_Loader();
