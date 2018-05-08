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

		$user = get_user_by( 'email', $email_address );
		$done = true;
		$export = array();

		if ( is_a( $user, 'WP_User' ) ) {

			$student = llms_get_student( $user );
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
							'group_id'    => $group_id,
							'group_label' => $group_label,
							'item_id'     => sprintf( '%1$s-%2$d', $post_type, $post_id ),
							'data'        => $this->get_enrollment_data( $post_id, $student, $post_type_obj ),
						);

					}

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

		$user = get_user_by( 'email', $email_address );
		$export = array();

		if ( is_a( $user, 'WP_User' ) ) {
			$export[] = array(
				'group_id'    => 'lifterlms_student',
				'group_label' => __( 'Student Data', 'lifterlms' ),
				'item_id'     => 'user',
				'data'        => $this->get_student_data( $user ),
			);
		}

		return array(
			'data' => $export,
			'done' => true,
		);

	}

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
	 * Get student data to export for a user
	 * @param    WP_User     $user
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_student_data( $user ) {

		$data = array();
		$student = llms_get_student( $user );

		if ( ! $student ) {
			return $data;
		}

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

}

return new LLMS_Privacy();
