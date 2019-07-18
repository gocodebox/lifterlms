<?php
/**
 * Twenty Nineteen Theme Support.
 *
 * @package  LifterLMS/Classes/ThemeSupport
 *
 * @since 3.31.0
 * @version 3.31.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Twenty Nineteen Theme Support.
 *
 * @since 3.31.0
 */
class LLMS_Twenty_Nineteen {

	/**
	 * Static Constructor.
	 *
	 * @since 3.31.0
	 *
	 * @return void
	 */
	public static function init() {

		// This theme doesn't have a sidebar.
		remove_action( 'lifterlms_sidebar', 'lifterlms_get_sidebar', 10 );

		// Handle content wrappers.
		remove_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
		remove_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );

		add_action( 'lifterlms_before_main_content', array( __CLASS__, 'output_content_wrapper' ), 10 );
		add_action( 'lifterlms_after_main_content', array( __CLASS__, 'output_content_wrapper_end' ), 10 );

	}

	/**
	 * Output Twentynineteen theme wrapper openers
	 *
	 * @since 3.31.0
	 *
	 * @return void
	 */
	public static function output_content_wrapper() {
		echo '<section id="primary" class="content-area"><main id="main" class="site-main"><div class="entry"><div class="entry-content">';
	}

	/**
	 * Output Twentynineteen theme wrapper closers
	 *
	 * @since 3.31.0
	 *
	 * @return void
	 */
	public static function output_content_wrapper_end() {
		echo '</div></div></main></section>';
	}

}

return LLMS_Twenty_Nineteen::init();
