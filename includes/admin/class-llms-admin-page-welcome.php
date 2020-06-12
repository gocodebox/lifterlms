<?php
/**
 * File Summary
 *
 * File description.
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Admin_Page_Welcome {

	public function __construct() {

		add_action( 'llms_admin_menu_init', array( $this, 'register' ) );

	}

	public function register() {

		add_submenu_page( 'lifterlms', __( 'Welcome to LifterLMS', 'lifterlms' ), __( 'Welcome', 'lifterlms' ), 'manage_lifterlms', 'llms-welcome', array( $this, 'output' ) );

	}

	protected function get_links() {

		$links = array(
			array(
				'icon'  => 'admin-site-alt3',
				'text'  => 'LifterLMS.com',
				'title' => __( 'Visit the LifterLMS homepage', 'lifterlms' ),
				'url'   => 'https://lifterlms.com',
			),
			array(
				'icon'  => 'media-document',
				'text'  => __( 'Knowledge base', 'lifterls' ),
				'title' => __( 'Documentation, tutorials, FAQs, and more', 'lifterlms' ),
				'url'   => 'https://lifterlms.com/docs',
			),
			array(
				'icon'  => 'welcome-learn-more',
				'text'  => __( 'Academy', 'lifterls' ),
				'title' => __( 'Take courses about LifterLMS, WordPress, and running an online learning platform', 'lifterlms' ),
				'url'   => 'https://lifterlms.com/academy',
			),
			array(
				'icon'  => 'editor-help',
				'text'  => __( 'Forums', 'lifterls' ),
				'title' => __( 'Get help and support with LifterLMS', 'lifterlms' ),
				'url'   => 'https://wordpress.org/support/plugin/lifterlms/',
			),
			array(
				'icon'  => 'facebook',
				'text'  => __( 'User Group', 'lifterls' ),
				'title' => __( 'Join the LifterLMS community on Facebook', 'lifterlms' ),
				'url'   => 'https://www.facebook.com/groups/lifterlmsvip/',
			),
			array(
				'icon'  => 'sos',
				'text'  => __( 'Support', 'lifterls' ),
				'title' => __( 'Get help and support with LifterLMS', 'lifterlms' ),
				'url'   => 'https://lifterlms.com/my-account/my-tickets',
			),
			array(
				'icon'  => 'hammer',
				'text'  => __( 'Find an Expert', 'lifterls' ),
				'title' => __( 'Kickstart your project with the help of a LifterLMS Expert', 'lifterlms' ),
				'url'   => 'https://lifterlms.com/store',
			),
			array(
				'icon'  => 'cart',
				'text'  => __( 'Store', 'lifterls' ),
				'title' => __( 'Enhance the core LifterLMS plugin with official add-ons and themes', 'lifterlms' ),
				'url'   => 'https://lifterlms.com/store',
			),
			array(
				'icon'  => 'admin-users',
				'text'  => __( 'My Account', 'lifterls' ),
				'title' => __( 'Visit my LifterLMS.com account dashboard', 'lifterlms' ),
				'url'   => 'https://lifterlms.com/my-account',
			),
		);

		foreach ( $links as &$link ) {
			$link['url'] = add_query_arg( array(
				'utm_source'   => 'welcome_screen',
				'utm_medium'   => 'product',
				'utm_campaign' => 'lifterlmsplugin',
				'utm_content'  => strtolower( $link['text'] ),
			), $link['url'] );
		}

		return $links;

	}

	public function output() {
		$links = $this->get_links();
		require_once 'views/welcome.php';
	}

}

return new LLMS_Admin_Page_Welcome();
