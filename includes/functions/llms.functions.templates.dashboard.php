<?php
/**
 * Template functions for the student dashboard
 *
 * @package LifterLMS/Functions
 *
 * @since 3.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lifterlms_template_student_dashboard' ) ) {

	/**
	 * Output the LifterLMS Student Dashboard
	 *
	 * @since 3.25.1
	 * @since 3.35.0 unslash `$_GET` data.
	 * @since 3.37.10 Add filter `llms_enable_open_registration`.
	 * @since 5.0.0 During password reset, retrieve reset key and login from cookie instead of query string.
	 *                Use `llms_get_open_registration_status()`.
	 *
	 * @param array $options Optional. Array of options. Default empty array.
	 * @return void
	 */
	function lifterlms_student_dashboard( $options = array() ) {

		$options = wp_parse_args(
			$options,
			array(
				'login_redirect' => get_permalink( llms_get_page_id( 'myaccount' ) ),
			)
		);

		/**
		 * Fires before the student dashboard output.
		 *
		 * @since Unknown
		 *
		 * @hooked lifterlms_template_student_dashboard_wrapper_open - 10
		 */
		do_action( 'lifterlms_before_student_dashboard' );

		/**
		 * Filters whether or not to display the student dashboard
		 *
		 * By default, this condition will show the dashboard to a logged in user
		 * and the login/registration forms (as well as the password recovery flow)
		 * to logged out users.
		 *
		 * The `LLMS_View_Manager` class uses this filter to modify the dashboard view
		 * conditionally based on the requested view role.
		 *
		 * @since 4.16.0
		 *
		 * @param type $arg Description.
		 */
		$display_dashboard = apply_filters( 'llms_display_student_dashboard', is_user_logged_in() );

		// Not displaying the dashboard (the user is not logged in), we'll show login/registration forms.
		if ( ! $display_dashboard ) {

			/**
			 * Allow adding a notice message to be displayed in the student dashboard where `llms_print_notices()` will be invoked.
			 *
			 * @since unknown
			 *
			 * @param string $message The notice message to be displayed in the student dashboard. Default empty string.
			 */
			$message = apply_filters( 'lifterlms_my_account_message', '' );
			if ( ! empty( $message ) ) {
				llms_add_notice( $message );
			}

			global $wp;
			if ( isset( $wp->query_vars['lost-password'] ) ) {

				$args = array();
				if ( llms_filter_input( INPUT_GET, 'reset-pass', FILTER_SANITIZE_NUMBER_INT ) ) {
					$args['form'] = 'reset_password';
					$cookie       = llms_parse_password_reset_cookie();
					$key          = '';
					$login        = '';
					$fields       = array();
					if ( is_wp_error( $cookie ) ) {
						llms_add_notice( $cookie->get_error_message(), 'error' );
					} else {
						$fields = LLMS_Person_Handler::get_password_reset_fields( $cookie['key'], $cookie['login'] );
					}
					$args['fields'] = $fields;
				} else {
					$args['form']   = 'lost_password';
					$args['fields'] = LLMS_Person_Handler::get_lost_password_fields();
				}

				llms_get_template( 'myaccount/form-lost-password.php', $args );

			} else {

				llms_print_notices();

				llms_get_login_form(
					null,
					/**
					 * Filter login form redirect URL
					 *
					 * @since unknown
					 *
					 * @param string $login_redirect The login redirect URL.
					 */
					apply_filters( 'llms_student_dashboard_login_redirect', $options['login_redirect'] )
				);

				if ( llms_parse_bool( llms_get_open_registration_status() ) ) {

					llms_get_template( 'global/form-registration.php' );

				}
			}
		} else {

			$tabs = LLMS_Student_Dashboard::get_tabs();

			$current_tab = LLMS_Student_Dashboard::get_current_tab( 'slug' );

			/**
			 * Fires before the student dashboard content output.
			 *
			 * @since unknown
			 *
			 * @hooked lifterlms_template_student_dashboard_header - 10
			 */
			do_action( 'lifterlms_before_student_dashboard_content' );

			if ( isset( $tabs[ $current_tab ] ) && isset( $tabs[ $current_tab ]['content'] ) && is_callable( $tabs[ $current_tab ]['content'] ) ) {

				call_user_func( $tabs[ $current_tab ]['content'] );

			}
		}

		/**
		 * Fires after the student dashboard output.
		 *
		 * @since unknown
		 *
		 * @hooked lifterlms_template_student_dashboard_wrapper_close - 10
		 */
		do_action( 'lifterlms_after_student_dashboard' );

	}
}


if ( ! function_exists( 'lifterlms_template_my_courses_loop' ) ) {

	/**
	 * Get course tiles for a student's courses
	 *
	 * @since 3.14.0
	 * @since 3.26.3 Unknown.
	 * @since 3.37.15 Added secondary sorting by `post_title` when the primary sort is `menu_order`.
	 *
	 * @param LLMS_Student $student Optional. LLMS_Student (current student if none supplied). Default `null`.
	 * @param bool         $preview Optional. If true, outputs a short list of courses (based on dashboard_recent_courses filter). Default `false`.
	 * @return void
	 */
	function lifterlms_template_my_courses_loop( $student = null, $preview = false ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return;
		}

		$courses = $student->get_courses(
			/**
			 * Filter the query args to retrieve the courses ids to be used for the "my_courses" loop.
			 *
			 * @since unknown
			 *
			 * @param array $args The query args.
			 */
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

			// get sorting option.
			$option = get_option( 'lifterlms_myaccount_courses_in_progress_sorting', 'date,DESC' );
			// parse to order & orderby.
			$option  = explode( ',', $option );
			$orderby = ! empty( $option[0] ) ? $option[0] : 'date';
			$order   = ! empty( $option[1] ) ? $option[1] : 'DESC';

			// enrollment date will obey the results order.
			if ( 'date' === $orderby ) {
				$orderby = 'post__in';
			} elseif ( 'order' === $orderby ) {
				// add secondary sorting by `post_title` when the primary sort is `menu_order`.
				$orderby = 'menu_order post_title';
			}

			/**
			 * Filter the number of courses per page to be displayed in the dashboard.
			 *
			 * @since unknown
			 *
			 * @param int $per_page The number or courses per page to be displayed. Defaults to the 'Courses per page' course catalog's setting.
			 */
			$per_page = apply_filters( 'llms_dashboard_courses_per_page', get_option( 'lifterlms_shop_courses_per_page', 9 ) );
			if ( $preview ) {
				/**
				 * Filter the number of courses per page to be displayed in the dashboard, when outputting a short list of courses.
				 *
				 * @since unknown
				 *
				 * @param int $per_page The number or courses per page to be displayed. Default is `3`.
				 */
				$per_page = apply_filters( 'llms_dashboard_recent_courses_count', llms_get_loop_columns() );
			}

			/**
			 * Filter the wp query args to retrieve the courses for the "my_courses" loop.
			 *
			 * @since unknown
			 *
			 * @param array $args The query args.
			 */
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

			// prevent pagination on the preview.
			if ( $preview ) {
				$query->max_num_pages = 1;
			}

			add_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

			lifterlms_loop( $query );

			remove_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

		}

	}
}


if ( ! function_exists( 'lifterlms_template_my_memberships_loop' ) ) {

	/**
	 * Get course tiles for a student's memberships
	 *
	 * @since 3.14.0
	 * @since 3.14.8 Unknown.
	 *
	 * @param LLMS_Student $student Optinoal. LLMS_Student (current student if none supplied). Default `null`.
	 * @return void
	 */
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

			$query->max_num_pages = 1; // prevent pagination here.

			lifterlms_loop( $query );

			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

		}

	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_home' ) ) {

	/**
	 * Main dashboard homepage template
	 *
	 * @since 3.14.0
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_home() {
		llms_get_template( 'myaccount/dashboard.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_header' ) ) {

	/**
	 * Dashboard header template
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_header() {
		llms_get_template( 'myaccount/header.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_achievements' ) ) {

	/**
	 * Template for My Achievements on dashboard
	 *
	 * @since 3.14.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param bool $preview Optional. If true, outputs a short list of courses (based on dashboard_recent_courses filter). Default `false`.
	 * @return void
	 */
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

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_certificates' ) ) {

	/**
	 * Template for My Certificates on dashboard
	 *
	 * @since 3.14.0
	 * @since 3.19.0 Unknown
	 *
	 * @param bool $preview Optional. If true, outputs a short list of courses (based on dashboard_recent_courses filter). Default `false`.
	 * @return void
	 */
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

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_courses' ) ) {

	/**
	 * Template for My Courses section on dashboard index
	 *
	 * @since 3.14.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param bool $preview Optional. If true, outputs a short list of courses (based on dashboard_recent_courses filter). Default `false`.
	 * @return void
	 */
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


if ( ! function_exists( 'lifterlms_template_student_dashboard_my_grades' ) ) {

	/**
	 * Output the "My Grades" template screen on the student dashboard
	 *
	 * @since 3.24.0
	 * @since 3.26.3 Unknown.
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_my_grades() {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		global $wp_query, $wp_rewrite;
		$slug = $wp_query->query['my-grades'];

		// list courses.
		if ( empty( $slug ) || false !== strpos( $slug, $wp_rewrite->pagination_base . '/' ) ) {

			/**
			 * Filter the number of courses per pages to be displayed in my grades
			 *
			 * @since unknown
			 *
			 * @param int $per_page The number of courses per pages to be displayed. Default is `10`.
			 */
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

			// show single.
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

			// get the latest achievement for the course.
			$achievements       = LLMS()->achievements()->get_achievements_by_post( $course->get( 'id' ) );
			$latest_achievement = false;
			foreach ( $student->get_achievements( 'updated_date', 'DESC', 'achievements' ) as $achievement ) {
				if ( in_array( $achievement->get( 'achievement_template' ), $achievements, true ) ) {
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

		}

	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_grades_table' ) ) {

	/**
	 * Output the template for a single grades table on the student dashboard
	 *
	 * @since 3.24.0
	 *
	 * @param LLMS_Course  $course  LLMS_Course.
	 * @param LLMS_Student $student LLMS_Student.
	 * @return void
	 */
	function lifterlms_template_student_dashboard_my_grades_table( $course, $student ) {
		/**
		 * Filter the student dashboard "my grades" table headings
		 *
		 * @since unknown
		 *
		 * @param array $section_headings "My Grades" table headings.
		 */
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

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_memberships' ) ) {

	/**
	 * Template for My Memberships section on dashboard index
	 *
	 * @since 3.14.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param bool $preview Optional. If true, outputs a short list of courses (based on dashboard_recent_courses filter). Default `false`.
	 * @return void
	 */
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
	 * @since 3.37.15 Use `in_array()`'s strict comparison.
	 * @since 3.37.16 Fixed typo when comparing the current view.
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
					/**
					 * Filter the number of notifications per page to be displayed in the dashboard's "my_notifications" tab.
					 *
					 * @since unknown
					 *
					 * @param int $per_page The number of notifications per page to be displayed. Default `25`.
					 */
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

			/**
			 * Filter the types of subscriber notification which can be managed
			 *
			 * @since unknown
			 *
			 * @param array $types The array of manageable types. Default is `array( 'email' )`.
			 */
			$types = apply_filters( 'llms_notification_subscriber_manageable_types', array( 'email' ) );

			$settings = array();
			$student  = new LLMS_Student( get_current_user_id() );

			foreach ( LLMS()->notifications()->get_controllers() as $controller ) {

				foreach ( $types as $type ) {

					$configs = $controller->get_subscribers_settings( $type );

					if ( in_array( 'student', array_keys( $configs ), true ) && 'yes' === $configs['student'] ) {

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

		}

		add_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

		llms_get_template( 'myaccount/my-notifications.php', $args );

		remove_filter( 'paginate_links', 'llms_modify_dashboard_pagination_links' );

	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_navigation' ) ) {

	/**
	 * Dashboard Navigation template
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_navigation() {
		llms_get_template( 'myaccount/navigation.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_title' ) ) {
	/**
	 * Dashboard title template
	 *
	 * @since 3.0.0
	 * @since 3.14.0 Unknown.
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_title() {
		$data  = LLMS_Student_Dashboard::get_current_tab();
		$title = isset( $data['title'] ) ? $data['title'] : '';

		/**
		 * Filter the student dasbhoard title for the current tab
		 *
		 * @since unknown
		 *
		 * @param string $title The student dashboard title.
		 */
		echo apply_filters( 'lifterlms_student_dashboard_title', '<h2 class="llms-sd-title">' . $title . '</h2>', $data );
	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_wrapper_close' ) ) :
	/**
	 * Output the student dashboard wrapper closing tags
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_wrapper_close() {
		echo '</div><!-- .llms-student-dashboard -->';
	}
endif;

if ( ! function_exists( 'lifterlms_template_student_dashboard_wrapper_open' ) ) :
	/**
	 * Output the student dashboard wrapper opening tags
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_wrapper_open() {
		$current = LLMS_Student_Dashboard::get_current_tab( 'slug' );
		echo '<div class="llms-student-dashboard ' . $current . '" data-current="' . $current . '">';
	}
endif;

/**
 * Modify the pagination links displayed on endpoints using the default LLMS loop
 *
 * @since 3.24.0
 * @since 3.26.3 Unknown.
 *
 * @param string $link Default link.
 * @return string
 */
function llms_modify_dashboard_pagination_links( $link ) {

	/**
	 * Allow 3rd parties to disable dashboard pagination link rewriting
	 *
	 * Resolves compatibility issues with LifterLMS WooCommerce.
	 *
	 * @since unknown
	 *
	 * @param bool   $disable Whether or not the dashboard pagination links should be disabled. Default `false`.
	 * @param string $link    The default link.
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
 * @since 3.24.0
 *
 * @param string       $id           Key of the table cell.
 * @param LLMS_Lesson  $lesson       LLMS_Lesson.
 * @param LLMS_Student $student      LLMS_Student.
 * @param array        $restrictions Restriction data from `llms_page_restricted()`.
 * @return void
 */
function llms_sd_my_grades_table_content( $id, $lesson, $student, $restrictions ) {

	/**
	 * Fires before the student dashboard my grades table cell content output
	 *
	 * The dynamic portion of the hook name, `$id`, refers to the key of the table cell.
	 *
	 * @since unknown
	 *
	 * @param LLMS_Lesson  $lesson       LLMS_Lesson instance.
	 * @param LLMS_Student $student      LLMS_Student instance.
	 * @param array        $restrictions Restriction data from `llms_page_restricted()`.
	 */
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

	/**
	 * Fires after the student dashboard my grades default table cell content output
	 *
	 * If id id one oare `completion_date`, `associated_quiz`, `overall_grade`.
	 * Can be used to display custom table cells.
	 *
	 * The dynamic portion of the hook name, `$id`, refers to the key of the table cell.
	 *
	 * @since unknown
	 *
	 * @param LLMS_Lesson  $lesson       LLMS_Lesson instance.
	 * @param LLMS_Student $student      LLMS_Student instance.
	 * @param array        $restrictions Restriction data from `llms_page_restricted()`.
	 */
	do_action( 'llms_sd_my_grades_table_content_' . $id, $lesson, $student, $restrictions );

}
