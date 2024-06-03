<?php
/**
 * Update functions for version 7.5.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 7.5.0
 * @version [versoin]]
 */

namespace LLMS\Updates\Version_7_5_0;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since 7.5.0
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '7.5.0';
}

/**
 * Disable favorites feature for old users.
 *
 * @since 7.5.0
 *
 * @return void
 */
function favorites_feature_bc() {
	update_option( 'lifterlms_favorites', 'no' );
}

/**
 * Update db version to 7.5.0
 *
 * @since 7.5.0
 *
 * @return false.
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
