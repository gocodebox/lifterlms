<?php
/**
 * Update functions for version 6.10.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 6.10.0
 * @version 6.10.0
 */

namespace LLMS\Updates\Version_6_10_0;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since 6.10.0
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '6.10.0';
}

/**
 * Migrates spanish user's provinces to the correct ones.
 *
 * @since 6.10.0
 *
 * @return bool Returns `true` if more records need to be updated and `false` upon completion.
 */
function migrate_spanish_users() {

	$per_page = \llms_update_util_get_items_per_page();

	$states_migration_map = array(
		'AS' => 'O', // Asturias.
		'CB' => 'S', // Cantabria.
		'RI' => 'LO', // 'La Rioja'.
		'MD' => 'M', // Madrid.
		'MC' => 'MU', // 'Murcia'.
		'NC' => 'NA', // 'Navarra'.
		'VC' => 'V', // 'Valencia'.
		// No map.
		'AN' => '',
		'AR' => '',
		'PV' => '',
		'CN' => '',
		'CL' => '',
		'CM' => '',
		'CT' => '',
		'EX' => '',
		'GA' => '',
		'SA' => '',
	);

	$query = new \WP_User_Query(
		array(
			'orderby'        => array(
				'ID' => 'ASC',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'llms_billing_country',
					'value'   => 'ES',
					'compare' => '=',
				),
				array(
					'key'     => 'llms_billing_state',
					'value'   => array_keys( $states_migration_map ),
					'compare' => 'IN',
				),
			),
			'posts_per_page' => $per_page,
			'no_found_rows'  => true, // We don't care about found rows since we'll run the query as many times as needed anyway.
		)
	);

	$users = $query->get_results();
	if ( $users ) {
		foreach ( $users as $user ) {
			$new_state = $states_migration_map[ \get_user_meta( $user->ID, 'llms_billing_state', true ) ] ?? '';
			\update_user_meta( $user->ID, 'llms_billing_state', $new_state );
		}
	}

	// If there was `$per_page` results assume there's another page and run again, otherwise we're done.
	return ( count( $users ) === $per_page );

}

/**
 * Update db version to 6.10.0.
 *
 * @since 6.10.0
 *
 * @return false.
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
