<?php
/**
 * Page functions.
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 6.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get url for when user cancels payment.
 *
 * @since 1.0.0
 *
 * @return string
 */
function llms_cancel_payment_url() {

	$cancel_payment_url = esc_url( get_permalink( llms_get_page_id( 'checkout' ) ) );
	return apply_filters( 'lifterlms_checkout_confirm_payment_url', $cancel_payment_url );
}

/**
 * Get url for redirect when user confirms payment.
 *
 * @since 1.0.0
 * @since 3.38.0 Added redirect query string parameter.
 * @since 5.9.0 Avoid passing `null` to `urldecode()` when no redirect is set in the `$_GET` array.
 *
 * @return string
 */
function llms_confirm_payment_url( $order_key = null ) {

	$args = array();

	if ( $order_key ) {
		$args['order'] = $order_key;
	}

	$redirect = llms_filter_input( INPUT_GET, 'redirect', FILTER_VALIDATE_URL );
	if ( $redirect ) {
		$args['redirect'] = rawurlencode( urldecode( $redirect ) );
	}

	$url = llms_get_endpoint_url( 'confirm-payment', '', get_permalink( llms_get_page_id( 'checkout' ) ) );
	if ( $args ) {
		$url = add_query_arg( $args, $url );
	}

	/**
	 * Filter the checkout confirmation URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url URL to the payment confirmation screen.
	 */
	return apply_filters( 'lifterlms_checkout_confirm_payment_url', $url );
}

/**
 * Retrieve the full URL to a LifterLMS endpoint.
 *
 * @since 1.0.0
 * @since 3.26.3 Unknown.
 * @since 5.9.0 Update to ensure the generated URL has (or doesn't have) a trailing slash based on the site's permalink settings.
 * @since 6.3.0 Try to build the correct URL even when `get_permalink()` returns an empty string (e.g. in BuddyPress profile endpoints).
 *              Prefer faster `strpos()` over `strstr()` since we only need to know if a substring is contained in a string.
 *
 * @param string $endpoint  ID of the endpoint, eg "view-courses".
 * @param string $value     Endpoint query parameter value.
 * @param string $permalink Base URL to append the endpoint to. Optional, uses the current page when not supplied.
 * @return string
 */
function llms_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {

	// Map endpoint to options.
	$vars     = llms()->query->get_query_vars();
	$endpoint = $vars[ $endpoint ] ?? $endpoint;

	/**
	 * In our dashboard endpoints, get_permalink() always returns the dashboard page permalink:
	 * something like https://example.com/dashboard/
	 * which is the base URL to append the endpoint to.
	 */
	$permalink         = $permalink ? $permalink : get_permalink();
	$is_base_permalink = true;

	/**
	 * No permalink available, e.g. in BuddyPress profile endpoint.
	 *
	 * We need to get the base URL to append the endpoint to, starting from
	 * the current requested URL.
	 */
	if ( ! $permalink && ! empty( $_SERVER['REQUEST_URI'] ) ) {
		$permalink         = home_url( filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL ) );
		$is_base_permalink = false;
	}

	if ( get_option( 'permalink_structure' ) ) {

		$query_string = '';

		if ( false !== strpos( $permalink, '?' ) ) {
			$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		}

		/**
		 * Normalize the permalink when not referring to the base URL.
		 */
		if ( ! $is_base_permalink ) {
			$permalink = _llms_normalize_endpoint_base_url( $permalink, $endpoint );
		}

		$url = trailingslashit( $permalink );

		if ( $value ) {
			$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
		} else {
			$url .= user_trailingslashit( $endpoint );
		}

		$url .= $query_string;

	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	/**
	 * Filter the final endpoint URL.
	 *
	 * @since 1.0.0
	 * @since 5.9.0 Added `$value` and `$permalink` parameters.
	 *
	 * @param string $url       The endpoint URL.
	 * @param string $endpoint  ID of the endpoint.
	 * @param string $value     Endpoint query parameter value.
	 * @param string $permalink Base URL to append the endpoint to. Optional, uses the current page when not supplied.
	 */
	return apply_filters( 'lifterlms_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

/**
 * Normalize the endpoint base URL.
 *
 * E.g., in the BuddyPress profile's tab, on my grades, page 2, it'll look like
 * //example.com/members/admin/courses/my-courses/page/2/
 *
 * We then need to normalize the endpoint base URL, which means
 * removing /my-courses/ (the endpoint) and the pagination information /page/2/.
 *
 * @since 6.3.0
 * @access private
 *
 * @param string $url      URL to extract the Base URL, to append the endpoint to, from.
 * @param string $endpoint Slug of the endpoint, eg "my-courses".
 * @return string
 */
function _llms_normalize_endpoint_base_url( $url, $endpoint ) {

	$_url = untrailingslashit( $url );

	// Remove pagination.
	global $wp_rewrite;
	$page       = llms_get_paged_query_var();
	$pagination = '/' . $wp_rewrite->pagination_base . '/' . $page;

	if ( $page > 1 && substr( $_url, -1 * strlen( $pagination ) ) === $pagination ) { // PHP8: str_ends_with(string $haystack, string $needle).
		$_url = substr( $_url, 0, -1 * strlen( $pagination ) );
	}

	// Remove the endpoint slug from the URL if it's its last part.
	if ( substr( $_url, -1 * strlen( $endpoint ) ) === $endpoint ) { // PHP8: str_ends_with(string $haystack, string $needle).
		$url = substr( $_url, 0, -1 * strlen( $endpoint ) );
	}

	return $url;
}

/**
 * Retrieve the WordPress Page ID of a LifterLMS Core Page.
 *
 * Available core pages are:
 * + checkout (formerly "shop")
 * + courses (Course catalog)
 * + myaccount (Student Dashboard)
 * + memberships (Membership catalog)
 *
 * @since 1.0.0
 *
 * @param string $page The page slug/name.
 * @return int The WP_Post ID of the page or -1 if the page is not found.
 */
function llms_get_page_id( $page ) {

	// Normalize some pages to make more sense without having to migrate options.
	if ( 'courses' === $page ) {
		$page = 'shop';
	}

	$id = get_option( 'lifterlms_' . $page . '_page_id' );

	/**
	 * Filter the ID of the requested LifterLMS Page.
	 *
	 * The dynamic portion of this filter, {$page}, refers to the LifterLMS page slug/name.
	 *
	 * Note that, historically, the course catalog was called the "shop" and therefore when requesting
	 * the filter will be "lifterlms_get_shop_page_id" instead of "lifterlms_get_courses_page_id".
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $id The WP_Post ID of the requested page or an empty string if the page doesn't exist.
	 */
	$page = apply_filters( "lifterlms_get_{$page}_page_id", $id );

	return $page ? absint( $page ) : -1;
}


/**
 * Retrieve the URL for a LifterLMS Page.
 *
 * EG: 'checkout', 'memberships', 'myaccount', 'courses' etc...
 *
 * @since  3.0.0
 *
 * @param string $page Name of the page.
 * @param array  $args Optional array of query arguments that can be passed to add_query_arg().
 * @return string
 */
function llms_get_page_url( $page, $args = array() ) {
	$url = add_query_arg( $args, get_permalink( llms_get_page_id( $page ) ) );
	return $url ? $url : '';
}


/**
 * Returns the url to the lost password endpoint url.
 *
 * @since Unknown
 *
 * @return string
 */
function llms_lostpassword_url( $lostpassword_url ) {
	if ( llms_get_page_id( 'myaccount' ) <= 0 || ! get_permalink( llms_get_page_id( 'myaccount' ) ) ) {
		return $lostpassword_url;
	}

	return llms_get_endpoint_url( 'lost-password', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );
}
add_filter( 'lostpassword_url', 'llms_lostpassword_url', 10, 1 );

/**
 * Returns the page number query var for the current request.
 *
 * `paged`:
 * Used on the homepage, blogpage, archive pages and pages to calculate pagination.
 * 1st page is 0 and from there the number correspond to the page number
 * `page`:
 * Used on a static front page and single pages for pagination (`<!--nextpage-->`).
 * Pagination on these pages works the same, a static front page is treated as single page on pagination.
 *
 * @since 6.3.0
 *
 * @return int
 */
function llms_get_paged_query_var() {

	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = get_query_var( 'page' );
	} else {
		$paged = 1;
	}
	return (int) $paged;
}
