<?php
/**
 * Page functions
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get url for when user cancels payment
 *
 * @return string [url to redirect user to on form post]
 */
function llms_cancel_payment_url() {

	$cancel_payment_url = esc_url( get_permalink( llms_get_page_id( 'checkout' ) ) );
	return apply_filters( 'lifterlms_checkout_confirm_payment_url', $cancel_payment_url );

}


/**
 * Get url for redirect when user confirms payment
 *
 * @since 1.0.0
 * @since [version] Added redirect query string parameter.
 *
 * @return string
 */
function llms_confirm_payment_url( $order_key = null ) {

	$args = array();

	if ( $order_key ) {
		$args['order'] = $order_key;
	}

	$redirect = urldecode( llms_filter_input( INPUT_GET, 'redirect', FILTER_VALIDATE_URL ) );
	if ( $redirect ) {
		$args['redirect'] = urlencode( $redirect );
	}

	$url = llms_get_endpoint_url( 'confirm-payment', '', get_permalink( llms_get_page_id( 'checkout' ) ) );
	if ( $args ) {
		$url = add_query_arg( $args, $url );
	}

	/**
	 * Filter the checkout confirmation URL
	 *
	 * @since 1.0.0
	 *
	 * @param $string $url URL to the payment confirmation screen.
	 */
	return apply_filters( 'lifterlms_checkout_confirm_payment_url', $url );

}


/**
 * Retrieve the full URL to a LifterLMS endpoint
 *
 * @param    string $endpoint   ID of the endpoint, eg "view-courses"
 * @param    string $value
 * @param    string $permalink  base URL to append the endpoint to
 * @return   string
 * @since    1.0.0
 * @version  3.26.3
 */
function llms_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink(); }

	// Map endpoint to options
	$vars     = LLMS()->query->get_query_vars();
	$endpoint = isset( $vars[ $endpoint ] ) ? $vars[ $endpoint ] : $endpoint;

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

	return apply_filters( 'lifterlms_get_endpoint_url', $url, $endpoint );
}


/**
 * Retrieve the WordPress Page ID of a LifterLMS Page
 *
 * core pages: myaccount, checkout, memberships, courses
 *
 * @param  string $page name of the page
 * @return int
 */
function llms_get_page_id( $page ) {

	// normalize some pages to make more sense without having to migrate options
	if ( 'courses' === $page ) {
		$page = 'shop';
	}

	$page = apply_filters( 'lifterlms_get_' . $page . '_page_id', get_option( 'lifterlms_' . $page . '_page_id' ) );
	return $page ? absint( $page ) : -1;
}


/**
 * Retrieve the URL for a LifterLMS Page
 * EG: 'checkout', 'memberships', 'myaccount', 'courses' etc...
 *
 * @since  3.0.0
 *
 * @param  string $page name of the page
 * @param  array  $args optional array of query arguments that can be passed to add_query_arg()
 * @return string
 */
function llms_get_page_url( $page, $args = array() ) {
	$url = add_query_arg( $args, get_permalink( llms_get_page_id( $page ) ) );
	return $url ? $url : '';
}


/**
 * Returns the url to the lost password endpoint url
 *
 * @since Unknown
 *
 * @return string
 */
function llms_lostpassword_url() {
	return llms_get_endpoint_url( 'lost-password', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );
}
add_filter( 'lostpassword_url', 'llms_lostpassword_url', 10, 0 );
