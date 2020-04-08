<?php
/**
 * LifterLMS Registration Shortcode
 *
 * [lifterlms_registration]
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 3.0.0
 * @version 3.4.3
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
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 3.4.3
	 *
	 * @return string
	 */
	protected function get_output() {

		$this->enqueue_script( 'password-strength-meter' );
		LLMS_Frontend_Assets::enqueue_inline_pw_script();

		ob_start();
		include llms_get_template_part_contents( 'global/form', 'registration' );
		return ob_get_clean();

	}

}

return LLMS_Shortcode_Registration::instance();
