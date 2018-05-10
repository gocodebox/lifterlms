<?php
defined( 'ABSPATH' ) || exit;

/**
 * Main Privacy Class
 * Hooks into WP Core data exporters and erasers to export / erase LifterLMS data
 * @since    [version]
 * @version  [version]
 */
class LLMS_Privacy extends LLMS_Abstract_Privacy {

	/**
	 * Constructor
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		parent::__construct( __( 'LifterLMS', 'lifterlms' ) );
		$this->add_exporter( 'lifterlms-student-data', __( 'Student Data', 'lifterlms' ), array( $this, 'exporter_student_data' ) );
		$this->add_exporter( 'lifterlms-course-data', __( 'Course Data', 'lifterlms' ), array( $this, 'exporter_course_data' ) );
		$this->add_exporter( 'lifterlms-membership-data', __( 'Membership Data', 'lifterlms' ), array( $this, 'exporter_membership_data' ) );
		$this->add_exporter( 'lifterlms-certificate-data', __( 'Certificate Data', 'lifterlms' ), array( $this, 'exporter_certificate_data' ) );

		add_action( 'wp_privacy_personal_data_export_file_created', array( $this, 'maybe_add_export_files' ), 100, 4 );

	}

	/**
	 * Export student certificate data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function exporter_certificate_data( $email_address, $page ) {

		$export = array();
		$student = $this->get_student_by_email( $email_address );

		if ( $student ) {

			$certs = $student->get_certificates( 'updated_date', 'DESC', 'certificates' );
			if ( $certs ) {

				$group_label = __( 'Certificate Data', 'lifterlms' );
				foreach ( $certs as $cert ) {

					$export[] = array(
						'group_id' => 'lifterlms_certificates',
						'group_label' => $group_label,
						'item_id' => sprintf( 'certificate-%d', $cert->get( 'id' ) ),
						'data' => $this->get_certificate_data( $cert ),
					);

				}

			}

		}

		return array(
			'data' => $export,
			'done' => true,
		);

	}

	/**
	 * Export student course data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function exporter_course_data( $email_address, $page ) {
		return $this->exporter_enrollment_data( $email_address, $page, 'course' );
	}

	/**
	 * General exporter for handling course and membership enrollment data
	 * @param    string     $email_address  Requested user's email address
	 * @param    int        $page           process page number
	 * @param    string     $post_type      name of the post type
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function exporter_enrollment_data( $email_address, $page, $post_type ) {

		$done = true;
		$export = array();

		$student = $this->get_student_by_email( $email_address );
		if ( $student ) {

			$limit = 250;

			$enrollments = $student->get_enrollments( $post_type, array(
				'limit' => $limit,
				'skip' => ( $page - 1 ) * $limit
			) );
			$done = ( ! $enrollments['more'] );

			if ( $enrollments['results'] ) {

				$post_type_obj = get_post_type_object( $post_type );
				/* translators: %s = post type singular name label (Course or Membership) */
				$group_label = sprintf( __( '%s Data', 'lifterlms' ), $post_type_obj->labels->singular_name );
				$group_id = 'lifterlms_' . $post_type;

				foreach ( $enrollments['results'] as $post_id ) {

					$export[] = array(
						'group_id' => $group_id,
						'group_label' => $group_label,
						'item_id' => sprintf( '%1$s-%2$d', $post_type, $post_id ),
						'data' => $this->get_enrollment_data( $post_id, $student, $post_type_obj ),
					);

				}

			}

		}

		return array(
			'data' => $export,
			'done' => $done,
		);

	}

	/**
	 * Export student membership data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function exporter_membership_data( $email_address, $page ) {
		return $this->exporter_enrollment_data( $email_address, $page, 'llms_membership' );
	}

	/**
	 * Export student data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function exporter_student_data( $email_address, $page ) {

		$export = array();

		$student = $this->get_student_by_email( $email_address );
		if ( $student ) {
			$export[] = array(
				'group_id' => 'lifterlms_student',
				'group_label' => __( 'Student Data', 'lifterlms' ),
				'item_id' => 'user',
				'data' => $this->get_student_data( $student ),
			);
		}

		return array(
			'data' => $export,
			'done' => true,
		);

	}

	/**
	 * Get data for a certificate
	 * @param    obj     $cert  LLMS_User_Certificate
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_certificate_data( $cert ) {

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
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_enrollment_data( $post_id, $student, $post_type_object ) {

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
	 * Retrive an instance of an LLMS_Student from email address
	 * @param    string     $email  Email addres
	 * @return   false|LLMS_Student
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_student_by_email( $email ) {

		$user = get_user_by( 'email', $email );
		if ( is_a( $user, 'WP_User' ) ) {
			return llms_get_student( $user );
		}

		return false;

	}

	/**
	 * Get student data to export for a user
	 * @param    LLMS_Student  $student
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_student_data( $student ) {

		$data = array();

		$props = apply_filters( 'llms_privacy_export_student_data_props', array(
			'billing_address_1' => __( 'Billing Address 1', 'lifterlms' ),
			'billing_address_2' => __( 'Billing Address 2', 'lifterlms' ),
			'billing_city' => __( 'Billing City', 'lifterlms' ),
			'billing_state' => __( 'Billing State', 'lifterlms' ),
			'billing_zip' => __( 'Billing Zip Code', 'lifterlms' ),
			'billing_country' => __( 'Billing Country', 'lifterlms' ),
			'phone' => __( 'Phone', 'lifterlms' ),
			'ip_address' => __( 'IP Address', 'lifterlms' ),
			'last_login' => __( 'Last Login Date', 'lifterlms' ),
		) );

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
	 * Add files to the zip file for a data export request
	 * Adds certificate files into the /certificates/ diretcotry within the archive
	 * @param    string     $archive_pathname      full path to the zip archive
	 * @param    string     $archive_url           full uri to the zip archive
	 * @param    string     $html_report_pathname  full path to the .html file within the archive
	 * @param    int        $request_id            WP Post ID of the export request
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function maybe_add_export_files( $archive_pathname, $archive_url, $html_report_pathname, $request_id ) {

		if ( ! class_exists( 'ZipArchive' ) ) {
			return;
		}

		$request = wp_get_user_request_data( $request_id );
		$student = $this->get_student_by_email( $request->email );

		if ( ! $student ) {
			return;
		}

		$certs = $student->get_certificates();
		if ( ! $certs ) {
			return;
		}

		$zip = new ZipArchive();
		$delete = array();
		if ( true === $zip->open( $archive_pathname ) ) {
			foreach ( $certs as $cert ) {
				$filepath = LLMS()->certificates()->get_export( $cert->certificate_id, true );
				if ( is_wp_error( $filepath ) ) {
					continue;
				}
				$delete[ $cert->certificate_id ] = $filepath;
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

}

return new LLMS_Privacy();
