<?php
/**
 * Update functions for version 5.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turn off autoload for accounting legacy options
 *
 * @since 5.0.0
 *
 * @return bool True if it needs to run again, false otherwise.
 */
function llms_update_500_legacy_options_autoload_off() {

	global $wpdb;

	$legacy_options_to_stop_autoloading = array(
		'lifterlms_registration_generate_username',
		'lifterlms_registration_password_strength',
		'lifterlms_registration_password_min_strength',
	);

	$sql = "
		UPDATE {$wpdb->options} SET autoload='no'
		WHERE option_name IN (" . implode( ', ', array_fill( 0, count( $legacy_options_to_stop_autoloading ), '%s' ) ) . ')';

	$wpdb->query(
		$wpdb->prepare(
			$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- No user input, it's safe.
			$legacy_options_to_stop_autoloading
		)
	); // db call ok; no-cache ok.

	return false;

}

/**
 * Admin welcome notice
 *
 * @since 5.0.0
 *
 * @return void
 */
function llms_update_500_add_admin_notice() {

	require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

	$notice_id        = 'v500-welcome-msg';
	$get_started_link = add_query_arg(
		array(
			'post_type' => 'llms_form',
		),
		admin_url( 'edit.php' )
	);

	$html = sprintf(
		'<strong>%1$s</strong><br><br>%2$s<br><br>%3$s',
		__( 'Welcome to LifterLMS 5.0!', 'lifterlms' ),
		__( 'This new version of LifterLMS brings you the power to build and customize your student information forms using a simple point and click interface constructed on top of the WordPress block editor. Customization like the removal of default fields, changing the text of field labels, or reordering fields within a form is all possible without any code or professional help.', 'lifterlms' ),
		sprintf(
			// Translators: %1$s = Opening anchor tag to Forms admin page; %2$s = Closing anchor tag.
			__( '%1$sGet Started%2$s', 'lifterlms' ),
			'<a class="button-primary" href="' . esc_url( $get_started_link ) . '" >',
			'</a>'
		) . ' ' .
		sprintf(
			// Translators: %1$s = Opening anchor tag to the welcome blog post on lifterlms.com; %2$s = Closing anchor tag.
			__( '%1$sRead More%2$s', 'lifterlms' ),
			'<a class="button" href="https://blog.lifterlms.com/5-0/" target="_blank" rel="noopener">',
			'</a>'
		)
	);

	LLMS_Admin_Notices::add_notice(
		$notice_id,
		$html,
		array(
			'type'             => 'success',
			'dismiss_for_days' => 0,
			'remindable'       => false,
		)
	);
	return false;
}

/**
 * Update db version to 5.0.0
 *
 * @since 5.0.0]
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function llms_update_500_update_db_version() {

	LLMS_Install::update_db_version( '5.0.0' );

}
