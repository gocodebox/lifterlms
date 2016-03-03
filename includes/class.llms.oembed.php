<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Handle custom oEmbed Providers
*
* @author codeBOX
* @project lifterLMS
*
* @since  1.4.6
*/
class LLMS_OEmbed {


	/**
	 * Constructor
	 */
	public function __construct() {

		/**
		 * Add oEmbed Provider for Wistia
		 * @since 1.4.6
		 */
		wp_oembed_add_provider( '/https?\:\/\/(.+)?(wistia\.com|wi\.st)\/.*/', 'https://fast.wistia.com/oembed', true );

	}

}
return new LLMS_OEmbed();
