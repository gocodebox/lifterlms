<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Post Model Sales Page Functiosn
 * @since    [version]
 * @version  [version]
 */
interface LLMS_Interface_Post_Sales_Page {

	/**
	 * Get the URL to a WP Page or Custom URL when sales page redirection is enabled
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_sales_page_url();

	/**
	 * Determine if sales page rediriction is enabled
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function has_sales_page_redirect();

}
