<?php
/**
* Notice Functions
* Functions for managing front end notices (alert messages)
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Stores notice in llms_notices session
 * @param  string $message     [The notice message]
 * @param  string $notice_type [notice type]
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
function llms_add_notice( $message, $notice_type = 'success' ) {

	$notices = LLMS()->session->get( 'llms_notices', array() );

	if ( 'success' === $notice_type ) {
		$message = apply_filters( 'lifterlms_add_message', $message );
	}

	$notices[ $notice_type ][] = apply_filters( 'lifterlms_add_' . $notice_type, $message );

	LLMS()->session->set( 'llms_notices', $notices );

}

/**
 * Clears all notices from session
 * @return void
 * @since   1.0.0
 * @version 3.12.0
 */
function llms_clear_notices() {
	LLMS()->session->set( 'llms_notices', array() );
}

/**
 * Retrieve an array of notice types
 * @return   array
 * @since    1.0.0
 * @version  1.0.0
 */
function llms_get_notice_types() {
	return apply_filters( 'lifterlms_notice_types', array( 'debug', 'error', 'notice', 'success' ) );
}

/**
 * Gets messages and errors which are stored in the session, then clears them.
 * @return   string
 * @since    3.0.0
 * @version  3.12.0
 */
function llms_get_notices() {

	$all_notices  = apply_filters( 'lifterlms_print_notices', LLMS()->session->get( 'llms_notices', array() ) );
	$notice_types = llms_get_notice_types();

	ob_start();

	foreach ( $notice_types as $notice_type ) {
		if ( llms_notice_count( $notice_type ) > 0 ) {
			llms_get_template( "notices/{$notice_type}.php", array(
				'messages' => $all_notices[ $notice_type ],
			) );
		}
	}

	add_action( 'shutdown', 'llms_clear_notices', 1 ); // prior to shutdown functions executed by session manager

	return ob_get_clean();

}


/**
 * Returns a count of all current notices by type.
 * @param  string   $notice_type  Type of notice passed. IE: error, success, warning
 * @return int
 * @since   1.0.0
 * @version 1.0.0
 */
function llms_notice_count( $notice_type = '' ) {

	$notice_count = 0;

	$all_notices  = LLMS()->session->get( 'llms_notices', array() );

	if ( isset( $all_notices[ $notice_type ] ) ) {

		$notice_count = absint( sizeof( $all_notices[ $notice_type ] ) );

	} elseif ( empty( $notice_type ) ) {

		foreach ( $all_notices as $notices ) {
			$notice_count += absint( sizeof( $all_notices ) );
		}
	}

	return $notice_count;
}

/**
 * Prints a single notice
 * @param   string $message     [The notice message]
 * @param   string $notice_type [notice type]
 * @return  void
 * @since   1.0.0
 * @version 1.0.0
 */
function llms_print_notice( $message, $notice_type = 'success' ) {

	if ( 'success' === $notice_type ) {
		$message = apply_filters( 'lifterlms_add_message', $message );
	}

	llms_get_template( "notices/{$notice_type}.php", array(
		'messages' => array( apply_filters( 'lifterlms_add_' . $notice_type, $message ) ),
	) );
}

/**
 * Prints all notices
 * @return  void
 * @since   1.0.0
 * @version 3.14.7
 */
function llms_print_notices() {
	echo llms_get_notices();
	llms_clear_notices();
}
