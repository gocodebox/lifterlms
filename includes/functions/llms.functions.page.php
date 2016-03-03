<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
* Page functions
*
* Functions used for managing endpoint urls
*
* @author codeBOX
* @project lifterLMS
*/


/**
 * Returns the url to the lost password endpoint url
 *
 * @param string $url
 *
 * @return string
 */
function llms_lostpassword_url() {
	return llms_get_endpoint_url( 'lost-password', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );
}
add_filter( 'lostpassword_url',  'llms_lostpassword_url', 10, 0 );

/**
 * Get endpoint URL
 *
 * @param string $page
 *
 * @return string
 */
function llms_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink(); }

	// Map endpoint to options
	$endpoint = isset( LLMS()->query->query_vars[ $endpoint ] ) ? LLMS()->query->query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );

		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'lifterlms_get_endpoint_url', $url );
}
