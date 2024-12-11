<?php
/**
 * Update functions for version [version]
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 7.8.5
 * @version 7.8.5
 */

namespace LLMS\Updates\Version_7_8_5;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since 7.8.5
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '7.8.5';
}

/**
 * Verify and delete the password_confirm usermeta.
 *
 * @since 7.8.5
 *
 * @return false
 */
function maybe_remove_pwc() {
	global $wpdb;
	$found_pwc_meta = $wpdb->get_results(
		"SELECT *
		 FROM {$wpdb->usermeta}
		 WHERE meta_key = 'password_confirm'"
	);

	if ( $found_pwc_meta ) {
		update_option( 'llms_pwc_notice', 'yes' );

		$wpdb->query(
			"DELETE
		 FROM {$wpdb->usermeta}
		 WHERE meta_key = 'password_confirm'"
		);

		show_notice();
	}

	return false;
}

/**
 * Shows an admin notice.
 *
 * @since 7.8.5
 *
 * @return boolean
 */
function show_notice() {

	$notice_id = sprintf( 'v%s-msg', str_replace( array( '.', '-' ), '', _get_db_version() ) );

	$html = sprintf(
		'<strong>%1$s</strong><br><br>%2$s<br><br>%3$s',
		__( 'Security Notice', 'lifterlms' ),
		sprintf(
			// Translators: %1$s = Opening anchor tag to the welcome blog post on lifterlms.com; %2$s = Closing anchor tag.
			__( 'We\'ve detected that your site has been affected by a security issue fixed in the v7.8.5 update to LifterLMS. Further action is required. Your site may have been saving user passwords to the user meta table in plaintext. %1$sClick here to learn more%2$s.', 'lifterlms' ),
			'<a href="https://lifterlms.com/blog/security-release-password-block/?utm_source=notice&utm_medium=product&utm_campaign=lifterlmsplugin&utm_content=785-notice" target="_blank" rel="noopener">',
			'</a>'
		),
		sprintf(
			// Translators: %1$s = Opening anchor tag to the welcome blog post on lifterlms.com; %2$s = Closing anchor tag.
			__( '%1$sRead More%2$s', 'lifterlms' ),
			'<a class="button" href="https://lifterlms.com/blog/security-release-password-block/?utm_source=notice&utm_medium=product&utm_campaign=lifterlmsplugin&utm_content=785-notice" target="_blank" rel="noopener">',
			'</a>'
		)
	);

	\LLMS_Admin_Notices::add_notice(
		$notice_id,
		$html,
		array(
			'type'             => 'error',
			'dismiss_for_days' => 0,
			'remindable'       => false,
		)
	);
	return false;
}

/**
 * Update db version to [version].
 *
 * @since 7.8.5
 *
 * @return false.
 */
function update_db_version() {
	global $wpdb;

	$found_pwc_meta = $wpdb->get_results(
		"SELECT *
		 FROM {$wpdb->usermeta}
		 WHERE meta_key = 'password_confirm'"
	);

	if ( $found_pwc_meta ) {
		// Don't update the db version yet.
		return;
	}

	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
