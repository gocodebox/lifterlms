<?php
/**
 * Template functions for loops (catalogs)
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lifterlms_archive_description' ) ) {
	/**
	 * Output the archive description for LifterLMS catalogs pages and post type / tax archives.
	 *
	 * @since 3.16.10
	 * @since 3.19.0 Unknown.
	 * @since 4.10.0 Moved logic to `lifterlms_get_archive_description()` so the function can be called without outputting the content.
	 *
	 * @see lifterlms_get_archive_description()
	 *
	 * @return void
	 */
	function lifterlms_archive_description() {
		echo wp_kses_post( lifterlms_get_archive_description() );
	}
}

if ( ! function_exists( 'lifterlms_get_archive_description' ) ) {
	/**
	 * Retrieve the archive description for LifterLMS catalogs pages and post type / tax archives.
	 *
	 * If content is added to the course/membership catalog page via the WP editor, output it as the archive description before the loop.
	 *
	 * @since 4.10.0 Moved from `lifterlms_archive_description()`.
	 *               Adjusted filter `llms_archive_description` to always run instead of only running if content exists to display,
	 *               this allows developers to filter the content even when an empty string is returned.
	 * @since 7.3.0 Fixed PHP Warning when no course/membership catalog page was set or if the
	 *              selected page doesn't exist anymore.
	 *
	 * @return string
	 */
	function lifterlms_get_archive_description() {

		$content = '';
		$page_id = false;

		// Get the page id for the catalog page setup in LLMS settings.
		if ( is_post_type_archive( 'course' ) || is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track' ) ) ) {
			$page_id = llms_get_page_id( 'courses' );
		} elseif ( is_post_type_archive( 'llms_membership' ) || is_tax( array( 'membership_tag', 'membership_cat' ) ) ) {
			$page_id = llms_get_page_id( 'memberships' );
		}

		// If a description is setup for the taxonomy term, use that description.
		if ( is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) ) ) {
			$content = get_the_archive_description();
		}

		// If we don't have a description, try to pull it from the page's content area.
		if ( empty( $content ) && (int) $page_id > 0 ) {
			$page    = get_post( $page_id );
			$content = $page ? $page->post_content : $content;
		}

		/**
		 * Filter the archive description
		 *
		 * @since Unknown
		 * @since 4.10.0 Added `$page_id` parameter.
		 *
		 * @param string    $content HTML description string.
		 * @param int|false $page_id WP_Post ID of the archive page being displayed.
		 */
		return apply_filters( 'llms_archive_description', llms_content( $content ), $page_id );
	}
}

/**
 * Output a LifterLMS Loop
 *
 * @param    obj $query  WP_Query, uses global $wp_query if not supplied
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
function lifterlms_loop( $query = null ) {

	global $wp_query;
	$temp = null;

	if ( $query ) {
		$temp     = $wp_query;
		$wp_query = $query;
	}

	if ( have_posts() ) {

		/**
		 * lifterlms_before_loop hook
		 *
		 * @hooked lifterlms_loop_start - 10
		 */
		do_action( 'lifterlms_before_loop' );

		while ( have_posts() ) {
			the_post();
			llms_get_template_part( 'loop/content', get_post_type() );
		}

		/**
		 * lifterlms_before_loop hook
		 *
		 * @hooked lifterlms_loop_end - 10
		 */
		do_action( 'lifterlms_after_loop' );

		llms_get_template_part( 'loop/pagination' );

	} else {

		llms_get_template( 'loop/none-found.php' );
	}

	if ( $query ) {
		$wp_query = $temp;
		wp_reset_postdata();
	}
}

/**
 * Link pagination helper.
 *
 * This is a wrapper around WP's `paginate_links()` method with common styling
 * and helpers for use within LifterLMS.
 *
 * @since 6.0.0
 *
 * @param array $args {
 *     Pagination arguments.
 *
 *     @type integer $current Current page number. Defaults to `1` or the value of `get_query_var( 'paged' )`.
 *     @type integer $total   Total number of pages to display. Defaults to `1` or `$wp_query->max_num_pages`.
 *     @type string  $context Display context. Adds additional customization depending on the context. Supported
 *                            contexts are "student_dashboard" which automatically filters links for use on the
 *                            dashboard.
 * }
 * @return string
 */
function llms_paginate_links( $args ) {

	global $wp_query;

	$args = wp_parse_args(
		$args,
		array(
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => max( 1, $wp_query->max_num_pages ),
			'context' => '',
		)
	);

	// Don't display pagination if there's only one page of results and `show_for_single` isn't explicitly enabled.
	if ( $args['total'] <= 1 ) {
		return '';
	}

	/**
	 * Filter the list of CSS classes on the pagination wrapper element.
	 *
	 * @since 4.10.0
	 *
	 * @param string[] $classes Array of CSS classes.
	 */
	$classes = apply_filters( 'llms_get_pagination_wrapper_classes', array( 'llms-pagination' ) );

	if ( 'student_dashboard' === $args['context'] ) {
		add_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );
	}

	$links = paginate_links(
		array(
			'base'      => str_replace( 999999, '%#%', get_pagenum_link( 999999, false ) ),
			'format'    => '?page=%#%',
			'total'     => $args['total'],
			'current'   => $args['current'],
			'prev_next' => true,
			// Translators: %s = Left double arrow character.
			'prev_text' => sprintf( _x( '%s Previous', 'pagination link text', 'lifterlms' ), '«' ),
			// Translators: %s = Right double arrow character.
			'next_text' => sprintf( _x( 'Next %s', 'pagination link text', 'lifterlms' ), '»' ),
			'type'      => 'list',
		)
	);

	if ( 'student_dashboard' === $args['context'] ) {
		remove_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );
	}

	return sprintf(
		'<nav class="%1$s">%2$s</nav>',
		esc_attr( implode( ' ', $classes ) ),
		$links
	);
}

/**
 * Retrieve the number of columns for llms loops
 *
 * @return   int
 * @since    3.14.0
 * @version  3.14.0
 */
function llms_get_loop_columns() {
	return absint( apply_filters( 'lifterlms_loop_columns', 3 ) );
}

/**
 * Get classes to add to the loop wrapper based on the queried object
 * Used in templates/loop/loop-start.php
 *
 * @return   string
 * @since    3.0.0
 * @version  3.14.0
 */
function llms_get_loop_list_classes() {

	$classes = array();

	$obj = get_queried_object();

	if ( $obj && $obj->name ) {
		$classes[] = 'llms-' . str_replace( 'llms_', '', $obj->name ) . '-list';
	}

	$classes[] = sprintf( 'cols-%d', llms_get_loop_columns() );

	return ' ' . implode( ' ', apply_filters( 'llms_get_loop_list_classes', $classes ) );
}


/**
 * Get archive loop end
 *
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_loop_end' ) ) {
	function lifterlms_loop_end() {
		llms_get_template( 'loop/loop-end.php' );
	}
}


/**
 * Output a featured video on the course tile in a LifterLMS Loop.
 *
 * @since 3.3.0
 * @since 7.1.3 Add div tag to wrap featured video output in loop.
 *
 * @return void
 */
function lifterlms_loop_featured_video() {
	global $post;
	if ( 'course' === $post->post_type || 'llms_membership' === $post->post_type ) {
		$product = llms_get_post( $post );
		if ( 'yes' === $product->get( 'tile_featured_video' ) ) {
			$video = $product->get_video();
			if ( $video ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="llms-video-wrapper">' . $video . '</div>';
			}
		}
	}
}

/**
 * Archive loop link end
 *
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_loop_link_end' ) ) {
	function lifterlms_loop_link_end() {
		if ( llms_loop_link_is_title_only() ) {
			return;
		}
		echo '</a><!-- .llms-loop-link -->';
	}
}

/**
 * Archive loop link start
 *
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_loop_link_start' ) ) {
	function lifterlms_loop_link_start() {
		if ( llms_loop_link_is_title_only() ) {
			return;
		}
		echo '<a class="llms-loop-link" href="' . esc_url( get_the_permalink() ) . '">';
	}
}

/**
 * Whether the current loop tile links only its title.
 *
 * Templates opt in by calling `llms_loop_link_title_only( true )`. Themes that
 * still use the wrapping-link markup never set the flag, so those tiles keep
 * the previous link.
 *
 * @since [version]
 *
 * @param bool|null $enable Pass true or false to set the flag. Omit to read it.
 * @return bool
 */
function llms_loop_link_title_only( $enable = null ) {
	static $title_only = false;

	if ( null !== $enable ) {
		$title_only = (bool) $enable;
	}

	return $title_only;
}

/**
 * Whether the current loop tile should skip the wrapping link.
 *
 * @since [version]
 *
 * @return bool
 */
function llms_loop_link_is_title_only() {
	return llms_loop_link_title_only();
}

/**
 * Whether the current request is the student dashboard page.
 *
 * Course and membership loops replace the main query, so `is_llms_account_page()`
 * is false while those tiles render. The original request query vars still identify
 * the dashboard page.
 *
 * @since [version]
 *
 * @return bool
 */
function llms_is_dashboard_request() {

	if ( function_exists( 'is_llms_account_page' ) && is_llms_account_page() ) {
		return true;
	}

	$page_id = function_exists( 'llms_get_page_id' ) ? (int) llms_get_page_id( 'myaccount' ) : 0;
	if ( ! $page_id ) {
		return false;
	}

	global $wp;
	if ( ! ( $wp instanceof WP ) ) {
		return false;
	}

	if ( isset( $wp->query_vars['page_id'] ) && (int) $wp->query_vars['page_id'] === $page_id ) {
		return true;
	}

	if ( empty( $wp->query_vars['pagename'] ) ) {
		return false;
	}

	$pagename = $wp->query_vars['pagename'];
	$page     = get_page_by_path( $pagename );
	if ( ! $page && false !== strpos( $pagename, '/' ) ) {
		$page = get_page_by_path( strstr( $pagename, '/', true ) );
	}

	return $page && (int) $page->ID === $page_id;
}

/**
 * Heading tag for loop, certificate, achievement, and dashboard content.
 *
 * Dashboard index sits under a section heading, so its items are one level
 * lower than items on a dashboard tab. Archives and shortcodes use h2.
 *
 * @since [version]
 *
 * @param string $context Where the heading is rendered.
 * @return string One of h1-h6.
 */
function llms_get_content_heading_tag( $context = '' ) {

	$tag = 'h2';

	if ( llms_is_dashboard_request() && is_user_logged_in() && class_exists( 'LLMS_Student_Dashboard' ) ) {
		$current = LLMS_Student_Dashboard::get_current_tab( 'slug' );
		$default = apply_filters( 'llms_student_dashboard_default_tab', 'dashboard' );
		$tag     = ( $current === $default ) ? 'h4' : 'h3';
	}

	/**
	 * Filter the heading tag used for LifterLMS loop and dashboard content.
	 *
	 * @since [version]
	 *
	 * @param string $tag     Heading tag.
	 * @param string $context Where the heading is rendered.
	 */
	$tag = apply_filters( 'llms_content_heading_tag', $tag, $context );

	if ( ! in_array( strtolower( (string) $tag ), array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
		return 'h2';
	}

	return strtolower( $tag );
}

/**
 * Output the loop item title, linked on its own.
 *
 * @since [version]
 *
 * @return void
 */
function lifterlms_template_loop_title() {
	$tag = tag_escape( llms_get_content_heading_tag( 'loop' ) );
	printf(
		'<%1$s class="llms-loop-title"><a class="llms-loop-link" href="%2$s">%3$s</a></%1$s>',
		$tag,
		esc_url( get_the_permalink() ),
		esc_html( get_the_title() )
	);
}

/**
 * Get Archive loop start
 *
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_loop_start' ) ) {
	function lifterlms_loop_start() {
		llms_get_template( 'loop/loop-start.php' );
	}
}

/**
 * Get loop item author template
 *
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_author' ) ) {

	function lifterlms_template_loop_author() {
		llms_get_template( 'loop/author.php' );
	}
}

/**
 * Course Difficulty Template Include
 *
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_difficulty' ) ) {

	function lifterlms_template_loop_difficulty() {
		if ( 'course' === get_post_type( get_the_ID() ) ) {
			llms_get_template( 'course/difficulty.php' );
		}
	}
}

/**
 * Count of total lessons in a course.
 *
 * @since 7.5.0
 *
 * @return void.
 */
if ( ! function_exists( 'lifterlms_template_loop_lesson_count' ) ) {

	function lifterlms_template_loop_lesson_count() {
		if ( 'course' === get_post_type( get_the_ID() ) ) {
			llms_get_template( 'course/lesson-count.php' );
		}
	}
}

if ( ! function_exists( 'lifterlms_template_loop_featured_pricing_information' ) ) {
	function lifterlms_template_loop_featured_pricing_information() {
		if ( in_array( get_post_type( get_the_ID() ), array( 'course', 'llms_membership' ) ) ) {
			llms_get_template( 'loop/featured-pricing.php' );
		}
	}
}

/**
 * Show enrollment date meta
 * used on Dashboard only
 *
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_enroll_date' ) ) {

	function lifterlms_template_loop_enroll_date() {
		llms_get_template( 'loop/enroll-date.php' );
	}
}

/**
 * Show enrollment status meta
 * used on dashboard only
 *
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_enroll_status' ) ) {

	function lifterlms_template_loop_enroll_status() {
		llms_get_template( 'loop/enroll-status.php' );
	}
}

/**
 * Lesson Length Template Include
 *
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_length' ) ) {

	function lifterlms_template_loop_length() {
		if ( 'course' === get_post_type( get_the_ID() ) ) {
			llms_get_template( 'course/length.php' );
		}
	}
}

/**
 * Archive loop progress bar for courses
 *
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_progress' ) ) {
	function lifterlms_template_loop_progress() {
		$uid = get_current_user_id();
		$cid = get_the_ID();
		if ( 'course' === get_post_type() && $uid ) {

			$student = new LLMS_Student( $uid );
			lifterlms_course_progress_bar( $student->get_progress( $cid, 'course' ), false, false );

		}
	}
}

/**
 * Product Thumbnail Template Include
 *
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_thumbnail' ) ) {

	function lifterlms_template_loop_thumbnail() {
		llms_get_template( 'loop/featured-image.php' );
	}
}
