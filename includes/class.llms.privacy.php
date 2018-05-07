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
		$this->add_exporter( 'lifterlms-student-data', __( 'Student Data', 'lifterlms' ), array( $this, 'student_data_exporter' ) );

	}

	/**
	 * Export student data by email address
	 * @param    string     $email_address  email address of the user to retrieve data for
	 * @param    int        $page           process page number
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function student_data_exporter( $email_address, $page ) {

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

		$props = apply_filters( 'llms_privacy_export_student_personal_data_props', array(
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

			$value = apply_filters( 'llms_privacy_export_student_personal_data_prop_value', $student->get( $prop ), $prop, $student );

			if ( $value ) {
				$data[] = array(
					'name'  => $name,
					'value' => $value,
				);
			}

		}

		return apply_filters( 'llms_privacy_export_student_personal_data', $data, $student );

	}

}

return new LLMS_Privacy();
