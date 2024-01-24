<?php
/**
 * LLMS_Admin_Plugins class
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 7.5.1
 * @version 7.5.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Modifications to the plugins page.
 *
 * @since 7.5.1
 */
class LLMS_Admin_Plugins {

	/**
	 * Constructor
	 *
	 * @since 7.5.1
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'plugin_action_links_' . plugin_basename( LLMS_PLUGIN_DIR . '/lifterlms.php' ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Add links to the plugins page.
	 */
	public function plugin_action_links( $links ) {
		$new_links = array(
			'dashboard' => '<a href="' . esc_url( admin_url( 'admin.php?page=llms-dashboard' ) ) . '">' . __( 'Dashboard', 'lifterlms' ) . '</a>',
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=llms-settings' ) ) . '">' . __( 'Settings', 'lifterlms' ) . '</a>',
		);

		$links = array_merge( $new_links, $links );
		return $links;
	}

	/**
	 * Add links to plugin description.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( LLMS_PLUGIN_DIR . '/lifterlms.php' ) === $file ) {
			$row_meta = array(
				'docs'      => '<a href="' . esc_url( 'https://lifterlms.com/docs/?utm_source=LifterLMS%20Plugin&utm_medium=Plugins%20Screen&utm_campaign=Plugin%20to%20Sale&utm_content=Documentation' ) . '" aria-label="' . esc_attr__( 'Docs', 'lifterlms' ) . '" target="_blank">' . esc_html__( 'Documentation', 'lifterlms' ) . '</a>',
				'support'   => '<a href="' . esc_url( 'https://lifterlms.com/my-account/my-tickets/?utm_source=LifterLMS%20Plugin&utm_medium=Plugins%20Screen&utm_campaign=Plugin%20to%20Sale&utm_content=Support' ) . '" aria-label="' . esc_attr__( 'Support', 'lifterlms' ) . '" target="_blank">' . esc_html__( 'Support', 'lifterlms' ) . '</a>',
				'pricing'   => '<a href="' . esc_url( 'https://lifterlms.com/pricing/?utm_source=LifterLMS%20Plugin&utm_medium=Plugins%20Screen&utm_campaign=Plugin%20to%20Sale&utm_content=Premium%20Plans' ) . '" aria-label="' . esc_attr__( 'Premium Plans', 'lifterlms' ) . '" target="_blank">' . esc_html__( 'Premium Plans', 'lifterlms' ) . '</a>',
			);

			$links = array_merge( $links, $row_meta );
		}

		return $links;
	}
}

return new LLMS_Admin_Plugins();
