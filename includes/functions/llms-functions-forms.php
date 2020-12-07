<?php
/**
 * Functions for LifterLMS Forms
 *
 * @package LifterLMS/Functions/Forms
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generate the HTML for a form field
 *
 * this function is used during AJAX calls so needs to be in a core file
 * loaded during AJAX calls!
 *
 * @since 3.0.0
 * @since 3.19.4 Unknown.
 * @since [version] Move from file: llms.functions.core.php.
 *               Utilize `LLMS_Form_Field` class for field generation and output.
 *
 * @param array   $field Field settings.
 * @param boolean $echo Whether or not to output (echo) the field HTML.
 * @return string
 */
function llms_form_field( $field = array(), $echo = true ) {

	$field = new LLMS_Form_Field( $field );

	if ( $echo ) {
		$field->render();
	}

	return $field->get_html();

}

/**
 * Retrieve the form post for a form at a given location.
 *
 * @since [version]
 *
 * @param string $location Form location, one of: "checkout", "enrollment", "registration", or "account".
 * @param array  $args Additional arguments passed to the short-circuit filter in `LLMS_Forms->get_form_post()`.
 * @return WP_Post|false
 */
function llms_get_form( $location, $args = array() ) {

	$forms = LLMS_Forms::instance();
	return $forms->get_form_post( $location, $args );

}

/**
 * Retrieve the HTML for a form at the given location.
 *
 * @since [version]
 *
 * @param string $location Form location, one of: "checkout", "enrollment", "registration", or "account".
 * @param array  $args Additional arguments passed to the short-circuit filter in `LLMS_Forms->get_form_post()`.
 * @return string
 */
function llms_get_form_html( $location, $args = array() ) {

	$forms = LLMS_Forms::instance();
	return $forms->get_form_html( $location, $args );

}

/**
 * Retrieve the title of a form at a given location.
 *
 * Returns an empty string if the form is disabled via form settings.
 *
 * @since [version]
 *
 * @param string $location Form location, one of: "checkout", "enrollment", "registration", or "account".
 * @param array  $args Additional arguments passed to the short-circuit filter in `LLMS_Forms->get_form_post()`.
 * @return string
 */
function llms_get_form_title( $location, $args = array() ) {

	$post = llms_get_form( $location, $args );
	if ( ! $post || ! llms_parse_bool( get_post_meta( $post->ID, '_llms_form_show_title', true ) ) ) {
		return '';
	}

	return get_the_title( $post->ID );

}
