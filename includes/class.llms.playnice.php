<?php
/**
 * Make LifterLMS play nicely with other plugins & themems
 *
 * @since    3.1.3
 * @version  3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_PlayNice {

	/**
	 * Constructor
	 * @since    3.1.3
	 * @version  3.1.3
	 */
	public function __construct() {

		add_filter( 'wpseo_premium_post_redirect_slug_change', array( $this, 'wp_seo_premium_redirects' ) );

	}

	/**
	 * WP Seo Premium Redirect Manager Conflict
	 *
	 * the redirect manager bugs out and creates broken redirects from a course/membership to the homepage
	 * when an access plan is updated
	 *
	 * this prevents that
	 *
	 * @param    bool     $bool  default is always false, which means a redirect will be created
	 * @return   boolean
	 * @since    3.1.3
	 * @version  3.1.3
	 */
	public function wp_seo_premium_redirects( $bool ) {

		$screen = get_current_screen();

		if ( ! empty( $screen->post_type ) && in_array( $screen->post_type, array( 'course', 'llms_membership' ) ) ) {

			$bool = true;

		}

		return $bool;

	}

}

return new LLMS_PlayNice();
