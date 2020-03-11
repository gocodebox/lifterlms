<?php
/**
 * Handle custom oEmbed Providers
 *
 * @package LifterLMS/Classes
 *
 * @since 1.4.6
 * @version 1.4.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle custom oEmbed Providers
 *
 * @since 1.4.6
 */
class LLMS_OEmbed {

	/**
	 * Constructor
	 *
	 * @since 1.4.6
	 *
	 * @return void
	 */
	public function __construct() {

		/**
		 * Add oEmbed Provider for Wistia
		 *
		 * @since 1.4.6
		 */
		wp_oembed_add_provider( '/https?\:\/\/(.+)?(wistia\.com|wi\.st)\/.*/', 'https://fast.wistia.com/oembed', true );

	}

}

return new LLMS_OEmbed();
