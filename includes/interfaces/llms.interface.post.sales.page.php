<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Post Model Sales Page Functiosn
 * @since    3.20.0
 * @version  3.20.0
 */
interface LLMS_Interface_Post_Sales_Page {

	/**
	 * Get the URL to a WP Page or Custom URL when sales page redirection is enabled
	 * @return   string
	 * @since    3.20.0
	 * @version  3.20.0
	 */
	public function get_sales_page_url();

	/**
	 * Determine if sales page rediriction is enabled
	 * @return   string
	 * @since    3.20.0
	 * @version  3.20.0
	 */
	public function has_sales_page_redirect();

}
