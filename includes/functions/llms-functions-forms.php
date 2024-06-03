<?php
/**
 * Functions for LifterLMS Forms
 *
 * @package LifterLMS/Functions/Forms
 *
 * @since 5.0.0
 * @version 7.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generate the HTML for a form field
 *
 * This function is used during AJAX calls so needs to be in a core file
 * loaded during AJAX calls!
 *
 * @since 3.0.0
 * @since 3.19.4 Unknown.
 * @since 5.0.0 Move from file: llms.functions.core.php.
 *              Utilize `LLMS_Form_Field` class for field generation and output.
 *
 * @param array      $field       Field settings.
 * @param boolean    $echo        Optional. Whether or not to output (echo) the field HTML. Default is `true`.
 * @param int|object $data_source Optional. Data source where to get field value from. Default is `null`.
 * @return string
 */
function llms_form_field( $field = array(), $echo = true, $data_source = null ) {

	$args = array( $field );
	if ( ! is_null( $data_source ) ) {
		$args[] = $data_source;
	}

	$field = new LLMS_Form_Field( ...$args );

	if ( $echo ) {
		$field->render();
	}

	return $field->get_html();

}

/**
 * Retrieve the form post for a form at a given location.
 *
 * @since 5.0.0
 *
 * @param string $location Form location, one of: "checkout", "registration", or "account".
 * @param array  $args Additional arguments passed to the short-circuit filter in `LLMS_Forms->get_form_post()`.
 * @return WP_Post|false
 */
function llms_get_form( $location, $args = array() ) {
	return LLMS_Forms::instance()->get_form_post( $location, $args );
}

/**
 * Retrieve the HTML for a form at the given location.
 *
 * @since 5.0.0
 *
 * @param string $location Form location, one of: "checkout", "registration", or "account".
 * @param array  $args Additional arguments passed to the short-circuit filter in `LLMS_Forms->get_form_post()`.
 * @return string
 */
function llms_get_form_html( $location, $args = array() ) {
	return LLMS_Forms::instance()->get_form_html( $location, $args );
}

/**
 * Retrieve the title of a form at a given location.
 *
 * Returns an empty string if the form is disabled via form settings.
 *
 * @since 5.0.0
 * @since 5.10.0 Return specific form title for checkout forms and free access plans.
 * @since 7.1.3 Added 3rd missing `$post_id` parameter for the Post Title Filter.
 *
 * @param string $location Form location, one of: "checkout", "registration", or "account".
 * @param array  $args Additional arguments passed to the short-circuit filter in `LLMS_Forms->get_form_post()`.
 * @return string
 */
function llms_get_form_title( $location, $args = array() ) {

	$post = llms_get_form( $location, $args );
	if ( ! $post || ! llms_parse_bool( get_post_meta( $post->ID, '_llms_form_show_title', true ) ) ) {
		return '';
	}

	return 'checkout' === $location && isset( $args['plan'] ) && $args['plan']->is_free()
		?
		apply_filters( 'the_title', get_post_meta( $post->ID, '_llms_form_title_free_access_plans', true ), $post->ID )
		:
		get_the_title( $post->ID );

}

/**
 * Displays a login form.
 *
 * Only displays the form for logged out users (because logged in users cannot login).
 *
 * @since 1.0.0
 * @since 3.19.4 Unknown
 * @since 5.0.0 Moved logic and filters for the $message, $redirect, and $layout parameters from the template into the function.
 *
 * @param string $message  Optional. Messages to display before login form via llms_add_notice().
 * @param string $redirect Optional. URL to redirect to after login. Defaults to current page url.
 * @param string $layout   Optional. Form layout. Accepts either 'columns' (default) or 'stacked'. Default is 'columns'.
 * @return void
 */
if ( ! function_exists( 'llms_get_login_form' ) ) {
	function llms_get_login_form( $message = null, $redirect = null, $layout = 'columns' ) {

		/**
		 * Filters whether or not the login form should be displayed
		 *
		 * By default, the registration form is hidden from logged-in users and
		 * displayed to logged out users.
		 *
		 * @since 4.16.0
		 * @since 5.0.0 Moved from template `global/form-login.php`/.
		 *
		 * @param boolean $hide_form Whether or not to hide the form. If `true`, the form is hidden, otherwise it is displayed.
		 */
		if ( apply_filters( 'llms_hide_login_form', is_user_logged_in() ) ) {
			return;
		}

		/**
		 * Customize the layout of the login form.
		 *
		 * @since Unknown
		 *
		 * @param string $layout Form layout. Accepts "columns" (default) for a side-by-side layout
		 *                       for form fields or "stacked" so fields sit on top of each other. Default is 'columns'.
		 */
		$layout = apply_filters( 'llms_login_form_layout', $layout );

		if ( ! empty( $message ) ) {
			llms_add_notice( $message, 'notice' );
		}

		$redirect = empty( $redirect ) ? get_permalink() : $redirect;

		llms_get_template( 'global/form-login.php', compact( 'message', 'redirect', 'layout' ) );
	}
}
