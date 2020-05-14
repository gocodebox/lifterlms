<?php
/**
 * LifterLMS Conditional Tag Functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.37.0
 * @version 3.37.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_course' ) ) {

	/**
	 * Determine if a single course is being displayed.
	 *
	 * @since Unknown
	 *
	 * @return boolean
	 */
	function is_course() {
		return is_singular( array( 'course' ) );
	}
}

if ( ! function_exists( 'is_course_category' ) ) {

	/**
	 * Determine if a course category archive page is being displayed.
	 *
	 * @since Unknown
	 *
	 * @param  mixed $term Single or array of course category ID(s), name(s), or slug(s).
	 * @return boolean
	 */
	function is_course_category( $term = '' ) {
		return is_tax( 'course_cat', $term );
	}
}

if ( ! function_exists( 'is_course_tag' ) ) {

	/**
	 * Determine if a course tag archive page is being displayed.
	 *
	 * @since 3.37.0
	 *
	 * @param  mixed $term Single or array of course tag ID(s), name(s), or slug(s).
	 * @return boolean
	 */
	function is_course_tag( $term = '' ) {
		return is_tax( 'course_tag', $term );
	}
}

if ( ! function_exists( 'is_course_taxonomy' ) ) {

	/**
	 * Determine if any course taxonomy archive page is being displayed.
	 *
	 * @since Unknown
	 *
	 * @return boolean
	 */
	function is_course_taxonomy() {
		return is_tax( get_object_taxonomies( 'course' ) );
	}
}

if ( ! function_exists( 'is_courses' ) ) {

	/**
	 * Determine if the course catalog (post type archive) is being displayed.
	 *
	 * @since 1.4.4
	 * @since 3.0.0 Unknown.
	 * @since 3.37.0 Remove ternary.
	 *
	 * @return boolean
	 */
	function is_courses() {
		return ( ( is_post_type_archive( 'course' ) ) || ( is_singular() && is_page( llms_get_page_id( 'courses' ) ) ) );
	}
}

if ( ! function_exists( 'is_lesson' ) ) {

	/**
	 * Determine if current post is a lifterLMS Lesson
	 *
	 * @since Unknown
	 * @since 3.37.0 Use `is_singular()` instead of comparing against global post's post type.
	 *
	 * @return boolean
	 */
	function is_lesson() {
		return is_singular( array( 'lesson' ) );
	}
}
if ( ! function_exists( 'is_lifterlms' ) ) {

	/**
	 * Determine if a LifterLMS post type or post type archive is being displayed.
	 *
	 * @since Unknown.
	 *
	 * @return boolean
	 */
	function is_lifterlms() {

		/**
		 * Modify the return of the is_lifterlms() conditional function.
		 *
		 * @since Unknown
		 * @since 3.37.0 Add check for `is_membership_taxonomy()`.
		 *
		 * @param boolean $is_lifterlms Default value.
		 */
		return apply_filters( 'is_lifterlms', ( is_course() || is_courses() || is_course_taxonomy() || is_lesson() || is_quiz() || is_membership() || is_memberships() || is_membership_taxonomy() ) );

	}
}

if ( ! function_exists( 'is_llms_account_page' ) ) {

	/**
	 * Determine if the LifterLMS Student Dashboard (account page) is being displayed.
	 *
	 * @since 1.4.6
	 * @since 3.37.0 Remove ternary condition.
	 *
	 * @return boolean
	 */
	function is_llms_account_page() {

		/**
		 * Override the default return of `is_llms_account_page()`
		 *
		 * @since Unknown
		 *
		 * @param bool $override Default override value (false).
		 */
		return ( is_page( llms_get_page_id( 'myaccount' ) ) || apply_filters( 'lifterlms_is_account_page', false ) );

	}
}

if ( ! function_exists( 'is_llms_checkout' ) ) {

	/**
	 * Determine if the LifterLMS Checkout page is being displayed.
	 *
	 * @since 1.4.6
	 * @since 3.37.0 Remove ternary condition.
	 *
	 * @return boolean
	 */
	function is_llms_checkout() {
		return is_page( llms_get_page_id( 'checkout' ) );
	}
}

if ( ! function_exists( 'is_membership' ) ) {

	/**
	 * Determine if a Membership is being displayed.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	function is_membership() {
		return is_singular( array( 'llms_membership' ) );
	}
}

if ( ! function_exists( 'is_membership_category' ) ) {

	/**
	 * Determine if a membership category archive page is being displayed.
	 *
	 * @since 3.37.0
	 *
	 * @param  mixed $term Single or array of membership category ID(s), name(s), or slug(s).
	 * @return boolean
	 */
	function is_membership_category( $term = '' ) {
		return is_tax( 'membership_cat', $term );
	}
}

if ( ! function_exists( 'is_membership_tag' ) ) {

	/**
	 * Determine if a membership tag archive page is being displayed.
	 *
	 * @since 3.37.0
	 *
	 * @param  mixed $term Single or array of membership tag ID(s), name(s), or slug(s).
	 * @return boolean
	 */
	function is_membership_tag( $term = '' ) {
		return is_tax( 'membership_tag', $term );
	}
}

if ( ! function_exists( 'is_membership_taxonomy' ) ) {

	/**
	 * Determine if any course taxonomy archive page is being displayed.
	 *
	 * @since 3.22.0
	 *
	 * @return bool
	 */
	function is_membership_taxonomy() {
		return is_tax( get_object_taxonomies( 'llms_membership' ) );
	}
}

if ( ! function_exists( 'is_memberships' ) ) {

	/**
	 * Determine if the membership catalog (post type archive) is being displayed.
	 *
	 * @since Unknown
	 * @since 3.37.0 Removed ternary condition.
	 *
	 * @return boolean
	 */
	function is_memberships() {
		return ( is_post_type_archive( 'llms_membership' ) || ( is_singular() && is_page( llms_get_page_id( 'memberships' ) ) ) );
	}
}

if ( ! function_exists( 'is_quiz' ) ) {

	/**
	 * Determine if a single Quiz is being displayed.
	 *
	 * @since Unknown.
	 * @since 3.37.0 Use `is_singular()` instead of comparing against global post's post type.
	 *
	 * @return boolean
	 */
	function is_quiz() {
		return is_singular( array( 'llms_quiz' ) );
	}
}
