<?php
/**
 * Template functions for the student dashboard
 *
 * @package LifterLMS/Functions
 *
 * @since 3.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lifterlms_student_dashboard' ) ) {

	/**
	 * Output the LifterLMS Student Dashboard
	 *
	 * @since 3.25.1
	 * @since 3.35.0 unslash `$_GET` data.
	 * @since 3.37.10 Add filter `llms_enable_open_registration`.
	 * @since 5.0.0 During password reset, retrieve reset key and login from cookie instead of query string.
	 *              Use `llms_get_open_registration_status()`.
	 *
	 * @param array $options Optional. Array of options. Default empty array.
	 * @return void
	 */
	function lifterlms_student_dashboard( $options = array() ) {

		$options = wp_parse_args(
			$options,
			array(
				'layout'         => 'columns',
				'login_redirect' => get_permalink( llms_get_page_id( 'myaccount' ) ),
			)
		);

		/**
		 * Fires before the student dashboard output.
		 *
		 * @since 7.8.0
		 *
		 * @param string $layout The layout of the dashboard.
		 *
		 * @hooked lifterlms_template_student_dashboard_wrapper_open - 10
		 */
		do_action( 'lifterlms_before_student_dashboard', $options['layout'] );

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
		 * @param bool $is_user_logged-in Whether or not the user is logged in.
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
			 * @hooked lifterlms_template_student_dashboard_navigation - 10
			 * @hooked lifterlms_template_student_dashboard_header - 20
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
	 * @since 6.3.0 Fix paged query not working when using plain permalinks.
	 * @since 7.1.3 Added filter for filtering 'Not enrolled text'.
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

			printf(
				'<p>%s</p>',
				/**
				 * Not enrolled text.
				 *
				 * Allows developers to filter the text to be displayed when the student is not enrolled in any courses.
				 *
				 * @since 7.1.3
				 *
				 * @param string $not_enrolled_text The text to be displayed when the student is not enrolled in any course.
				 */
				esc_html( apply_filters( 'lifterlms_dashboard_courses_not_enrolled_text', __( 'You are not enrolled in any courses.', 'lifterlms' ) ) )
			);

		} else {

			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_status', 25 );
			add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_enroll_date', 30 );

			// get sorting option.
			$option = get_option( 'lifterlms_myaccount_courses_in_progress_sorting', 'date,DESC' );
			// parse to order & orderby.
			$option  = explode( ',', $option );
			$orderby = ! empty( $option[0] ) ? $option[0] : 'date';
			$order   = ! empty( $option[1] ) ? $option[1] : 'DESC';

			// Enrollment date will obey the results order.
			if ( 'date' === $orderby ) {
				$orderby = 'post__in';
			} elseif ( 'order' === $orderby ) {
				// Add secondary sorting by `post_title` when the primary sort is `menu_order`.
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
					'paged'          => llms_get_paged_query_var(),
					'orderby'        => $orderby,
					'order'          => $order,
					'post__in'       => $courses['results'],
					'post_status'    => 'publish',
					'post_type'      => 'course',
					'posts_per_page' => $per_page,
				)
			);

			$query = new WP_Query( $query_args );

			// Prevent pagination on the preview.
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

if ( ! function_exists( 'llms_template_my_favorites_loop' ) ) {

	/**
	 * Get student's favorites.
	 *
	 * @since 7.5.0
	 *
	 * @param LLMS_Student $student   Optional. LLMS_Student (current student if none supplied). Default `null`.
	 * @param array        $favorites Optional. Array of favorites (current student's favorites if none supplied). Default `null`.
	 * @return void
	 */
	function llms_template_my_favorites_loop( $student = null, $favorites = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return;
		}

		$favorites = $favorites ?? $student->get_favorites();

		if ( ! $favorites ) {

			printf( '<p>%s</p>', esc_html__( 'No favorites found.', 'lifterlms' ) );

		} else {

			// Adding Parent Course IDs in Favorites for each lesson.
			foreach ( $favorites as $key => $favorite ) {
				$lesson                  = new LLMS_Lesson( $favorite->post_id );
				$favorite->parent_course = $lesson->get( 'parent_course' );
			}

			// Grouping Favorites by Parent Course ID.
			$favorites = array_reduce(
				$favorites,
				function ( $carry, $item ) {
					$carry[ $item->parent_course ][] = $item;
					return $carry;
				},
				array()
			);

			echo '<div class="llms-syllabus-wrapper">';

			// Printing Favorite Lessons under each Parent Course.
			foreach ( $favorites as $course => $lessons ) {

				// Get Course Name.
				$course = new LLMS_Course( $course );

				echo '<h3 class="llms-h3 llms-section-title">';
					echo esc_html( $course->get( 'title' ) );
				echo '</h3>';

				foreach ( $lessons as $lesson ) {

					$lesson = new LLMS_Lesson( $lesson->post_id );

					llms_get_template(
						'course/lesson-preview.php',
						array(
							'lesson' => $lesson,
						)
					);

				}
			}

			echo '</div>';
		}
	}
}

if ( ! function_exists( 'lifterlms_template_my_memberships_loop' ) ) {

	/**
	 * Get course tiles for a student's memberships
	 *
	 * @since 3.14.0
	 * @since 3.14.8 Unknown.
	 * @since 7.1.3 Added filter for filtering 'Not enrolled text'.
	 *
	 * @param LLMS_Student $student Optional. LLMS_Student (current student if none supplied). Default `null`.
	 * @return void
	 */
	function lifterlms_template_my_memberships_loop( $student = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return;
		}

		$memberships = $student->get_membership_levels();

		if ( ! $memberships ) {

			printf(
				'<p>%s</p>',
				/**
				 * Not enrolled text.
				 *
				 * Allows developers to filter the text to be displayed when the student is not enrolled in any memberships.
				 *
				 * @since 7.1.3
				 *
				 * @param string $not_enrolled_text The text to be displayed when the student is not enrolled in any memberships.
				 */
				esc_html( apply_filters( 'lifterlms_dashboard_memberships_not_enrolled_text', __( 'You are not enrolled in any memberships.', 'lifterlms' ) ) )
			);

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

			$query->max_num_pages = 1; // Prevent pagination here.

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
	 * @since 6.0.0 Don't output HTML when the endpoint is disabled.
	 *
	 * @param bool $preview If `true`, outputs a short list of achievements to display on the dashboard
	 *                      landing page. Otherwise displays all of the earned achievements for display
	 *                      on the view-achievements endpoint.
	 * @return void
	 */
	function lifterlms_template_student_dashboard_my_achievements( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$enabled = LLMS_Student_Dashboard::is_endpoint_enabled( 'view-achievements' );
		if ( ! $enabled ) {
			return;
		}

		$more = false;
		if ( $preview ) {
			$more = array(
				'url'  => llms_get_endpoint_url( 'view-achievements', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Achievements', 'lifterlms' ),
			);
		}

		ob_start();

		lifterlms_template_achievements_loop( $student, $preview ? llms_get_achievement_loop_columns() : false );

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
	 * @since 6.0.0 Output short list when `$preview` is `true`.
	 *               Don't output any HTML when the endpoint is disabled.
	 *
	 * @param bool $preview If `true`, outputs a short list of certificates to display on the dashboard
	 *                      landing page. Otherwise displays all of the earned certificates for display
	 *                      on the view-certificates endpoint.
	 * @return void
	 */
	function lifterlms_template_student_dashboard_my_certificates( $preview = false ) {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		$enabled = LLMS_Student_Dashboard::is_endpoint_enabled( 'view-certificates' );
		if ( ! $enabled ) {
			return;
		}

		$more = false;
		if ( $preview ) {
			$more = array(
				'url'  => llms_get_endpoint_url( 'view-certificates', '', llms_get_page_url( 'myaccount' ) ),
				'text' => __( 'View All My Certificates', 'lifterlms' ),
			);
		}

		ob_start();
		lifterlms_template_certificates_loop( $student, $preview ? llms_get_certificates_loop_columns() : false );

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

if ( ! function_exists( 'llms_template_student_dashboard_my_favorites' ) ) {

	/**
	 * Template for My Favorites section on dashboard index.
	 *
	 * @since 7.5.0
	 *
	 * @return void
	 */
	function llms_template_student_dashboard_my_favorites() {

		$student = llms_get_student();

		if ( ! $student || ! llms_is_favorites_enabled() ) {
			return;
		}

		ob_start();
		llms_template_my_favorites_loop( $student );

		llms_get_template(
			'myaccount/my-favorites.php',
			array(
				'content' => ob_get_clean(),
			)
		);
	}
}

if ( ! function_exists( 'lifterlms_template_student_dashboard_my_grades' ) ) {

	/**
	 * Output the "My Grades" template screen on the student dashboard.
	 *
	 * @since 3.24.0
	 * @since 3.26.3 Unknown.
	 * @since 5.3.2 Cast achievement_template ID to string when comparing to the list of achievement IDs related the course/membership (list of strings).
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.0.0 Use updated method signature for `LLMS_Student::get_achievements()`.
	 * @since 6.3.0 Prevent trying to access to a non existing index when retrieving the slug from the `$wp_query`.
	 *              Fixed pagination not working when using plain permalinks.
	 * @return void
	 */
	function lifterlms_template_student_dashboard_my_grades() {

		$student = llms_get_student();
		if ( ! $student ) {
			return;
		}

		global $wp_query, $wp_rewrite;
		$slug = $wp_query->query['my-grades'] ?? '';

		// List courses.
		if ( empty( $slug ) || false !== strpos( $slug, $wp_rewrite->pagination_base . '/' ) ) {

			/**
			 * Filter the number of courses per pages to be displayed in my grades
			 *
			 * @since unknown
			 *
			 * @param int $per_page The number of courses per pages to be displayed. Default is `10`.
			 */
			$per_page = apply_filters( 'llms_sd_grades_courses_per_page', 10 );
			$page     = llms_get_paged_query_var();

			$sort = llms_filter_input_sanitize_string( INPUT_GET, 'sort' );
			if ( ! $sort ) {
				$sort = 'date_desc';
			}
			$parts = explode( '_', $sort );

			// Validate sort.
			$parts[0] = llms_sanitize_with_safelist( $parts[0], array( 'date', 'title' ) );
			$parts[1] = llms_sanitize_with_safelist( $parts[1], array( 'desc', 'asc' ) );

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

			// Show single.
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

			// It's not stupid if it works unless it is stupid.
			$post_ids = array_merge(
				array( $course->get( 'id' ) ),
				$course->get_sections( 'ids' ),
				$course->get_lessons( 'ids' ),
				$course->get_quizzes()
			);

			$achievements = $student->get_achievements(
				array(
					'related_posts' => $post_ids,
					'per_page'      => 1,
					'no_found_rows' => true,
				)
			)->get_awards();

			$latest_achievement = $achievements ? $achievements[0] : false;

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
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *              Fix how the protected {@see LLMS_Notifications_Query::$max_pages} property is accessed.
	 * @since 6.3.0 Fix paged query not working when using plain permalinks.
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

		$view = isset( $_GET['sdview'] ) ? llms_filter_input( INPUT_GET, 'sdview' ) : 'view';

		if ( 'view' === $view ) {

			$page = llms_get_paged_query_var();

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
				'max'     => $notifications->get_max_pages(),
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

			foreach ( llms()->notifications()->get_controllers() as $controller ) {

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
		echo wp_kses_post( apply_filters( 'lifterlms_student_dashboard_title', '<h2 class="llms-sd-title">' . $title . '</h2>', $data ) );
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
	 * @since 7.8.0
	 *
	 * @param string $layout Dashboard layout. Accepts "stacked" (default) or "columns".
	 *
	 * @return void
	 */
	function lifterlms_template_student_dashboard_wrapper_open( $layout ) {
		$current = LLMS_Student_Dashboard::get_current_tab( 'slug' );
		echo '<div class="llms-student-dashboard ' . $current . ' llms-sd-layout-' . esc_attr( $layout ) . '" data-current="' . $current . '">';
	}
endif;

/**
 * Modify the pagination links displayed on endpoints using the default LLMS loop.
 *
 * @since 3.24.0
 * @since 3.26.3 Unknown.
 * @since 6.3.0 Fixed pagination when using plain permalinks.
 * @since 7.2.0 Made sure the pagination links is not altered when not in the LifterLMS dashboard context.
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
	 * @since 7.2.0 Defaults to `false` only on the LifterLMS dashboard context, while `true` elsewhere.
	 *
	 * @param bool   $disable Whether or not the dashboard pagination links should be disabled.
	 *                        Default `false` in the LifterLMS dashboard context, `true` elsewhere.
	 * @param string $link    The default link.
	 */
	if ( apply_filters( 'llms_modify_dashboard_pagination_links_disable', ! is_page( llms_get_page_id( 'myaccount' ) ), $link ) ) {
		return $link;
	}

	global $wp_rewrite;

	$query = wp_parse_url( $link, PHP_URL_QUERY );

	if ( $query ) {
		$link = str_replace( '?' . $query, '', $link );
	}
	// No plain permalinks.
	if ( get_option( 'permalink_structure' ) ) {
		$parts = explode( '/', untrailingslashit( $link ) );
		$page  = end( $parts );
		$link  = llms_get_endpoint_url( LLMS_Student_Dashboard::get_current_tab( 'slug' ), $wp_rewrite->pagination_base . '/' . $page . '/', llms_get_page_url( 'myaccount' ) );
	} else { // With plain permalinks.
		preg_match( '/paged?=([0-9]+)/', $link, $pages ); // Extract the 'page(d)' var.
		$paged  = empty( $pages ) || count( $pages ) < 2 || $pages[1] < 2 ? '' : $pages[0]; // No pagination or page 1 nothing to add.
		$query .= $paged ? '&' . $paged : '';
		$link   = home_url();
	}

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

	ob_start();

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
				echo esc_html( $student->get_completion_date( $lesson->get( 'id' ), get_option( 'date_format' ) ) );
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
					echo '<span class="llms-status llms-' . esc_attr( $attempt->get( 'status' ) ) . '">' . esc_html( $attempt->l10n( 'status' ) ) . '</span>';
				}
				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
			} else {
				echo '&ndash;';
			}
			break;

		case 'overall_grade':
			$grade = $student->get_grade( $lesson->get( 'id' ) );
			echo is_numeric( $grade ) ? wp_kses_post( llms_get_donut( $grade, '', 'mini' ) ) : '&ndash;';
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

	$html = ob_get_clean();

	/**
	 * Filters the HTML returned by llms_sd_my_grades_table_content().
	 *
	 * @since 6.0.0
	 *
	 * @param string       $html         The cell HTML.
	 * @param string       $id           Key of the table cell.
	 * @param LLMS_Lesson  $lesson       LLMS_Lesson.
	 * @param LLMS_Student $student      LLMS_Student.
	 * @param array        $restrictions Restriction data from `llms_page_restricted()`.
	 */
	return apply_filters( 'llms_sd_my_grades_table_content', $html, $id, $lesson, $student, $restrictions );
}
