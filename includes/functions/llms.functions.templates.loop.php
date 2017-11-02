<?php
/**
 * Template functions for the student dashboard
 * @since    1.0.0
 * @version  3.14.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function lifterlms_loop( $query = null ) {

	global $wp_query;
	$temp = null;

	if ( $query ) {
		$temp = $wp_query;
		$wp_query = $query;
	}

	if ( have_posts() ) {

		/**
		 * lifterlms_before_loop hook
		 * @hooked lifterlms_loop_start - 10
		 */
		do_action( 'lifterlms_before_loop' );

		while ( have_posts() ) {
			the_post();
			llms_get_template_part( 'loop/content', get_post_type() );
		}

		/**
		 * lifterlms_before_loop hook
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
 * Retrieve the number of columns for llms loops
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
 * Output a featured video on the course tile in a LifterLMS Loop
 * @return   void
 * @since    3.3.0
 * @version  3.3.0
 */
function lifterlms_loop_featured_video() {
	global $post;
	if ( 'course' === $post->post_type ) {
		$course = llms_get_post( $post );
		if ( 'yes' === $course->get( 'tile_featured_video' ) ) {
			$video = $course->get_video();
			if ( $video ) {
				echo $video;
			}
		}
	}
}

/**
 * Archive loop link end
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_loop_link_end' ) ) {
	function lifterlms_loop_link_end() {
		echo '</a><!-- .llms-loop-link -->';
	}
}

/**
 * Archive loop link start
 * @return  void
 * @since   1.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_loop_link_start' ) ) {
	function lifterlms_loop_link_start() {
		echo '<a class="llms-loop-link" href="' . get_the_permalink() . '">';
	}
}

/**
 * Get Archive loop start
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
 * Show enrollment date meta
 * used on Dashboard only
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
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_thumbnail' ) ) {

	function lifterlms_template_loop_thumbnail() {
		llms_get_template( 'loop/featured-image.php' );
	}
}

/**
 * product View Template Include
 * @return void
 * @since   1.0.0
 * @version 1.0.0
 */
if ( ! function_exists( 'lifterlms_template_loop_view_link' ) ) {

	function lifterlms_template_loop_view_link() {
		llms_get_template( 'loop/view-link.php' );
	}
}
