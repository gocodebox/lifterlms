<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Privacy Exporter functions
 * @since    3.18.0
 * @version  3.18.0
 */
class LLMS_Privacy_Exporters extends LLMS_Privacy {

	/**
	 * Export student achievement data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function achievement_data( $email_address, $page ) {

		$data = array();

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return self::get_return( $data );
		}

		$achievements = self::get_student_achievements( $student );
		if ( $achievements ) {

			$group_label = __( 'Achievements', 'lifterlms' );
			foreach ( $achievements as $achievement ) {

				$data[] = array(
					'group_id' => 'lifterlms_achievements',
					'group_label' => $group_label,
					'item_id' => sprintf( 'achievement-%d', $achievement->get( 'id' ) ),
					'data' => self::get_achievement_data( $achievement ),
				);

			}
		}

		return self::get_return( $data );

	}

	/**
	 * Export student certificate data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function certificate_data( $email_address, $page ) {

		$data = array();

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return self::get_return( $data );
		}

		$certs = self::get_student_certificates( $student );
		if ( $certs ) {

			$group_label = __( 'Certificates', 'lifterlms' );
			foreach ( $certs as $cert ) {

				$data[] = array(
					'group_id' => 'lifterlms_certificates',
					'group_label' => $group_label,
					'item_id' => sprintf( 'certificate-%d', $cert->get( 'id' ) ),
					'data' => self::get_certificate_data( $cert ),
				);

			}
		}

		return self::get_return( $data );

	}

	/**
	 * Get data for a certificate
	 * @param    obj     $achievement  LLMS_User_Certificate
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_achievement_data( $achievement ) {

		$data = array();

		$data[] = array(
			'name' => __( 'Title', 'lifterlms' ),
			'value' => $achievement->get( 'title' ),
		);

		$data[] = array(
			'name' => __( 'Description', 'lifterlms' ),
			'value' => $achievement->get( 'content' ),
		);

		$data[] = array(
			'name' => __( 'Earned Date', 'lifterlms' ),
			'value' => $achievement->get_earned_date( 'Y-m-d H:i:s' ),
		);

		$data[] = array(
			'name' => __( 'Image', 'lifterlms' ),
			'value' => $achievement->get_image(),
		);

		return $data;

	}



	/**
	 * Get data for a certificate
	 * @param    obj     $cert  LLMS_User_Certificate
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_certificate_data( $cert ) {

		$data = array();

		$title = $cert->get( 'certificate_title' );

		$filename = LLMS()->certificates()->get_export( $cert->get( 'id' ), true );
		if ( ! is_wp_error( $filename ) ) {
			$title = '<a href="certificates/' . basename( $filename ) . '">' . $title . '</a>';
		}

		$data[] = array(
			'name' => __( 'Title', 'lifterlms' ),
			'value' => $title,
		);

		$data[] = array(
			'name' => __( 'Earned Date', 'lifterlms' ),
			'value' => $cert->get_earned_date( 'Y-m-d H:i:s' ),
		);

		return $data;

	}

	/**
	 * Get an array of enrollment data for a course or membership
	 * @param    int     $post_id           WP Post ID of course or membership
	 * @param    obj     $student           LLMS_Student
	 * @param    obj     $post_type_object  WP post type object
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_enrollment_data( $post_id, $student, $post_type_object ) {

		$data = array();

		$data[] = array(
			/* translators: %s = post type singular name label (Course or Membership) */
			'name'  => sprintf( __( '%s Title', 'lifterlms' ), $post_type_object->labels->singular_name ),
			'value' => get_the_title( $post_id ),
		);

		$data[] = array(
			'name' => __( 'Enrollement Status', 'lifterlms' ),
			'value' => llms_get_enrollment_status_name( $student->get_enrollment_status( $post_id ) ),
		);

		$data[] = array(
			'name' => __( 'Enrollement Date', 'lifterlms' ),
			'value' => $student->get_enrollment_date( $post_id, 'enrolled', 'Y-m-d H:i:s' ),
		);

		if ( 'course' === $post_type_object->name ) {

			$data[] = array(
				'name' => __( 'Last Activity', 'lifterlms' ),
				'value' => $student->get_enrollment_date( $post_id, 'updated', 'Y-m-d H:i:s' ),
			);

			$progress = $student->get_progress( $post_id, 'course' );
			if ( is_numeric( $progress ) ) {
				$progress .= '%';
			}
			$data[] = array(
				'name' => __( 'Progress', 'lifterlms' ),
				'value' => $progress,
			);

			$grade = $student->get_grade( $post_id );
			if ( is_numeric( $grade ) ) {
				$grade .= '%';
			}
			$data[] = array(
				'name' => __( 'Grade', 'lifterlms' ),
				'value' => $grade,
			);

		}

		return apply_filters( 'llms_privacy_export_enrollment_data', $data, $post_id, $student, $post_type_object );

	}

	/**
	 * Retrieve export data for a single order
	 * @param    obj     $order  LLMS_Order
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_order_data( $order ) {

		$data = array();

		$props = self::get_order_data_props( 'export' );

		foreach ( $props as $prop => $name ) {

			$value = apply_filters( 'llms_privacy_export_order_data_prop_value', $order->get( $prop ), $prop, $order );

			if ( $value ) {
				$data[] = array(
					'name'  => $name,
					'value' => $value,
				);
			}
		}

		$transactions = $order->get_transactions( array(
			'per_page' => 500,
		) );
		if ( $transactions['transactions'] ) {
			$txns = array();
			foreach ( $transactions['transactions'] as $txn ) {
				$txns[] = sprintf( '%1$s &mdash; %2$s (#%3$d)', $txn->get( 'date' ), $txn->get_price( 'amount' ), $txn->get( 'id' ) );
			}
			$data[] = array(
				'name' => __( 'Transactions', 'lifterlms' ),
				'value' => implode( '<br>', $txns ),
			);
		}

		return apply_filters( 'llms_privacy_export_order_data', $data, $order );
	}

	/**
	 * Get export data for a single quiz attempt
	 * @param    obj     $attempt  LLMS_Quiz_Attempt
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_quiz_attempt_data( $attempt ) {

		$data = array();

		$quiz = $attempt->get_quiz();
		if ( $quiz ) {
			$data[] = array(
				'name' => __( 'Title', 'lifterlms' ),
				'value' => $quiz->get( 'title' ),
			);
		}

		$data[] = array(
			'name' => __( 'Attempt ID', 'lifterlms' ),
			'value' => $attempt->get_key(),
		);

		$data[] = array(
			'name' => __( 'Attempt Number', 'lifterlms' ),
			'value' => $attempt->get( 'attempt' ),
		);

		$data[] = array(
			'name' => __( 'Status', 'lifterlms' ),
			'value' => $attempt->l10n( 'status' ),
		);

		$grade = $attempt->get( 'grade' );
		$data[] = array(
			'name' => __( 'Grade', 'lifterlms' ),
			'value' => is_numeric( $grade ) ? $grade . '%' : '&ndash;',
		);

		return $data;
	}

	/**
	 * Return export data to an exporter
	 * @param    array      $data  array of data
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_return( $data = array(), $done = true ) {
		return array(
			'data' => $data,
			'done' => $done,
		);
	}

	/**
	 * Get student data to export for a user
	 * @param    LLMS_Student  $student
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_student_data( $student ) {

		$data = array();

		$props = self::get_student_data_props();

		foreach ( $props as $prop => $name ) {

			$value = apply_filters( 'llms_privacy_export_student_data_prop_value', $student->get( $prop ), $prop, $student );

			if ( $value ) {
				$data[] = array(
					'name'  => $name,
					'value' => $value,
				);
			}
		}

		return apply_filters( 'llms_privacy_export_student_data', $data, $student );

	}

	/**
	 * Export student course data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function course_data( $email_address, $page ) {
		return self::enrollment_data( $email_address, $page, 'course' );
	}

	/**
	 * General exporter for handling course and membership enrollment data
	 * @param    string     $email_address  Requested user's email address
	 * @param    int        $page           process page number
	 * @param    string     $post_type      name of the post type
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function enrollment_data( $email_address, $page, $post_type ) {

		$data = array();

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return self::get_return( $data );
		}

		$enrollments = self::get_student_enrollments( $student, $page, $post_type );
		if ( $enrollments['results'] ) {

			$post_type_obj = get_post_type_object( $post_type );
			$group_id = 'lifterlms_' . $post_type;

			foreach ( $enrollments['results'] as $post_id ) {

				$data[] = array(
					'group_id' => $group_id,
					'group_label' => $post_type_obj->labels->name,
					'item_id' => sprintf( '%1$s-%2$d', $post_type, $post_id ),
					'data' => self::get_enrollment_data( $post_id, $student, $post_type_obj ),
				);

			}
		}

		return self::get_return( $data, $enrollments['done'] );

	}

	/**
	 * Add files to the zip file for a data export request
	 * Adds certificate files into the /certificates/ directory within the archive
	 * @param    string     $archive_pathname      full path to the zip archive
	 * @param    string     $archive_url           full uri to the zip archive
	 * @param    string     $html_report_pathname  full path to the .html file within the archive
	 * @param    int        $request_id            WP Post ID of the export request
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function maybe_add_export_files( $archive_pathname, $archive_url, $html_report_pathname, $request_id ) {

		if ( ! class_exists( 'ZipArchive' ) ) {
			return;
		}

		$request = wp_get_user_request_data( $request_id );
		$student = self::get_student_by_email( $request->email );

		if ( ! $student ) {
			return;
		}

		$certs = self::get_student_certificates( $student );
		if ( ! $certs ) {
			return;
		}

		$zip = new ZipArchive();
		$delete = array();
		if ( true === $zip->open( $archive_pathname ) ) {
			foreach ( $certs as $cert ) {
				$filepath = LLMS()->certificates()->get_export( $cert->get( 'id' ), true );
				$delete[ $cert->certificate_id ] = $filepath;
				if ( is_wp_error( $filepath ) ) {
					continue;
				}
				$zip->addFile( $filepath, '/certificates/' . basename( $filepath ) );
			}
		}

		$zip->close();

		// cleanup all files
		foreach ( $delete as $id => $path ) {
			wp_delete_file( $path );
			delete_post_meta( $id, '_llms_export_filepath' );
		}

	}

	/**
	 * Export student membership data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function membership_data( $email_address, $page ) {
		return self::enrollment_data( $email_address, $page, 'llms_membership' );
	}

	/**
	 * Export student certificate data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function order_data( $email_address, $page ) {

		$data = array();

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return self::get_return( $data );
		}

		$orders = self::get_student_orders( $student, $page );

		$group_label = __( 'Orders', 'lifterlms' );
		foreach ( $orders['orders'] as $order ) {

			$data[] = array(
				'group_id' => 'lifterlms_orders',
				'group_label' => $group_label,
				'item_id' => sprintf( 'order-%d', $order->get( 'id' ) ),
				'data' => self::get_order_data( $order ),
			);

		}

		return self::get_return( $data, $orders['done'] );

	}

	/**
	 * Export student data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   [type]
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function student_data( $email_address, $page ) {

		$data = array();

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return self::get_return( $data );
		}

		$data[] = array(
			'group_id' => 'lifterlms_student',
			'group_label' => __( 'Personal Information', 'lifterlms' ),
			'item_id' => sprintf( 'student-%d', $student->get( 'id' ) ),
			'data' => self::get_student_data( $student ),
		);

		return self::get_return( $data );

	}

	/**
	 * Export quiz attempt data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function quiz_data( $email_address, $page ) {

		$data = array();

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return self::get_return( $data );
		}

		$query = self::get_student_quizzes( $student, $page );
		$done = true;
		if ( $query->has_results() ) {

			$group_label = __( 'Quiz Attempts', 'lifterlms' );
			foreach ( $query->get_attempts() as $attempt ) {

				$data[] = array(
					'group_id' => 'lifterlms_quizzes',
					'group_label' => $group_label,
					'item_id' => sprintf( 'order-%d', $attempt->get( 'id' ) ),
					'data' => self::get_quiz_attempt_data( $attempt ),
				);

			}

			$done = $query->is_last_page();

		}

		return self::get_return( $data, $done );

	}

}
