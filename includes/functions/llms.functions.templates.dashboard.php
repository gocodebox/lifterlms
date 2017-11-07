<?php
/**
 * Template functions for the student dashboard
 * @since    3.0.0
 * @version  3.14.8
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Get course tiles for a student's courses
 * @param    obj        $student  LLMS_Student (current student if none supplied)
 * @param    boolean    $preview  if true, outputs a short list of courses (based on dashboard_recent_courses filter)
 * @return   void
 * @since    3.14.0
 * @version  3.14.6
 */
if ( ! function_exists( 'lifterlms_template_my_courses_loop' ) ) {
	function lifterlms_template_my_courses_loop( $student = null, $preview = false ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return;
		}

		$courses = $student->get_courses( array(
			'limit' => 500,
		) );

		if ( ! $courses['results'] ) {

			printf( '<p>%s</p>', __( 'You are not enrolled in any courses.', 'lifterlms' ) );

		} else {

			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

			// get sorting option
			$option = get_option( 'lifterlms_myaccount_courses_in_progress_sorting', 'date,DESC' );
			// parse to order & orderby
			$option = explode( ',', $option );
			$orderby = ! empty( $option[0] ) ? $option[0] : 'date';
			$order = ! empty( $option[1] ) ? $option[1] : 'DESC';

			// enrollment date will obey the results order
			if ( 'date' === $orderby ) {
				$orderby = 'post__in';
			} elseif ( 'order' === $orderby ) {
				$orderby = 'menu_order';
			}

			$per_page = apply_filters( 'llms_dashboard_courses_per_page', get_option( 'lifterlms_shop_courses_per_page', 9 ) );
			if ( $preview ) {
				$per_page = apply_filters( 'llms_dashboard_recent_courses_count', llms_get_loop_columns() );
			}

			$query = new WP_Query( array(
				'paged' => get_query_var( 'paged' ),
				'orderby' => $orderby,
				'order' => $order,
				'post__in' => $courses['results'],
				'post_status' => 'publish',
				'post_type' => 'course',
				'posts_per_page' => $per_page,
			) );

			// prevent pagination on the preview
			if ( $preview ) {
				$query->max_num_pages = 1;
			}

			lifterlms_loop( $query );

			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

		}// End if().

	}
}// End if().

/**
 * Get course tiles for a student's courses
 * @param    obj        $student  LLMS_Student (current student if none supplied)
 * @param    boolean    $preview  if true, outputs a short list of courses (based on dashboard_recent_courses filter)
 * @return   void
 * @since    3.14.0
 * @version  3.14.8
 */
if ( ! function_exists( 'lifterlms_template_my_memberships_loop' ) ) {
	function lifterlms_template_my_memberships_loop( $student = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return;
		}

		$memberships = $student->get_membership_levels();

		if ( ! $memberships ) {

			printf( '<p>%s</p>', __( 'You are not enrolled in any memberships.', 'lifterlms' ) );

		} else {

			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

			$query = new WP_Query( array(
				'orderby' => 'title',
				'order' => 'ASC',
				'post__in' => $memberships,
				'post_status' => 'publish',
				'post_type' => 'llms_membership',
				'posts_per_page' => -1,
			) );

			$query->max_num_pages = 1; // prevent pagination here

			lifterlms_loop( $query );

			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

		}

	}
}// End if().


/**
 * Main dashboard homepage template
 * @return void
 * @since    3.14.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_home' ) ) {
	function lifterlms_template_student_dashboard_home() {
		llms_get_template( 'myaccount/dashboard.php' );
	}
}

/**
 * Dashboard header template
 * @return void
 * @since    3.0.0
 * @version  3.0.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_header' ) ) {
	function lifterlms_template_student_dashboard_header() {
		llms_get_template( 'myaccount/header.php' );
	}
}

/**
 * Template for My Achievements on dashboard
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_achievements' ) ) {
	function lifterlms_template_student_dashboard_my_achievements( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$more = false;
		if ( $preview ) {
			$more = array(
				'url' => llms_get_endpoint_url( 'view-achievements', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Achievements', 'lifterlms' ),
			);
		}

		ob_start();

		$limit = $preview ? llms_get_achievement_loop_columns() : false;
		lifterlms_template_achievements_loop( $student, $limit );

		llms_get_template( 'myaccount/dashboard-section.php', array(
			'action' => 'my_achievements',
			'slug' => 'llms-my-achievements',
			'title' => __( 'My Achievements', 'lifterlms' ),
			'content' => ob_get_clean(),
			'more' => $more,
		) );

	}
}

/**
 * Template for My Certificates on dashboard
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_certificates' ) ) {
	function lifterlms_template_student_dashboard_my_certificates( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		ob_start();
		lifterlms_template_certificates_loop( $student );

		llms_get_template( 'myaccount/dashboard-section.php', array(
			'action' => 'my_certificates',
			'slug' => 'llms-my-certificates',
			'title' => __( 'My Certificates', 'lifterlms' ),
			'content' => ob_get_clean(),
			'more' => false,
		) );

	}
}

/**
 * Template for My Courses section on dashboard index
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_courses' ) ) {
	function lifterlms_template_student_dashboard_my_courses( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$more = false;
		if ( $preview ) {
			$more = array(
				'url' => llms_get_endpoint_url( 'view-courses', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Courses', 'lifterlms' ),
			);
		}

		ob_start();
		lifterlms_template_my_courses_loop( $student, $preview );

		llms_get_template( 'myaccount/dashboard-section.php', array(
			'action' => 'my_courses',
			'slug' => 'llms-my-courses',
			'title' => __( 'My Courses', 'lifterlms' ),
			'content' => ob_get_clean(),
			'more' => $more,
		) );

	}
}

/**
 * Template for My Memberships section on dashboard index
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_memberships' ) ) {
	function lifterlms_template_student_dashboard_my_memberships() {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		ob_start();
		lifterlms_template_my_memberships_loop( $student );

		llms_get_template( 'myaccount/dashboard-section.php', array(
			'action' => 'my_memberships',
			'slug' => 'llms-my-memberships',
			'title' => __( 'My Memberships', 'lifterlms' ),
			'content' => ob_get_clean(),
			'more' => false,
		) );

	}
}

/**
 * Dashboard Navigation template
 * @return void
 * @since    3.0.0
 * @version  3.0.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_navigation' ) ) {
	function lifterlms_template_student_dashboard_navigation() {
		llms_get_template( 'myaccount/navigation.php' );
	}
}

/**
 * Dashboard title template
 * @return void
 * @since    3.0.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_title' ) ) {
	function lifterlms_template_student_dashboard_title() {
		$data = LLMS_Student_Dashboard::get_current_tab();
		$title = isset( $data['title'] ) ? $data['title'] : '';
		echo apply_filters( 'lifterlms_student_dashboard_title', '<h2 class="llms-sd-title">' . $title . '</h2>' );
	}
}

/**
 * output the student dashboard wrapper opening tags
 * @return   void
 * @since    3.0.0
 * @version  3.0.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_wrapper_close' ) ) :
	function lifterlms_template_student_dashboard_wrapper_close() {
		echo '</div><!-- .llms-student-dashboard -->';
	}
endif;

/**
 * output the student dashboard wrapper opening tags
 * @return   void
 * @since    3.0.0
 * @version  3.10.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_wrapper_open' ) ) :
	function lifterlms_template_student_dashboard_wrapper_open() {
		$current = LLMS_Student_Dashboard::get_current_tab( 'slug' );
		echo '<div class="llms-student-dashboard ' . $current . '" data-current="' . $current . '">';
	}
endif;
