<?php
/**
 * LifterLMS Registration Shortcode
 *
 * [lifterlms_registration]
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 3.0.0
 * @version 5.0.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Registration
 *
 * @since 3.0.0
 * @since 3.4.3 Migrated to utilize `LLMS_Shortcode` abstract.
 */
class LLMS_Shortcode_Registration extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_registration';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * The variables `$atts` & `$content` are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter.
	 *
	 * @since 3.4.3
	 * @since 5.0.0 Remove password strength enqueue script.
	 * @since 5.0.2 Added select enqueue script and inline script for address info.
	 *
	 * @return string
	 */
	protected function get_output() {
		/**
		 * Enqueue select2 scripts and styles.
		 */
		llms()->assets->enqueue_script( 'llms-select2' );
		llms()->assets->enqueue_style( 'llms-select2-styles' );

		if ( ! wp_script_is( 'llms' ) ) {
			// If the main LifterLMS script isn't enqueued, adding inline script below will fail.
			llms()->assets->enqueue_script( 'llms' );
		}

		wp_add_inline_script(
			'llms',
			"window.llms.address_info = '" . wp_json_encode( llms_get_countries_address_info() ) . "';"
		);

		ob_start();
		include llms_get_template_part_contents( 'global/form', 'registration' );
		return ob_get_clean();
	}
}

return LLMS_Shortcode_Registration::instance();
