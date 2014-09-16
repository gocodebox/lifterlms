<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	lifterLMS/Admin
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'LLMS_Admin_Assets' ) ) :

/**
 * LLMS_Admin_Assets Class
 */
class LLMS_Admin_Assets {
	public static $suffix = ''; //'.min';

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Return an array of all possible page ids for lifterLMS
	 */
	public function get_screen_ids() {
		$screen_id = sanitize_title( __( 'lifterLMS', 'lifterlms' ) );

	    return apply_filters( 'lifterlms_screen_ids', array(
	    	$screen_id . '_page_llms-settings',
	    	'course',
	    	'edit-course',
	    	'edit-course_cat',
	    ));
	}

	/**
	 * Enqueue styles
	 */
	public function admin_styles() {

			wp_enqueue_style( 'admin-styles', plugins_url( '/assets/css/admin' . LLMS_Admin_Assets::$suffix . '.css', LLMS_PLUGIN_FILE ) );
			wp_enqueue_style( 'chosen-styles', plugins_url( '/assets/chosen/chosen' . LLMS_Admin_Assets::$suffix . '.css', LLMS_PLUGIN_FILE ) );
	}

	/**
	 * Enqueue scripts
	 */
	public function admin_scripts() {
		$screen = get_current_screen();

		if ( in_array( $screen->id, LLMS_Admin_Assets::get_screen_ids() ) ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_media();

			wp_enqueue_script( 'chosen-jquery', plugins_url( 'assets/chosen/chosen.jquery' . LLMS_Admin_Assets::$suffix . '.js', LLMS_PLUGIN_FILE ), array('jquery'), '', TRUE);
			wp_enqueue_script( 'llms-ajax', plugins_url(  '/assets/js/llms-ajax' . LLMS_Admin_Assets::$suffix . '.js', LLMS_PLUGIN_FILE ), array('jquery'), '', TRUE);
			
			wp_enqueue_script( 'llms-metabox-data', plugins_url(  '/assets/js/llms-metabox-data' . LLMS_Admin_Assets::$suffix . '.js', LLMS_PLUGIN_FILE ), array('jquery'), '', TRUE);
			wp_enqueue_script( 'llms-metabox-fields', plugins_url(  '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$suffix . '.js', LLMS_PLUGIN_FILE ), array('jquery'), '', TRUE);
			wp_enqueue_script( 'llms-metabox-syllabus', plugins_url(  '/assets/js/llms-metabox-syllabus' . LLMS_Admin_Assets::$suffix . '.js', LLMS_PLUGIN_FILE ), array('jquery'), '', TRUE);

		}
	}

}

endif;

return new LLMS_Admin_Assets;