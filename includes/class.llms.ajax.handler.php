<?php
/**
 * LifterLMS AJAX Event Handler.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_AJAX_Handler class
 *
 * @since 1.0.0
 * @since 3.0.0 Added `bulk_enroll_students()` handler.
 * @since 3.2.0 Added `get_admin_table_data()` handler.
 * @since 3.4.0 Unknown.
 * @since 3.13.0 Added `instructors_mb_store()` handler.
 * @since 3.15.0 Added `export_admin_table()` handler, and other unknown changes.
 * @since 3.28.1 Unknown.
 * @since 3.30.0 Added `llms_save_membership_autoenroll_courses` method.
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.32.0 Update `select2_query_posts` to use llms_filter_input() and allows for querying posts by post status(es).
 * @since 3.33.0 Update `update_student_enrollment` to handle enrollment deletion requests, make sure the input array param 'post_id' field is not empty.
 *               Also always return either a WP_Error on failure or a "success" array on requested action performed.
 * @since 3.33.1 Update `llms_update_access_plans` to use `wp_unslash()` before inserting access plan data.
 * @since 3.37.2 Update `select2_query_posts` to allow filtering posts by instructor.
 * @since 3.37.14 Added `persist_tracking_events()` handler.
 *                Used strict comparison where needed.
 * @since 3.37.15 Update `get_admin_table_data()` and `export_admin_table()` to verify user permissions before processing data.
 * @since 3.39.0 Minor code readability updates to the `validate_coupon_code()` method.
 * @since 5.7.0 Deprecated the `LLMS_AJAX_Handler::add_lesson_to_course()` method with no replacement.
 *              Deprecated the `LLMS_AJAX_Handler::create_lesson()` method with no replacement.
 *              Deprecated the `LLMS_AJAX_Handler::create_section()` method with no replacement.
 */
class LLMS_AJAX_Handler {
	/**
	 * Queue all members of a membership to be enrolled into a specific course
	 *
	 * Triggered from the auto-enrollment tab of a membership.
	 *
	 * @since 3.4.0
	 * @since 3.15.0 Unknown.
	 *
	 * @param array $request Array of request data.
	 * @return array
	 */
	public static function bulk_enroll_membership_into_course( $request ) {

		if ( empty( $request['post_id'] ) || empty( $request['course_id'] ) ) {
			return new WP_Error( 400, __( 'Missing required parameters', 'lifterlms' ) );
		}

		do_action( 'llms_membership_do_bulk_course_enrollment', $request['post_id'], $request['course_id'] );

		return array(
			'message' => __( 'Members are being enrolled in the background. You may leave this page.', 'lifterlms' ),
		);
	}

	/**
	 * Add or remove a student from a course or membership
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Unknown.
	 *
	 * @param array $request $_REQUEST object.
	 * @return (void|WP_Error)
	 */
	public static function bulk_enroll_students( $request ) {

		if ( empty( $request['post_id'] ) || empty( $request['student_ids'] ) || ! is_array( $request['student_ids'] ) ) {
			return new WP_Error( 400, __( 'Missing required parameters', 'lifterlms' ) );
		}

		$post_id = intval( $request['post_id'] );

		foreach ( $request['student_ids'] as $id ) {
			llms_enroll_student( intval( $id ), $post_id, 'admin_' . get_current_user_id() );
		}
	}

	/**
	 * Determines if voucher codes already exist.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public static function check_voucher_duplicate() {

		$post_id = ! empty( $_REQUEST['postId'] ) ? absint( llms_filter_input( INPUT_POST, 'postId', FILTER_SANITIZE_NUMBER_INT ) ) : 0;
		$codes   = ! empty( $_REQUEST['codes'] ) ? llms_filter_input_sanitize_string( INPUT_POST, 'codes', array( FILTER_REQUIRE_ARRAY ) ) : array();

		if ( ! $post_id || ! $codes ) {
			return new WP_Error( 400, __( 'Missing required parameters', 'lifterlms' ) );
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 401, __( 'Missing required permissions to perform this action.', 'lifterlms' ) );
		}

		$codes = implode(
			',',
			array_map(
				function ( $code ) {
					return sprintf( "'%s'", esc_sql( $code ) );
				},
				array_filter( $codes )
			)
		);

		global $wpdb;
		$table = $wpdb->prefix . 'lifterlms_vouchers_codes';
		$res   = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT code FROM $table WHERE code IN( $codes ) AND voucher_id != %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				array( $post_id )
			),
			ARRAY_A
		);

		wp_send_json(
			array(
				'success'    => true,
				'duplicates' => $res,
			)
		);
		wp_die();
	}

	/**
	 * Move a Product Access Plan to the trash
	 *
	 * @since 3.0.0
	 *
	 * @param array $request $_REQUEST object.
	 * @return bool|WP_Error WP_Error on error, true if successful.
	 */
	public static function delete_access_plan( $request ) {

		// shouldn't be possible.
		if ( empty( $request['plan_id'] ) ) {
			die();
		}

		if ( ! wp_trash_post( $request['plan_id'] ) ) {

			$err = new WP_Error();
			$err->add( 'error', __( 'There was a problem deleting your access plan, please try again.', 'lifterlms' ) );
			return $err;

		}

		return true;
	}

	/**
	 * Retrieve a new instance of admin table class from a handler string.
	 *
	 * @since 3.37.15
	 * @since 4.7.0 Don't require `LLMS_Admin_Reporting`, it's loaded automatically.
	 *
	 * @param string $handler Unprefixed handler class string. For example "Students" or "Course_Students".
	 * @return object|false Instance of the admin table class or false if the class can't be found.
	 */
	protected static function get_admin_table_instance( $handler ) {

		LLMS_Admin_Reporting::includes();

		$handler = 'LLMS_Table_' . $handler;
		if ( class_exists( $handler ) ) {
			return new $handler();
		}

		return false;
	}

	/**
	 * Queue a table export event
	 *
	 * @since 3.15.0
	 * @since 3.28.1 Unknown.
	 * @since 3.37.15 Verify user permissions before processing request data.
	 *
	 * @param array $request Post data ($_REQUEST).
	 * @return array
	 */
	public static function export_admin_table( $request ) {

		if ( ! current_user_can( 'view_lifterlms_reports' ) || empty( $request['handler'] ) ) {
			return false;
		}

		$table = self::get_admin_table_instance( $request['handler'] );
		if ( ! $table ) {
			return false;
		}

		$file = isset( $request['filename'] ) ? $request['filename'] : null;
		return $table->generate_export_file( $request, $file );
	}

	/**
	 * Reload admin tables
	 *
	 * @since 3.2.0
	 * @since 3.37.15 Verify user permissions before processing request data.
	 *                Use `wp_json_encode()` in favor of `json_encode()`.
	 *
	 * @param array $request Post data ($_REQUEST).
	 * @return array
	 */
	public static function get_admin_table_data( $request ) {

		if ( ! current_user_can( 'view_lifterlms_reports' ) || empty( $request['handler'] ) ) {
			return false;
		}

		$table = self::get_admin_table_instance( $request['handler'] );
		if ( ! $table ) {
			return false;
		}

		$table->get_results( $request );
		return array(
			'args'  => wp_json_encode( $table->get_args() ),
			'thead' => trim( $table->get_thead_html() ),
			'tbody' => trim( $table->get_tbody_html() ),
			'tfoot' => trim( $table->get_tfoot_html() ),
		);
	}

	/**
	 * Store data for the instructors metabox
	 *
	 * @since 3.13.0
	 * @since 3.30.3 Fixed typos.
	 *
	 * @param array $request $_REQUEST object.
	 * @return array
	 */
	public static function instructors_mb_store( $request ) {

		// validate required params.
		if ( ! isset( $request['store_action'] ) || ! isset( $request['post_id'] ) ) {

			return array(
				'data'    => array(),
				'message' => __( 'Missing required parameters', 'lifterlms' ),
				'success' => false,
			);

		}

		$post = llms_get_post( $request['post_id'] );

		switch ( $request['store_action'] ) {

			case 'load':
				$instructors = $post->get_instructors();
				break;

			case 'save':
				$instructors = array();

				foreach ( $request['rows'] as $instructor ) {

					foreach ( $instructor as $key => $val ) {

						$new_key                = str_replace( array( 'llms', '_' ), '', $key );
						$new_key                = preg_replace( '/[0-9]+/', '', $new_key );
						$instructor[ $new_key ] = $val;
						unset( $instructor[ $key ] );

					}

					$instructors[] = $instructor;

				}

				$post->set_instructors( $instructors );

				break;

		}

		$data = array();

		foreach ( $instructors as $instructor ) {

			$new_instructor = array();
			foreach ( $instructor as $key => $val ) {
				if ( 'id' === $key ) {
					$val = llms_make_select2_student_array( array( $instructor['id'] ) );
				}
				$new_instructor[ '_llms_' . $key ] = $val;
			}
			$data[] = $new_instructor;
		}

		wp_send_json(
			array(
				'data'    => $data,
				'message' => 'success',
				'success' => true,
			)
		);
	}

	/**
	 * Handle notification display & dismissal.
	 *
	 * @since 3.8.0
	 * @since 3.37.14 Use strict comparison.
	 * @since 7.1.0 Improve notifications query performance by not calculating unneeded found rows.
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function notifications_heartbeart( $request ) {

		$ret = array(
			'new' => array(),
		);

		if ( ! empty( $request['dismissals'] ) ) {
			foreach ( $request['dismissals'] as $nid ) {
				$noti = new LLMS_Notification( $nid );
				if ( get_current_user_id() === absint( $noti->get( 'subscriber' ) ) ) {
					$noti->set( 'status', 'read' );
				}
			}
		}

		// Get 5 most recent new notifications for the current user.
		$query = new LLMS_Notifications_Query(
			array(
				'per_page'      => 5,
				'statuses'      => 'new',
				'types'         => 'basic',
				'subscriber'    => get_current_user_id(),
				'no_found_rows' => true,
			)
		);

		$ret['new'] = $query->get_notifications();

		return $ret;
	}

	/**
	 * Remove a course from the list of membership auto enrollment courses
	 *
	 * Called from "Auto Enrollment" tab of LLMS Membership Metaboxes.
	 *
	 * @since 3.0.0
	 *
	 * @param array $request $_POST data.
	 * @return (void|WP_Error)
	 */
	public static function membership_remove_auto_enroll_course( $request ) {

		if ( empty( $request['post_id'] ) || empty( $request['course_id'] ) ) {
			return new WP_Error( 'error', __( 'Missing required parameters.', 'lifterlms' ) );
		}

		$membership = new LLMS_Membership( $request['post_id'] );

		if ( ! $membership->remove_auto_enroll_course( intval( $request['course_id'] ) ) ) {
			return new WP_Error( 'error', __( 'There was an error removing the course, please try again.', 'lifterlms' ) );
		}
	}

	/**
	 * Retrieve Students.
	 *
	 * Used by Select2 AJAX functions to load paginated student results.
	 * Also allows querying by:
	 *      first name
	 *      last name
	 *      email.
	 *
	 * @since Unknown
	 * @since 3.14.2 Unknown.
	 * @since 5.5.0 Do not encode quotes when sanitizing search term.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @deprecated 6.2.0 `LLMS_AJAX_Handler::query_students()` is deprecated in favor of the REST API list students endpoint.
	 *
	 * @return void
	 */
	public static function query_students() {

		_deprecated_function( __METHOD__, '6.2.0', 'the REST API list students endpoint' );

		// Grab the search term if it exists.
		$term = array_key_exists( 'term', $_REQUEST ) ? llms_filter_input_sanitize_string( INPUT_POST, 'term', array( FILTER_FLAG_NO_ENCODE_QUOTES ) ) : '';

		$page = array_key_exists( 'page', $_REQUEST ) ? llms_filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT ) : 0;

		$enrolled_in     = array_key_exists( 'enrolled_in', $_REQUEST ) ? sanitize_text_field( wp_unslash( $_REQUEST['enrolled_in'] ) ) : null;
		$not_enrolled_in = array_key_exists( 'not_enrolled_in', $_REQUEST ) ? sanitize_text_field( wp_unslash( $_REQUEST['not_enrolled_in'] ) ) : null;

		$roles = array_key_exists( 'roles', $_REQUEST ) ? sanitize_text_field( wp_unslash( $_REQUEST['roles'] ) ) : null;

		global $wpdb;

		$limit = 30;
		$start = $limit * $page;

		$vars = array();

		$roles_sql = '';
		if ( $roles ) {
			$roles = explode( ',', $roles );
			$roles = array_map( 'trim', $roles );
			$total = count( $roles );
			foreach ( $roles as $i => $role ) {
				$roles_sql .= "roles.meta_value LIKE '%s'";
				$vars[]     = '%"' . $role . '"%';
				if ( $total > 1 && $i + 1 !== $total ) {
					$roles_sql .= ' OR ';
				}
			}

			$roles_sql = "JOIN $wpdb->usermeta AS roles
							ON $wpdb->users.ID = roles.user_id
						   AND roles.meta_key = '{$wpdb->prefix}capabilities'
						   AND ( $roles_sql )
						";
		}

		// there was a search query.
		if ( $term ) {

			// email only.
			if ( false !== strpos( $term, '@' ) ) {

				$query = "SELECT
							  ID AS id
							, user_email AS email
							, display_name AS name
						  FROM $wpdb->users
						  $roles_sql
						  WHERE user_email LIKE '%s'
						  ORDER BY display_name
						  LIMIT %d, %d;";

				$vars = array_merge(
					$vars,
					array(
						'%' . $term . '%',
						$start,
						$limit,
					)
				);

			} elseif ( false !== strpos( $term, ' ' ) ) {

				$term = explode( ' ', $term );

				$query = "SELECT
							  users.ID AS id
							, users.user_email AS email
							, users.display_name AS name
						  FROM $wpdb->users AS users
						  $roles_sql
						  LEFT JOIN wp_usermeta AS fname ON fname.user_id = users.ID
						  LEFT JOIN wp_usermeta AS lname ON lname.user_id = users.ID
						  WHERE ( fname.meta_key = 'first_name' AND fname.meta_value LIKE '%s' )
						  	AND ( lname.meta_key = 'last_name' AND lname.meta_value LIKE '%s' )
						  ORDER BY users.display_name
						  LIMIT %d, %d;";

				$vars = array_merge(
					$vars,
					array(
						'%' . $term[0] . '%', // first name.
						'%' . $term[1] . '%', // last name.
						$start,
						$limit,
					)
				);

				// search for login, display name, or email.
			} else {

				$query = "SELECT
							  ID AS id
							, user_email AS email
							, display_name AS name
						  FROM $wpdb->users
						  $roles_sql
						  WHERE
						  	user_email LIKE '%s'
						  	OR user_login LIKE '%s'
						  	OR display_name LIKE '%s'
						  ORDER BY display_name
						  LIMIT %d, %d;";

				$vars = array_merge(
					$vars,
					array(
						'%' . $term . '%',
						'%' . $term . '%',
						'%' . $term . '%',
						$start,
						$limit,
					)
				);

			}
		} else {

			$query = "SELECT
						  ID AS id
						, user_email AS email
						, display_name AS name
					  FROM $wpdb->users
					  $roles_sql
					  ORDER BY display_name
					  LIMIT %d, %d;";

			$vars = array_merge(
				$vars,
				array(
					$start,
					$limit,
				)
			);

		}

		$res = $wpdb->get_results( $wpdb->prepare( $query, $vars ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $enrolled_in ) {

			$checks = explode( ',', $enrolled_in );
			$checks = array_map( 'trim', $checks );

			// Loop through each user.
			foreach ( $res as $key => $user ) {

				// Loop through each check -- this is an OR relationship situation.
				foreach ( $checks as $id ) {

					// If the user is enrolled break to the next user, they can stay.
					if ( llms_is_user_enrolled( $user->id, $id ) ) {

						continue 2;

					}
				}

				// If we get here that means the user isn't enrolled in any of the check posts remove them from the results.
				unset( $res[ $key ] );
			}
		}

		if ( $not_enrolled_in ) {

			$checks = explode( ',', $enrolled_in );
			$checks = array_map( 'trim', $checks );

			// Loop through each user.
			foreach ( $res as $key => $user ) {

				// Loop through each check -- this is an OR relationship situation.
				// If the user is enrolled in any of the courses they need to be filtered out.
				foreach ( $checks as $id ) {

					// If the user is enrolled break remove them and break to the next user.
					if ( llms_is_user_enrolled( $user->id, $id ) ) {

						unset( $res[ $key ] );
						continue 2;

					}
				}
			}
		}

		echo json_encode(
			array(
				'items'   => $res,
				'more'    => count( $res ) === $limit,
				'success' => true,
			)
		);

		wp_die();
	}

	/**
	 * Start a Quiz Attempt.
	 *
	 * @since 3.9.0
	 * @since 3.16.4 Unknown.
	 * @since 6.4.0 Make sure attempts limit was not reached.
	 * @since 7.8.0 Use `$attempt->get( 'status' )` instead of the not existing `$attempt->get_status()` method and added `can_be_resumed` param.
	 *
	 * @param array $request $_POST data.
	 *                       required:
	 *                           (string) attempt_key
	 *                           or
	 *                           (int) quiz_id
	 *                           (int) lesson_id.
	 * @return WP_Error|array WP_Error on error or array containing html template of the first question.
	 */
	public static function quiz_start( $request ) {

		$err = new WP_Error();

		$student = llms_get_student();
		if ( ! $student ) {
			$err->add( 400, __( 'You must be logged in to take quizzes.', 'lifterlms' ) );
			return $err;
		}

		// Limit reached?
		if ( isset( $request['quiz_id'] ) && ! ( new LLMS_Quiz( $request['quiz_id'] ) )->is_open() ) {
			$err->add( 400, __( "You've reached the maximum number of attempts for this quiz.", 'lifterlms' ) );
			return $err;
		}

		$attempt = false;
		if ( ! empty( $request['attempt_key'] ) ) {
			$attempt = $student->quizzes()->get_attempt_by_key( $request['attempt_key'] );
		}

		if ( ! $attempt || 'new' !== $attempt->get( 'status' ) ) {

			if ( ! isset( $request['quiz_id'] ) || ! isset( $request['lesson_id'] ) ) {
				$err->add( 400, __( 'There was an error starting the quiz. Please return to the lesson and begin again.', 'lifterlms' ) );
				return $err;
			}

			// Mark the previous attempt as ended if it could be resumed but we're restarting instead.
			$previous_attempt_key = ( new LLMS_Quiz( $request['quiz_id'] ) )->get_student_last_attempt_key();
			if ( $previous_attempt_key ) {
				$previous_attempt = $student->quizzes()->get_attempt_by_key( $previous_attempt_key );
				if ( $previous_attempt && $previous_attempt->can_be_resumed() ) {
					$previous_attempt->end();
				}
			}

			$attempt = LLMS_Quiz_Attempt::init( absint( $request['quiz_id'] ), absint( $request['lesson_id'] ), $student->get( 'id' ) );

		}

		$question_id = $attempt->get_first_question();
		if ( ! $question_id ) {
			$err->add( 404, __( 'Unable to start quiz because the quiz does not contain any questions.', 'lifterlms' ) );
			return $err;
		}

		$attempt->start();
		$html = llms_get_template_ajax(
			'content-single-question.php',
			array(
				'attempt'  => $attempt,
				'question' => llms_get_post( $question_id ),
			)
		);

		$quiz  = $attempt->get_quiz();
		$limit = $quiz->has_time_limit() ? $quiz->get( 'time_limit' ) : false;

		return array(
			'attempt_key'    => $attempt->get_key(),
			'html'           => $html,
			'time_limit'     => $limit,
			'question_id'    => $question_id,
			'total'          => $attempt->get_count( 'questions' ),
			'can_be_resumed' => $attempt->can_be_resumed(),
		);
	}

	/**
	 * Resume a Quiz Attempt.
	 *
	 * @since 7.8.0
	 *
	 * @param array $request $_POST data.
	 *                       required:
	 *                           (string) attempt_key
	 * @return WP_Error|array WP_Error on error or array containing html template of the first question to be answered.
	 */
	public static function quiz_resume( $request ) {

		$err = new WP_Error();

		$student = llms_get_student();
		if ( ! $student ) {
			$err->add( 400, __( 'You must be logged in to take quizzes.', 'lifterlms' ) );
			return $err;
		}

		if ( ! isset( $request['attempt_key'] ) ) {
			$err->add( 400, __( 'Attempt key is required.', 'lifterlms' ) );
			return $err;
		}

		$attempt = $student->quizzes()->get_attempt_by_key( $request['attempt_key'] );

		if ( empty( $attempt ) ) {
			$err->add( 404, __( 'The requested attempt could not be found.', 'lifterlms' ) );
			return $err;
		}

		$quiz = $attempt->get_quiz();
		if ( empty( $quiz ) ) {
			$err->add( 400, __( 'No quiz found.', 'lifterlms' ) );
			return $err;
		}

		if (
			! $attempt->can_be_resumed() ||
			! $attempt->is_last_attempt()
		) {
			$err->add(
				400,
				__(
					'There was an error resuming the quiz. Please return to the lesson and begin again.',
					'lifterlms'
				)
			);
			return $err;
		}

		$question_ids = array_column( $attempt->get_questions(), 'id' );
		if ( ! $question_ids ) {
			$err->add(
				404,
				__( 'Unable to resume quiz because the quiz does not contain any questions.', 'lifterlms' )
			);
			return $err;
		}

		$question_id = $attempt->get( 'current_question_id' ) ? $attempt->get( 'current_question_id' ) : $attempt->get_next_question( null );

		if ( ! $question_id ) {
			return self::quiz_end( $request, $attempt );
		}

		$html = llms_get_template_ajax(
			'content-single-question.php',
			array(
				'attempt'  => $attempt,
				'question' => llms_get_post( $question_id ),
			)
		);

		return array(
			'attempt_key'    => $attempt->get_key(),
			'html'           => $html,
			'question_id'    => $question_id,
			'total'          => $attempt->get_count( 'questions' ),
			'question_ids'   => $question_ids,
			'can_be_resumed' => $attempt->can_be_resumed(),
		);
	}

	/**
	 * AJAX Quiz get question.
	 *
	 * @since 7.8.0
	 *
	 * @param array $request $_POST data.
	 * @return WP_Error|array
	 */
	public static function quiz_get_question( $request ) {
		$err = new WP_Error();

		$student = llms_get_student();
		if ( ! $student ) {
			$err->add( 400, __( 'You must be logged in to take quizzes.', 'lifterlms' ) );
			return $err;
		}

		$required = array( 'attempt_key', 'question_id' );
		foreach ( $required as $key ) {
			if ( empty( $request[ $key ] ) ) {
				$err->add( 400, __( 'Missing required parameters. Could not proceed.', 'lifterlms' ) );
				return $err;
			}
		}

		$attempt_key     = sanitize_text_field( $request['attempt_key'] );
		$question_id     = absint( $request['question_id'] );
		$student_quizzes = $student->quizzes();
		$attempt         = $student_quizzes->get_attempt_by_key( $attempt_key );

		// Don't allow the question to be retrieved if the attempt is not open or can't be resumed.
		if ( ! $attempt || ! $attempt->get_quiz()->is_open() || ( $attempt->get_quiz()->can_be_resumed() && ! $attempt->can_be_resumed() ) ) {
			$err->add( 500, __( 'There was an error retrieving the question. Please return to the lesson and try again.', 'lifterlms' ) );
			return $err;
		}

		$question_id = $attempt->get_question( $question_id );

		if ( ! $question_id ) {
			$err->add( 404, __( 'Cannot find the requested question id. Please return to the lesson and try again.', 'lifterlms' ) );
			return $err;
		}

		$html = llms_get_template_ajax(
			'content-single-question.php',
			array(
				'attempt'  => $attempt,
				'question' => llms_get_post( $question_id ),
			)
		);

		return array(
			'html'        => $html,
			'question_id' => $question_id,
		);
	}

	/**
	 * AJAX Quiz answer question.
	 *
	 * @since 3.9.0
	 * @since 3.27.0 Unknown.
	 * @since 6.4.0 Make sure attempts limit was not reached.
	 *
	 * @param array $request $_POST data.
	 * @return WP_Error|string
	 */
	public static function quiz_answer_question( $request ) {

		$err = new WP_Error();

		$student = llms_get_student();
		if ( ! $student ) {
			$err->add( 400, __( 'You must be logged in to take quizzes.', 'lifterlms' ) );
			return $err;
		}

		$required = array( 'attempt_key', 'question_id', 'question_type' );
		foreach ( $required as $key ) {
			if ( ! isset( $request[ $key ] ) ) {
				$err->add( 400, __( 'Missing required parameters. Could not proceed.', 'lifterlms' ) );
				return $err;
			}
		}

		$attempt_key = sanitize_text_field( $request['attempt_key'] );
		$question_id = absint( $request['question_id'] );
		$answer      = array_map( 'stripslashes_deep', isset( $request['answer'] ) ? $request['answer'] : array() );

		$student_quizzes = $student->quizzes();
		$attempt         = $student_quizzes->get_attempt_by_key( $attempt_key );
		if ( ! $attempt || 'incomplete' !== $attempt->get( 'status' ) || ( $attempt->get_quiz()->can_be_resumed() && ! $attempt->can_be_resumed() ) ) {
			$err->add( 500, __( 'There was an error recording your answer. Please return to the lesson and begin again.', 'lifterlms' ) );
			return $err;
		}

		/**
		 * Check limit not reached.
		 *
		 * First check whether the quiz is open (so to leverage the `llms_quiz_is_open` filter ),
		 * if not, check also for remaining attempts.
		 *
		 * At this point the current attempt has already been counted (maybe the last allowed),
		 * so we check that the remaining attempt is just greater than -1.
		 */
		$quiz_id = $attempt->get( 'quiz_id' );
		if ( ! ( new LLMS_Quiz( $quiz_id ) )->is_open() &&
				$student_quizzes->get_attempts_remaining_for_quiz( $quiz_id, true ) < 0 ) {
			$err->add( 400, __( "You've reached the maximum number of attempts for this quiz.", 'lifterlms' ) );
			return $err;
		}

		// record the answer.
		$attempt->answer_question( $question_id, $answer );

		if ( isset( $request['via_previous_question'] ) ) {
			$attempt->set( 'current_question_id', $attempt->get_previous_question( $question_id ) );
			$attempt->save();

			return;
		}

		if ( isset( $request['via_exit_quiz'] ) ) {
			$attempt->set( 'current_question_id', $question_id );
			$attempt->save();

			return;
		}

		// get the next question.
		$question_id = $attempt->get_next_question( $question_id );

		// return html for the next question.
		if ( $question_id ) {
			$attempt->set( 'current_question_id', absint( $question_id ) );
			$attempt->save();

			$html = llms_get_template_ajax(
				'content-single-question.php',
				array(
					'attempt'  => $attempt,
					'question' => llms_get_post( $question_id ),
				)
			);

			return array(
				'html'        => $html,
				'question_id' => $question_id,
			);

		} else {

			return self::quiz_end( $request, $attempt );

		}
	}

	/**
	 * End a quiz attempt.
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param array                  $request $_POST data.
	 * @param LLMS_Quiz_Attempt|null $attempt The quiz attempt.
	 * @return array
	 */
	public static function quiz_end( $request, $attempt = null ) {

		$err = new WP_Error();

		if ( ! $attempt ) {

			$student = llms_get_student();
			if ( ! $student ) {
				$err->add( 400, __( 'You must be logged in to take quizzes.', 'lifterlms' ) );
				return $err;
			}

			if ( ! isset( $request['attempt_key'] ) ) {
				$err->add( 400, __( 'Missing required parameters. Could not proceed.', 'lifterlms' ) );
				return $err;
			}

			$attempt = $student->quizzes()->get_attempt_by_key( sanitize_text_field( $request['attempt_key'] ) );

			if ( ! $attempt ) {
				$err->add( 404, __( 'The requested attempt could not be found.', 'lifterlms' ) );
				return $err;
			}
		}

		// Record the attempt's completion.
		$attempt->end();

		// Setup a redirect.
		$url = add_query_arg(
			array(
				'attempt_key' => $attempt->get_key(),
			),
			get_permalink( $attempt->get( 'quiz_id' ) )
		);

		return array(
			/**
			 * Filter the quiz redirect URL on completion.
			 *
			 * @since Unknown
			 *
			 * @param string            $url     The quiz redirect URL on completion.
			 * @param LLMS_Quiz_Attempt $attempt The quiz attempt.
			 */
			'redirect' => apply_filters( 'llms_quiz_complete_redirect', $url, $attempt ),
		);
	}

	/**
	 * Remove a coupon from an order during checkout
	 *
	 * @since 3.0.0
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function remove_coupon_code( $request ) {

		llms()->session->set( 'llms_coupon', false );

		$plan = new LLMS_Access_Plan( $request['plan_id'] );

		ob_start();
		llms_get_template( 'checkout/form-coupon.php' );
		$coupon_html = ob_get_clean();

		ob_start();
		llms_get_template(
			'checkout/form-gateways.php',
			array(
				'coupon'           => false,
				'gateways'         => llms()->payment_gateways()->get_enabled_payment_gateways(),
				'selected_gateway' => llms()->payment_gateways()->get_default_gateway(),
				'plan'             => $plan,
			)
		);
		$gateways_html = ob_get_clean();

		ob_start();
		llms_get_template(
			'checkout/form-summary.php',
			array(
				'coupon'  => false,
				'plan'    => $plan,
				'product' => $plan->get_product(),
			)
		);
		$summary_html = ob_get_clean();

		return array(
			'coupon_html'   => $coupon_html,
			'gateways_html' => $gateways_html,
			'summary_html'  => $summary_html,
		);
	}

	/**
	 * Handle Select2 Search boxes for WordPress Posts by Post Type and Post Status.
	 *
	 * @since 3.0.0
	 * @since 3.32.0 Updated to use llms_filter_input().
	 * @since 3.32.0 Posts can be queried by post status(es) via the `$_POST['post_statuses']`.
	 *               By default only the published posts will be queried.
	 * @since 3.37.2 Posts can be 'filtered' by instructor via the `$_POST['instructor_id']`.
	 * @since 5.5.0 Do not encode quotes when sanitizing search term.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public static function select2_query_posts() {

		global $wpdb;

		// Grab the search term if it exists.
		$term = llms_filter_input_sanitize_string( INPUT_POST, 'term', array( FILTER_FLAG_NO_ENCODE_QUOTES ) );

		// Get the page.
		$page = llms_filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );

		// Get post type(s).
		$post_type        = sanitize_text_field( llms_filter_input_sanitize_string( INPUT_POST, 'post_type' ) );
		$post_types_array = explode( ',', $post_type );
		foreach ( $post_types_array as &$str ) {
			$str = "'" . esc_sql( trim( $str ) ) . "'";
		}
		$post_types = implode( ',', $post_types_array );

		// Get post status(es).
		$post_statuses       = llms_filter_input_sanitize_string( INPUT_POST, 'post_statuses' );
		$post_statuses       = empty( $post_statuses ) ? 'publish' : $post_statuses;
		$post_statuses_array = explode( ',', $post_statuses );
		foreach ( $post_statuses_array as &$str ) {
			$str = "'" . esc_sql( trim( $str ) ) . "'";
		}
		$post_statuses = implode( ',', $post_statuses_array );

		// Filter posts (llms posts) by instructor ID.
		$instructor_id = llms_filter_input( INPUT_POST, 'instructor_id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $instructor_id ) ) {
			$serialized_iid = serialize(
				array(
					'id' => absint( $instructor_id ),
				)
			);
			$serialized_iid = str_replace( array( 'a:1:{', '}' ), '', $serialized_iid );

			$join = $wpdb->prepare(
				" JOIN $wpdb->postmeta AS m ON p.ID = m.post_id AND m.meta_key = '_llms_instructors' AND m.meta_value LIKE %s",
				'%' . $wpdb->esc_like( $serialized_iid ) . '%'
			);
		} else {
			$join = '';
		}

		$limit = 30;
		$start = $limit * $page;

		if ( $term ) {
			$like = " AND post_title LIKE '%s'";
			$vars = array( '%' . $term . '%', $start, $limit );
		} else {
			$like = '';
			$vars = array( $start, $limit );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID as ID, p.post_title as post_title, p.post_type as post_type
			 FROM $wpdb->posts as p
			 $join
			 WHERE p.post_type IN ( $post_types )
			   AND p.post_status IN ( $post_statuses )
			       $like
			 ORDER BY post_title
			 LIMIT %d, %d
			",
				$vars
			) // phpcs:ignore -- The number of params is correct, $vars is an array of two elements.
		);// no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$items = array();

		$grouping = ( count( $post_types_array ) > 1 );

		foreach ( $posts as $post ) {

			$item = array(
				'id'   => $post->ID,
				'name' => $post->post_title . ' (' . __( 'ID#', 'lifterlms' ) . ' ' . $post->ID . ')',
			);

			if ( $grouping ) {

				// Setup an object for the optgroup if it's not already set up.
				if ( ! isset( $items[ $post->post_type ] ) ) {
					$obj                       = get_post_type_object( $post->post_type );
					$items[ $post->post_type ] = array(
						'label' => $obj->labels->name,
						'items' => array(),
					);
				}

				$items[ $post->post_type ]['items'][] = $item;

			} else {

				$items[] = $item;

			}
		}

		echo json_encode(
			array(
				'items'   => $items,
				'more'    => count( $items ) === $limit,
				'success' => true,
			)
		);
		wp_die();
	}

	/**
	 * Add or remove a student from a course or membership.
	 *
	 * @since 3.0.0
	 * @since 3.33.0 Handle the delete enrollment request and make sure the $request['post_id'] is not empty.
	 *               Also always return either a WP_Error on failure or a "success" array on action performed.
	 * @since 3.37.14 Use strict comparison.
	 *
	 * @param array $request $_POST data.
	 * @return (WP_Error|array)
	 */
	public static function update_student_enrollment( $request ) {

		if ( empty( $request['student_id'] ) || empty( $request['status'] ) || empty( $request['post_id'] ) ) {
			return new WP_Error( 400, __( 'Missing required parameters', 'lifterlms' ) );
		}

		if ( ! in_array( $request['status'], array( 'add', 'remove', 'delete' ), true ) ) {
			return new WP_Error( 400, __( 'Invalid status', 'lifterlms' ) );
		}

		$student_id = intval( $request['student_id'] );
		$post_id    = intval( $request['post_id'] );

		switch ( $request['status'] ) {
			case 'add':
				$res = llms_enroll_student( $student_id, $post_id, 'admin_' . get_current_user_id() );
				break;

			case 'remove':
				$res = llms_unenroll_student( $student_id, $post_id, 'cancelled', 'any' );
				break;

			case 'delete':
				$res = llms_delete_student_enrollment( $student_id, $post_id, 'any' );
				break;
		}

		if ( ! $res ) {
			// Translators: %s = action add|remove|delete.
			return new WP_Error( 400, sprintf( __( 'Action "%1$s" failed. Please try again', 'lifterlms' ), $request['status'] ) );
		}

		return array(
			'success' => true,
		);
	}

	/**
	 * Validate a Coupon via the Checkout Form
	 *
	 * @since 3.0.0
	 * @since 3.39.0 Minor changes to code for readability with no changes to function behavior.
	 * @since 4.21.1 Sanitize user-submitted coupon code before outputting in error messages.
	 *
	 * @param array $request $_POST data.
	 * @return array|WP_Error On success, returns an array containing HTML parts used to update the interface of the checkout screen.
	 *                        On error, returns an error object with details of the encountered error.
	 */
	public static function validate_coupon_code( $request ) {

		$error = new WP_Error();

		$request['code'] = ! empty( $request['code'] ) ? sanitize_text_field( $request['code'] ) : '';

		if ( empty( $request['code'] ) ) {

			$error->add( 'error', __( 'Please enter a coupon code.', 'lifterlms' ) );

		} elseif ( empty( $request['plan_id'] ) ) {

			$error->add( 'error', __( 'Please enter a plan ID.', 'lifterlms' ) );

		} else {

			$cid = llms_find_coupon( $request['code'] );

			if ( ! $cid ) {

				// Translators: %s = coupon code.
				$error->add( 'error', sprintf( __( 'Coupon code "%s" not found.', 'lifterlms' ), $request['code'] ) );

			} else {

				$coupon = new LLMS_Coupon( $cid );
				$valid  = $coupon->is_valid( $request['plan_id'] );

				if ( is_wp_error( $valid ) ) {

					$error = $valid;

				} else {

					llms()->session->set(
						'llms_coupon',
						array(
							'plan_id'   => $request['plan_id'],
							'coupon_id' => $coupon->get( 'id' ),
						)
					);

					$plan = new LLMS_Access_Plan( $request['plan_id'] );

					ob_start();
					llms_get_template(
						'checkout/form-coupon.php',
						array(
							'coupon' => $coupon,
						)
					);
					$coupon_html = ob_get_clean();

					ob_start();
					llms_get_template(
						'checkout/form-gateways.php',
						array(
							'coupon'           => $coupon,
							'gateways'         => llms()->payment_gateways()->get_enabled_payment_gateways(),
							'selected_gateway' => llms()->payment_gateways()->get_default_gateway(),
							'plan'             => $plan,
						)
					);
					$gateways_html = ob_get_clean();

					ob_start();
					llms_get_template(
						'checkout/form-summary.php',
						array(
							'coupon'  => $coupon,
							'plan'    => $plan,
							'product' => $plan->get_product(),
						)
					);
					$summary_html = ob_get_clean();

					return array(
						'code'          => $coupon->get( 'title' ),
						'coupon_html'   => $coupon_html,
						'gateways_html' => $gateways_html,
						'summary_html'  => $summary_html,
					);

				}
			}
		}

		return $error;
	}

	/**
	 * Create course's section.
	 *
	 * @since Unknown
	 * @deprecated 5.7.0 There is not a replacement.
	 *
	 * @param array $request $_POST data.
	 * @return string
	 */
	public static function create_section( $request ) {

		llms_deprecated_function( __METHOD__, '5.7.0' );
		$section_id = LLMS_Post_Handler::create_section( $request['post_id'], $request['title'] );

		$html = LLMS_Meta_Box_Course_Outline::section_tile( $section_id );

		return $html;
	}

	/**
	 * Get course's sections
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return LLMS_Section[]
	 */
	public static function get_course_sections( $request ) {

		$course   = new LLMS_Course( $request['post_id'] );
		$sections = $course->get_sections( 'posts' );

		return $sections;
	}

	/**
	 * Get a course's section
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return LLMS_Section
	 */
	public static function get_course_section( $request ) {

		return new LLMS_Section( $request['section_id'] );
	}

	/**
	 * Update a course's section
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return (array|void) If section updated returns an array of the type:
	 *                      id    => {post id}
	 *                      title => {new title}
	 */
	public static function update_course_section( $request ) {

		$section = new LLMS_Section( $request['section_id'] );
		return $section->set_title( $request['title'] );
	}

	/**
	 * Create course's lesson.
	 *
	 * @since Unknown
	 * @deprecated 5.7.0 There is not a replacement.
	 *
	 * @param array $request $_POST data.
	 * @return string
	 */
	public static function create_lesson( $request ) {

		llms_deprecated_function( __METHOD__, '5.7.0' );
		$lesson_id = LLMS_Post_Handler::create_lesson(
			$request['post_id'],
			$request['section_id'],
			$request['title'],
			$request['excerpt']
		);

		$html = LLMS_Meta_Box_Course_Outline::lesson_tile( $lesson_id, $request['section_id'] );

		return $html;
	}

	/**
	 * Get the list of options for the lesson's select
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function get_lesson_options_for_select( $request ) {

		return LLMS_Post_Handler::get_lesson_options_for_select_list();
	}

	/**
	 * Add a lesson to a course
	 *
	 * @since Unknown
	 * @deprecated 5.7.0 There is not a replacement.
	 *
	 * @param array $request $_POST data.
	 * @return string
	 */
	public static function add_lesson_to_course( $request ) {

		llms_deprecated_function( __METHOD__, '5.7.0' );
		$lesson_id = LLMS_Lesson_Handler::assign_to_course( $request['post_id'], $request['section_id'], $request['lesson_id'] );

		$html = LLMS_Meta_Box_Course_Outline::lesson_tile( $lesson_id, $request['section_id'] );

		return $html;
	}

	/**
	 * Get a course's lesson
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function get_course_lesson( $request ) {

		$l = new LLMS_Lesson( $request['lesson_id'] );

		return array(
			'id'      => $l->get( 'id' ),
			'title'   => $l->get( 'title' ),
			'excerpt' => $l->get( 'excerpt' ),
		);
	}

	/**
	 * Update course's lesson
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function update_course_lesson( $request ) {

		$post_data = array(
			'title'   => $request['title'],
			'excerpt' => $request['excerpt'],
		);

		$lesson = new LLMS_Lesson( $request['lesson_id'] );

		return $lesson->update( $post_data );
	}

	/**
	 * Remove a lesson from a course
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function remove_course_lesson( $request ) {

		$post_data = array(
			'parent_course'  => '',
			'parent_section' => '',
			'order'          => '',
		);

		$lesson = new LLMS_Lesson( $request['lesson_id'] );

		return $lesson->update( $post_data );
	}

	/**
	 * Delete a course's section
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return (WP_Post|false|null) Post data on success, false or null on failure.
	 */
	public static function delete_course_section( $request ) {

		$section = new LLMS_Section( $request['section_id'] );
		return $section->delete();
	}

	/**
	 * Update course's sections order
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return (array|null)
	 */
	public static function update_section_order( $request ) {

		$updated_data;

		foreach ( $request['sections'] as $key => $value ) {

			$section              = new LLMS_Section( $key );
			$updated_data[ $key ] = $section->update(
				array(
					'order' => $value,
				)
			);

		}

		return $updated_data;
	}

	/**
	 * Update section's lessons order
	 *
	 * @since Unknown
	 *
	 * @param array $request $_POST data.
	 * @return (array|null)
	 */
	public static function update_lesson_order( $request ) {

		$updated_data;

		foreach ( $request['lessons'] as $key => $value ) {

			$lesson               = new LLMS_Lesson( $key );
			$updated_data[ $key ] = $lesson->update(
				array(
					'parent_section' => $value['parent_section'],
					'order'          => $value['order'],
				)
			);

		}

		return $updated_data;
	}

	/**
	 * "API" for the Admin Builder.
	 *
	 * @since 3.13.0
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function llms_builder( $request ) {

		return LLMS_Admin_Builder::handle_ajax( $request );
	}

	/**
	 * Save autoenroll courses list for a Membership
	 *
	 * @since 3.30.0
	 *
	 * @param array $request $_POST data.
	 * @return null|true
	 */
	public static function llms_save_membership_autoenroll_courses( $request ) {

		// Missing required fields.
		if ( empty( $request['post_id'] ) || ! isset( $request['courses'] ) ) {
			return;
		}

		// Not a membership.
		$membership = llms_get_post( $request['post_id'] );
		if ( ! $membership || ! is_a( $membership, 'LLMS_Membership' ) ) {
			return;
		}

		$courses = array_map( 'absint', (array) $request['courses'] );
		$membership->add_auto_enroll_courses( $courses, true );

		return true;
	}

	/**
	 * AJAX handler for creating and updating access plans via the metabox on courses & memberships
	 *
	 * @since 3.29.0
	 * @since 3.33.1 Use `wp_unslash()` before inserting access plan data.
	 *
	 * @param array $request $_POST data.
	 * @return array
	 */
	public static function llms_update_access_plans( $request ) {

		if ( empty( $request['plans'] ) || ! is_array( $request['plans'] ) || empty( $request['post_id'] ) ) {
			return new WP_Error( 'error', __( 'Missing Required Parameters.', 'lifterlms' ) );
		}

		$metabox       = new LLMS_Meta_Box_Product();
		$post_id       = absint( $request['post_id'] );
		$metabox->post = get_post( $post_id );

		$errors = array();

		foreach ( $request['plans'] as $raw_plan_data ) {

			if ( empty( $raw_plan_data ) ) {
				continue;
			}

			$raw_plan_data = wp_unslash( $raw_plan_data );

			// Ensure we can switch plans that used to be paid to free.
			if ( isset( $raw_plan_data['is_free'] ) && llms_parse_bool( $raw_plan_data['is_free'] ) && ! isset( $raw_plan_data['price'] ) ) {
				$raw_plan_data['price'] = 0;
			}

			$raw_plan_data['product_id'] = $post_id;

			// retained filter for backwards compat.
			$raw_plan_data = apply_filters( 'llms_access_before_save_plan', $raw_plan_data, $metabox );

			$plan = llms_insert_access_plan( $raw_plan_data );
			if ( is_wp_error( $plan ) ) {
				$errors[ $raw_plan_data['menu_order'] ] = $plan;
			} else {
				// retained hook for backwards compat.
				do_action( 'llms_access_plan_saved', $plan, $raw_plan_data, $metabox );
			}
		}

		return array(
			'errors' => $errors,
			'html'   => $metabox->get_html(),
		);
	}

	/**
	 * AJAX handler for persisting tracking events.
	 *
	 * @since 3.37.14
	 *
	 * @param array $request $_POST data.
	 * @return array|WP_Error
	 */
	public static function persist_tracking_events( $request ) {

		if ( empty( $request['llms-tracking'] ) ) {
			return new WP_Error( 'error', __( 'Missing tracking data.', 'lifterlms' ) );
		}

		$success = llms()->events()->store_tracking_events( wp_unslash( $request['llms-tracking'] ) );

		if ( ! is_wp_error( $success ) ) {
			$success = array(
				'success' => true,
			);
		}

		return $success;
	}
}

new LLMS_AJAX_Handler();
