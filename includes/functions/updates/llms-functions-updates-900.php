<?php
/**
 * Update functions for version [version]
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 9.0.0
 */

namespace LLMS\Updates\Version_9_0_0;

defined( 'ABSPATH' ) || exit;

function _get_db_version() {
	return '9.0.0';
}

/**
 * Shows an admin notice.
 *
 * @since 9.0.0
 *
 * @return boolean
 */
function show_notice() {

	$notice_id = sprintf( 'v%s-msg', str_replace( array( '.', '-' ), '', _get_db_version() ) );

	$html = sprintf(
		'<strong>%1$s</strong><br><br>%2$s<br><br>%3$s',
		__( 'New Features Available', 'lifterlms' ),
		// Translators: %1$s = Opening anchor tag to the security settings tab; %2$s = Closing anchor tag.
		sprintf(
			__( 'We\'ve added spam and security features to protect your website inside the core plugin. You can review the available features on the new %1$sSecurity settings tab%2$s.', 'lifterlms' ),
			'<a href="' . admin_url( 'admin.php?page=llms-settings&tab=security' ) . '">',
			'</a>'
		),
		sprintf(
			// Translators: %1$s = Opening anchor tag to the blog post on lifterlms.com; %2$s = Closing anchor tag.
			__( '%1$sRead More%2$s', 'lifterlms' ),
			'<a class="button" href="https://lifterlms.com/blog/new-website-spam-and-security-features/?utm_source=notice&utm_medium=product&utm_campaign=lifterlmsplugin&utm_content=900-notice" target="_blank" rel="noopener">',
			'</a>'
		)
	);

	\LLMS_Admin_Notices::add_notice(
		$notice_id,
		$html,
		array(
			'type'             => 'info',
			'dismiss_for_days' => 0,
			'remindable'       => false,
		)
	);
	return false;
}

/**
 * Update db version.
 *
 * @since 9.0.0
 *
 * @return false.
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
