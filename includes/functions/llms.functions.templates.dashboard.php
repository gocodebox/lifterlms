<?php
/**
 * Template functions for the student dashboard
 *
 * @since    3.0.0
 * @version  3.26.3
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lifterlms_template_student_dashboard' ) ) {

	/**
	 * Output the LifterLMS Student Dashboard
	 *
	 * @since 3.25.1
	 * @since 3.35.0 unslash `$_GET` data.
	 *
	 * @param   array $options  array of options.
	 * @return  void
	 */
	function lifterlms_student_dashboard( $options = array() ) {

		$options = wp_parse_args(
			$options,
			array(
				'login_redirect' => get_permalink( llms_get_page_id( 'myaccount' ) ),
			)
		);

		/**
		 * @hooked lifterlms_template_student_dashboard_wrapper_open - 10
		 */
		do_action( 'lifterlms_before_student_dashboard' );

		// If user is not logged in
		if ( ! is_user_logged_in() ) {

			$message = apply_filters( 'lifterlms_my_account_message', '' );
			if ( ! empty( $message ) ) {
				llms_add_notice( $message );
			}

			global $wp;
			if ( isset( $wp->query_vars['lost-password'] ) ) {

				$args = array();

				if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
					$args['form']   = 'reset_password';
					$args['fields'] = LLMS_Person_Handler::get_password_reset_fields( trim( sanitize_text_field( wp_unslash( $_GET['key'] ) ) ), trim( sanitize_text_field( wp_unslash( $_GET['login'] ) ) ) );
				} else {
					$args['form']   = 'lost_password';
					$args['fields'] = LLMS_Person_Handler::get_lost_password_fields();
				}

				llms_get_template( 'myaccount/form-lost-password.php', $args );

			} else {

				llms_print_notices();

				llms_get_login_form(
					null,
					apply_filters( 'llms_student_dashboard_login_redirect', $options['login_redirect'] )
				);

				// can be enabled / disabled on options page.
				if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) {

					llms_get_template( 'global/form-registration.php' );

				}
			}
		} else {

			$tabs = LLMS_Student_Dashboard::get_tabs();

			$current_tab = LLMS_Student_Dashboard::get_current_tab( 'slug' );

			/**
			 * @hooked lifterlms_template_student_dashboard_header - 10
			 */
			do_action( 'lifterlms_before_student_dashboard_content' );

			if ( isset( $tabs[ $current_tab ] ) && isset( $tabs[ $current_tab ]['content'] ) && is_callable( $tabs[ $current_tab ]['content'] ) ) {

				call_user_func( $tabs[ $current_tab ]['content'] );

			}
		}// End if().

		/**
		 * @hooked lifterlms_template_student_dashboard_wrapper_close - 10
		 */
		do_action( 'lifterlms_after_student_dashboard' );

	}
}// End if().

/**
 * Get course tiles for a student's courses
 *
 * @param    obj        $student  LLMS_Student (current student if none supplied)
 * @param    boolean    $preview  if true, outputs a short list of courses (based on dashboard_recent_courses filter)
 * @return   void
 * @since    3.14.0
 * @version  3.26.3
 */
if ( ! function_exists( 'lifterlms_template_my_courses_loop' ) ) {
	function lifterlms_template_my_courses_loop( $student = null, $preview = false ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return;
		}

		$courses = $student->get_courses(
			apply_filters(
				'llms_my_courses_loop_courses_query_args',
				array(
					'limit' => 500,
				),
				$student
			)
		);

		if ( ! $courses['results'] ) {

			printf( '<p>%s</p>', __( 'You are not enrolled in any courses.', 'lifterlms' ) );

		} else {

			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

			// get sorting option
			$option = get_option( 'lifterlms_myaccount_courses_in_progress_sorting', 'date,DESC' );
			// parse to order & orderby
			$option  = explode( ',', $option );
			$orderby = ! empty( $option[0] ) ? $option[0] : 'date';
			$order   = ! empty( $option[1] ) ? $option[1] : 'DESC';

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

			$query_args = apply_filters(
				'llms_dashboard_courses_wp_query_args',
				array(
					'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
					'orderby'        => $orderby,
					'order'          => $order,
					'post__in'       => $courses['results'],
					'post_status'    => 'publish',
					'post_type'      => 'course',
					'posts_per_page' => $per_page,
				)
			);

			$query = new WP_Query( $query_args );

			// prevent pagination on the preview
			if ( $preview ) {
				$query->max_num_pages = 1;
			}

			add_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

			lifterlms_loop( $query );

			remove_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

		}// End if().

	}
}// End if().

/**
 * Get course tiles for a student's courses
 *
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

			$query = new WP_Query(
				array(
					'orderby'        => 'title',
					'order'          => 'ASC',
					'post__in'       => $memberships,
					'post_status'    => 'publish',
					'post_type'      => 'llms_membership',
					'posts_per_page' => -1,
				)
			);

			$query->max_num_pages = 1; // prevent pagination here

			lifterlms_loop( $query );

			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

		}

	}
}// End if().


/**
 * Main dashboard homepage template
 *
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
 *
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
 *
 * @return   void
 * @since    3.14.0
 * @version  3.19.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_achievements' ) ) {
	function lifterlms_template_student_dashboard_my_achievements( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$more = false;
		if ( $preview && LLMS_Student_Dashboard::is_endpoint_enabled( 'view-achievements' ) ) {
			$more = array(
				'url'  => llms_get_endpoint_url( 'view-achievements', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Achievements', 'lifterlms' ),
			);
		}

		ob_start();

		$limit = $preview ? llms_get_achievement_loop_columns() : false;
		lifterlms_template_achievements_loop( $student, $limit );

		llms_get_template(
			'myaccount/dashboard-section.php',
			array(
				'action'  => 'my_achievements',
				'slug'    => 'llms-my-achievements',
				'title'   => $preview ? __( 'My Achievements', 'lifterlms' ) : '',
				'content' => ob_get_clean(),
				'more'    => $more,
			)
		);

	}
}

/**
 * Template for My Certificates on dashboard
 *
 * @return   void
 * @since    3.14.0
 * @version  3.19.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_certificates' ) ) {
	function lifterlms_template_student_dashboard_my_certificates( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$more = false;
		if ( $preview && LLMS_Student_Dashboard::is_endpoint_enabled( 'view-certificates' ) ) {
			$more = array(
				'url'  => llms_get_endpoint_url( 'view-certificates', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Certificates', 'lifterlms' ),
			);
		}

		ob_start();
		lifterlms_template_certificates_loop( $student );

		llms_get_template(
			'myaccount/dashboard-section.php',
			array(
				'action'  => 'my_certificates',
				'slug'    => 'llms-my-certificates',
				'title'   => $preview ? __( 'My Certificates', 'lifterlms' ) : '',
				'content' => ob_get_clean(),
				'more'    => $more,
			)
		);

	}
}

/**
 * Template for My Courses section on dashboard index
 *
 * @return   void
 * @since    3.14.0
 * @version  3.19.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_courses' ) ) {
	function lifterlms_template_student_dashboard_my_courses( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$more = false;
		if ( $preview && LLMS_Student_Dashboard::is_endpoint_enabled( 'view-courses' ) ) {
			$more = array(
				'url'  => llms_get_endpoint_url( 'view-courses', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Courses', 'lifterlms' ),
			);
		}

		ob_start();
		lifterlms_template_my_courses_loop( $student, $preview );

		llms_get_template(
			'myaccount/dashboard-section.php',
			array(
				'action'  => 'my_courses',
				'slug'    => 'llms-my-courses',
				'title'   => $preview ? __( 'My Courses', 'lifterlms' ) : '',
				'content' => ob_get_clean(),
				'more'    => $more,
			)
		);

	}
}

/**
 * Output the "My Grades" template screen on the student dashboard
 *
 * @return   void
 * @since    3.24.0
 * @version  3.26.3
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_grades' ) ) {
	function lifterlms_template_student_dashboard_my_grades() {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		global $wp_query, $wp_rewrite;
		$slug = $wp_query->query['my-grades'];

		// list courses
		if ( empty( $slug ) || false !== strpos( $slug, $wp_rewrite->pagination_base . '/' ) ) {

			$per_page = apply_filters( 'llms_sd_grades_courses_per_page', 10 );
			$page     = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

			$sort = filter_input( INPUT_GET, 'sort', FILTER_SANITIZE_STRING );
			if ( ! $sort ) {
				$sort = 'date_desc';
			}
			$parts = explode( '_', $sort );

			$courses = $student->get_courses(
				array(
					'limit'   => $per_page,
					'skip'    => $per_page * ( $page - 1 ),
					'orderby' => $parts[0],
					'order'   => strtoupper( $parts[1] ),
				)
			);

			add_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );
			llms_get_template(
				'myaccount/my-grades.php',
				array(
					'courses'    => array_map( 'llms_get_post', $courses['results'] ),
					'student'    => $student,
					'sort'       => $sort,
					'pagination' => array(
						'current' => absint( $page ),
						'max'     => absint( ceil( $courses['found'] / $per_page ) ),
					),
				)
			);
			remove_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

			// show single
		} else {

			$course = get_posts(
				array(
					'name'      => $slug,
					'post_type' => 'course',
				)
			);

			$course = array_shift( $course );
			if ( $course ) {
				$course = llms_get_post( $course );
			}

			// get the latest achievement for the course
			$achievements       = LLMS()->achievements()->get_achievements_by_post( $course->get( 'id' ) );
			$latest_achievement = false;
			foreach ( $student->get_achievements( 'updated_date', 'DESC', 'achievements' ) as $achievement ) {
				if ( in_array( $achievement->get( 'achievement_template' ), $achievements ) ) {
					$latest_achievement = $achievement;
					break;
				}
			}

			$last_activity = $student->get_events(
				array(
					'per_page' => 1,
					'post_id'  => $course->get( 'id' ),
				)
			);

			llms_get_template(
				'myaccount/my-grades-single.php',
				array(
					'course'             => $course,
					'student'            => $student,
					'latest_achievement' => $latest_achievement,
					'last_activity'      => $last_activity ? strtotime( $last_activity[0]->get( 'updated_date' ) ) : false,
				)
			);

		}// End if().

	}
}// End if().

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_grades_table' ) ) {
	/**
	 * Output the template for a single grades table on the student dashboard
	 *
	 * @param   obj $course  LLMS_Course.
	 * @param   obj $student LLMS_Student.
	 * @return  void
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	function lifterlms_template_student_dashboard_my_grades_table( $course, $student ) {

		$section_headings = apply_filters(
			'llms_student_dashboard_my_grades_table_headings',
			array(
				'completion_date' => __( 'Completion Date', 'lifterlms' ),
				'associated_quiz' => __( 'Quiz', 'lifterlms' ),
				'overall_grade'   => __( 'Grade', 'lifterlms' ),
			)
		);

		llms_get_template(
			'myaccount/my-grades-single-table.php',
			array(
				'course'           => $course,
				'student'          => $student,
				'section_headings' => $section_headings,
			)
		);

	}
}

/**
 * Template for My Memberships section on dashboard index
 *
 * @return   void
 * @since    3.14.0
 * @version  3.19.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_my_memberships' ) ) {
	function lifterlms_template_student_dashboard_my_memberships( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$more = false;
		if ( $preview && LLMS_Student_Dashboard::is_endpoint_enabled( 'view-memberships' ) ) {
			$more = array(
				'url'  => llms_get_endpoint_url( 'view-memberships', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Memberships', 'lifterlms' ),
			);
		}

		ob_start();
		lifterlms_template_my_memberships_loop( $student );

		llms_get_template(
			'myaccount/dashboard-section.php',
			array(
				'action'  => 'my_memberships',
				'slug'    => 'llms-my-memberships',
				'title'   => $preview ? __( 'My Memberships', 'lifterlms' ) : '',
				'content' => ob_get_clean(),
				'more'    => $more,
			)
		);

	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_notifications' ) ) {

	/**
	 * Template for My Notifications student dashboard endpoint
	 *
	 * @since 3.26.3
	 * @since 3.35.0 Sanitize `$_GET` data.
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_my_notifications() {

		$url = llms_get_endpoint_url( 'notifications', '', llms_get_page_url( 'myaccount' ) );

		$sections = array(
			array(
				'url'  => $url,
				'name' => __( 'View Notifications', 'lifterlms' ),
			),
			array(
				'url'  => add_query_arg( 'sdview', 'prefs', $url ),
				'name' => __( 'Manage Preferences', 'lifterlms' ),
			),
		);

		$view = isset( $_GET['sdview'] ) ? llms_filter_input( INPUT_GET, 'sdview', FILTER_SANITIZE_STRING ) : 'view';

		if ( 'view' === $view ) {

			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

			$notifications = new LLMS_Notifications_Query(
				array(
					'page'       => $page,
					'per_page'   => apply_filters( 'llms_sd_my_notifications_per_page', 25 ),
					'subscriber' => get_current_user_id(),
					'sort'       => array(
						'created' => 'DESC',
						'id'      => 'DESC',
					),
					'types'      => 'basic',
				)
			);

			$pagination = array(
				'max'     => $notifications->max_pages,
				'current' => $page,
			);

			$args = array(
				'notifications' => $notifications->get_notifications(),
				'pagination'    => $pagination,
				'sections'      => $sections,
			);

		} else {

			$types = apply_filters( 'llms_notification_subscriber_manageable_types', array( 'email' ) );

			$settings = array();
			$student  = new LLMS_Student( get_current_user_id() );

			foreach ( LLMS()->notifications()->get_controllers() as $controller ) {

				foreach ( $types as $type ) {

					$configs = $controller->get_subscribers_settings( $type );
					if ( in_array( 'student', array_keys( $configs ) ) && 'yes' === $configs['student'] ) {

						if ( ! isset( $settings[ $type ] ) ) {
							$settings[ $type ] = array();
						}

						$settings[ $type ][ $controller->id ] = array(
							'name'  => $controller->get_title(),
							'value' => $student->get_notification_subscription( $type, $controller->id, 'yes' ),
						);
					}
				}
			}

			$args = array(
				'sections' => $sections,
				'settings' => $settings,
			);

		}// End if().

		add_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

		llms_get_template( 'myaccount/my-notifications.php', $args );

		remove_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

	}
}// End if().


/**
 * Dashboard Navigation template
 *
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
 *
 * @return void
 * @since    3.0.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_student_dashboard_title' ) ) {
	function lifterlms_template_student_dashboard_title() {
		$data  = LLMS_Student_Dashboard::get_current_tab();
		$title = isset( $data['title'] ) ? $data['title'] : '';
		echo apply_filters( 'lifterlms_student_dashboard_title', '<h2 class="llms-sd-title">' . $title . '</h2>', $data );
	}
}

/**
 * output the student dashboard wrapper opening tags
 *
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
 *
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

/**
 * Modify the pagination links displayed on endpoints using the default LLMS loop
 *
 * @param    string $link  default link.
 * @return   string
 * @since    3.24.0
 * @version  3.26.3
 */
function llms_modify_dashboard_pagination_links( $link ) {

	/**
	 * Allow 3rd parties to disable dashboard pagination link rewriting.
	 * Resolves compatibility issues with LifterLMS WooCommerce.
	 */
	if ( apply_filters( 'llms_modify_dashboard_pagination_links_disable', false, $link ) ) {
		return $link;
	}

	global $wp_rewrite;

	$query = parse_url( $link, PHP_URL_QUERY );

	if ( $query ) {
		$link = str_replace( '?' . $query, '', $link );
	}

	$parts = explode( '/', untrailingslashit( $link ) );
	$page  = end( $parts );
	$link  = llms_get_endpoint_url( LLMS_Student_Dashboard::get_current_tab( 'slug' ), $wp_rewrite->pagination_base . '/' . $page . '/', llms_get_page_url( 'myaccount' ) );
	if ( $query ) {
		$link .= '?' . $query;
	}

	return $link;

}

/**
 * Output content for a single cell on the student single course grades table
 *
 * @param   string $id           key of the table cell.
 * @param   obj    $lesson       LLMS_Lesson.
 * @param   obj    $student      LLMS_Student.
 * @param   array  $restrictions restriction data from `llms_page_restricted()`.
 * @return  void
 * @since   3.24.0
 * @version 3.24.0
 */
function llms_sd_my_grades_table_content( $id, $lesson, $student, $restrictions ) {

	do_action( 'llms_sd_my_grades_table_content_' . $id . '_before', $lesson, $student, $restrictions );

	switch ( $id ) {

		case 'completion_date':
			if ( $student->is_complete( $lesson->get( 'id' ) ) ) {
				echo $student->get_completion_date( $lesson->get( 'id' ), get_option( 'date_format' ) );
			} else {
				echo '&ndash;';
			}
			break;

		case 'associated_quiz':
			if ( $lesson->has_quiz() && $restrictions['is_restricted'] ) {
				echo '<i class="fa fa-lock" aria-hidden="true"></i>';
			} elseif ( $lesson->has_quiz() ) {
				$attempt = $student->quizzes()->get_last_attempt( $lesson->get( 'quiz' ) );
				$url     = $attempt ? $attempt->get_permalink() : get_permalink( $lesson->get( 'quiz' ) );
				$text    = $attempt ? __( 'Review', 'lifterlms' ) : __( 'Start', 'lifterlms' );
				if ( $attempt ) {
					echo '<span class="llms-status llms-' . esc_attr( $attempt->get( 'status' ) ) . '">' . $attempt->l10n( 'status' ) . '</span>';
				}
				echo '<a href="' . $url . '">' . $text . '</a>';
			} else {
				echo '&ndash;';
			}
			break;

		case 'overall_grade':
			$grade = $student->get_grade( $lesson->get( 'id' ) );
			echo is_numeric( $grade ) ? llms_get_donut( $grade, '', 'mini' ) : '&ndash;';
			break;

	}

	do_action( 'llms_sd_my_grades_table_content_' . $id, $lesson, $student, $restrictions );

}
