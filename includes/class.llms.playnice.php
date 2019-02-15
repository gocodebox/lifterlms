<?php
/**
 * Make LifterLMS play nicely with other plugins, themes, & webhosts
 *
 * @package  LifterLMS/Classes
 * @since    3.1.3
 * @version  3.25.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_PlayNice class.
 */
class LLMS_PlayNice {

	/**
	 * Constructor
	 *
	 * @since    3.1.3
	 * @version  3.25.1
	 */
	public function __construct() {

		// Yoast Premium redirect manager slug change filter
		add_filter( 'wpseo_premium_post_redirect_slug_change', array( $this, 'wp_seo_premium_redirects' ), 10, 2 );

		// optimize press live editor initialization
		add_action( 'op_liveeditor_init' , array( $this, 'wp_optimizepress_live_editor' ) );

		// wpe heartbeat fix
		add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'wpe_heartbeat_allowed_pages' ) );

	}

	/**
	 * OptimizePress LiveEditor fix
	 *
	 * The live editor for OptimizePress does not work because it is trying to load a frontend environment
	 * in the admin area and needs access lifterlms frontend files.
	 *
	 * This function loads all frontend files when the optimizepress live editor is initalized.
	 *
	 * @return   void
	 * @since    3.2.2
	 * @version  3.19.6
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
		include_once( 'forms/frontend/class.llms.frontend.forms.php' );
		include_once( 'forms/frontend/class.llms.frontend.password.php' );
		include_once( 'class.llms.person.php' );
		include_once( 'shortcodes/class.llms.shortcodes.php' );

		include_once( 'shortcodes/class.llms.shortcode.my.account.php' );
		include_once( 'shortcodes/class.llms.shortcode.checkout.php' );

	}

	/**
	 * WP Seo Premium Redirect Manager Conflict
	 *
	 * The redirect manager bugs out and creates broken redirects from a course/membership to the homepage
	 * when an access plan is updated. This prevents that.
	 *
	 * @param    bool     $bool    default is always false, which means a redirect will be created.
	 * @param    integer  $post_id the post id of the post being saved.
	 * @return   boolean
	 * @since    3.1.3
	 * @version  3.25.1
	 */
	public function wp_seo_premium_redirects( $bool, $post_id = null ) {

		if ( ! empty( $post_id ) ) {
			$post_type = get_post_type( $post_id );
		}

		if ( empty( $post_type ) ) {
			$screen    = get_current_screen();
			$post_type = $screen->post_type;
		}

		if ( empty( $post_type ) ) {
			return $bool;
		}

		return in_array( $post_type, array( 'course', 'llms_membership' ), true );

	}

	/**
	 * WPE blocks the WordPress Heartbeat script from being loaded
	 * Event when it's explicitly defined as a dependency
	 * @param    array    $pages    list of pages that the heartbeat is allowed to load on
	 * @return   array
	 * @since    3.16.4
	 * @version  3.16.4
	 */
	public function wpe_heartbeat_allowed_pages( $pages ) {

		if ( is_admin() && isset( $_GET['page'] ) && 'llms-course-builder' === $_GET['page'] ) {

			$pages[] = 'admin.php';

		}

		return $pages;

	}

}

return new LLMS_PlayNice();
