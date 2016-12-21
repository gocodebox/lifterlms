<?php
/**
 * Make LifterLMS play nicely with other plugins & themems
 *
 * @since    3.1.3
 * @version  3.2.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_PlayNice {

	/**
	 * Constructor
	 * @since    3.1.3
	 * @version  3.2.2
	 */
	public function __construct() {

		// Yoast Premium redirect manager slug change filter
		add_filter( 'wpseo_premium_post_redirect_slug_change', array( $this, 'wp_seo_premium_redirects' ) );

		// optimize press live editor initialization
		add_action( 'op_liveeditor_init' , array( $this, 'wp_optimizepress_live_editor' ) );

	}

	/**
	 * OptimizePress LiveEditor fix
	 *
	 * The live editor for OptimizePress does not work because it is trying to load a frontend environment
	 * in the admin area and needs access lifterlms frontend files
	 *
	 * This function loads all frontend files when the optimizepress live editor is initalized
	 *
	 * @return void
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	public function wp_optimizepress_live_editor() {

		// These files are necessary to get optimizepress ajax to play nicely in the liveeditor
		include_once( 'class.llms.ajax.php' );
		include_once( 'class.llms.ajax.handler.php' );

		// These files are all necesarry to get the liveeditor to open
		include_once( 'llms.template.functions.php' );
		include_once 'class.llms.https.php';

		include_once( 'class.llms.template.loader.php' );
		include_once( 'class.llms.frontend.assets.php' );
		include_once( 'class.llms.frontend.forms.php' );
		include_once( 'class.llms.frontend.password.php' );
		include_once( 'class.llms.person.php' );
		include_once( 'class.llms.shortcodes.php' );

		include_once( 'shortcodes/class.llms.shortcode.my.account.php' );
		include_once( 'shortcodes/class.llms.shortcode.checkout.php' );

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
