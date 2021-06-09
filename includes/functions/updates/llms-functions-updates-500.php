<?php
/**
 * Update functions for version 5.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turn off autoload for accounting legacy options
 *
 * @since [version]
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
			$sql,
			$legacy_options_to_stop_autoloading
		)
	); // db call ok; no-cache ok.

	set_transient( 'llms_update_500_autoload_off_legacy_options', 'complete', DAY_IN_SECONDS );
	return false;

}

/**
 * Admin welcome notice
 *
 * @since [version]
 *
 * @return void
 */
function llms_update_500_add_admin_notice() {

	$html = sprintf(
		'<strong>%1$s</strong><br><br>%2$s<br><br>%3$s',
		__( 'Welcome to LifterLMS 5.0!', 'lifterlms' ),
		__( 'This new version of LifterLMS brings you the power to build and customize your student information forms using a simple point and click interface constructed on top of the WordPress block editor. Customization like the removal of default fields, changing the text of field labels, or reordering fields within a form is all possible without any code or professional help.', 'lifterlms' ),
		sprintf(
			// Translators: %1$s = Link to the welcome blog post on lifterlms.com, %2$s = Link to the documentation on lifterlms.com, %3$s Link to the Forms admin page.
			__( '%1$s, %2$s or %3$s', 'lifterlms' ),
			sprintf(
				// Translators: %1$s = Opening anchor tag to the welcome blog post on lifterlms.com; %2$s = Closing anchor tag.
				__( '%1$sRead More%2$s', 'lifterlms' ),
				'<a href="" target="_blank" rel="noopener">',
				'</a>'
			),
			sprintf(
				// Translators: %1$s = Link to the documentation on lifterlms.com; %2$s = Closing anchor tag.
				__( '%1$sRead the docs%2$s', 'lifterlms' ),
				'<a href="" target="_blank" rel="noopener">',
				'</a>'
			),
			sprintf(
				// Translators: %1$s = Opening anchor tag to Forms admin page; %2$s = Closing anchor tag.
				__( '%1$sGet Started%2$s', 'lifterlms' ),
				'<a href="' . add_query_arg(
					array(
						'post_type' => 'llms_form',
					),
					admin_url( 'edit.php' )
				) . '" >',
				'</a>'
			)
		)
	);

	LLMS_Admin_Notices::add_notice(
		basename( __FILE__, '.php' ),
		$html,
		array(
			'type'             => 'success',
			'dismiss_for_days' => 730, // @TODO: there should be a "forever" setting here.
			'remindable'       => false,
		)
	);
	return false;
}

/**
 * Update db version to 5.0.0
 *
 * @since [version]]
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function llms_update_500_update_db_version() {

	if ( 'complete' !== get_transient( 'llms_update_500_autoload_off_legacy_options' ) ) {
		// Needs to run again.
		return true;
	}

	LLMS_Install::update_db_version( '5.0.0' );
	// Show the notice when update done.
	llms_update_500_add_admin_notice();
}
