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

		/**
		 * Require additional classes
		 */
		include_once 'class-llms-privacy-erasers.php';
		include_once 'class-llms-privacy-exporters.php';

		/**
		 * Exporters
		 */
		$this->add_exporter( 'lifterlms-student-data', __( 'Student Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'student_data' ) );
		$this->add_exporter( 'lifterlms-course-data', __( 'Course Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'course_data' ) );
		$this->add_exporter( 'lifterlms-quiz-data', __( 'Quiz Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'quiz_data' ) );
		$this->add_exporter( 'lifterlms-membership-data', __( 'Membership Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'membership_data' ) );
		$this->add_exporter( 'lifterlms-order-data', __( 'Order Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'order_data' ) );
		$this->add_exporter( 'lifterlms-achievement-data', __( 'Achievement Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'achievement_data' ) );
		$this->add_exporter( 'lifterlms-certificate-data', __( 'Certificate Data', 'lifterlms' ), array( 'LLMS_Privacy_Exporters', 'certificate_data' ) );

		/**
		 * Erasers
		 */
		$this->add_eraser( 'lifterlms-student-data', __( 'Student Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'student_data' ) );
		$this->add_eraser( 'lifterlms-quiz-data', __( 'Quiz Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'quiz_data' ) );
		$this->add_eraser( 'lifterlms-order-data', __( 'Order Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'order_data' ) );
		$this->add_eraser( 'lifterlms-achievement-data', __( 'Achievement Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'achievement_data' ) );
		$this->add_eraser( 'lifterlms-certificate-data', __( 'Order Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'certificate_data' ) );
		$this->add_eraser( 'lifterlms-notification-data', __( 'Notification Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'notification_data' ) );
		// this eraser should always be last because some of the items above rely on postmeta data to function
		$this->add_eraser( 'lifterlms-postmeta-data', __( 'Postmeta Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'postmeta_data' ) );

		/**
		 * Hooks
		 */
		// add individual cert HTML files to the export directory
		add_action( 'wp_privacy_personal_data_export_file_created', array( 'LLMS_Privacy_Exporters', 'maybe_add_export_files' ), 100, 4 );

		// anonymize erased order properties
		add_filter( 'llms_privacy_get_anon_prop_value', array( 'LLMS_Privacy_Erasers', 'anonymize_prop' ), 10, 3 );

	}

	/**
	 * Anonymize a property value
	 * @param    string     $prop  property name
	 * @param    obj        $obj   associated object (if any)
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public static function get_anon_prop_value( $prop, $obj = null ) {
		return apply_filters( 'llms_privacy_get_anon_prop_value', '', $prop, $obj );
	}

	/**
	 * Retrieve an array of student data properties which should be exported & erased
	 * @param    string   $type  request type [export|erasure]
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_order_data_props( $type ) {

		$props = array();

		// don't erase these fields, only export them
		if ( 'export' === $type ) {
			$props = array(
				'id' => __( 'Order Number', 'lifterlms' ),
				'date' => __( 'Order Date', 'lifterlms' ),
				'product_title' => __( 'Product', 'lifterlms' ),
				'plan_title' => __( 'Plan', 'lifterlms' ),
			);
		} elseif ( 'erasure' === $type ) {
			$props = array(
				'user_id' => __( 'User ID', 'lifterlms' ),
			);
		}

		$props = array_merge( $props, array(
			'billing_first_name' => __( 'Billing First Name', 'lifterlms' ),
			'billing_last_name' => __( 'Billing Last Name', 'lifterlms' ),
			'billing_email' => __( 'Billing Email', 'lifterlms' ),
			'billing_address_1' => __( 'Billing Address 1', 'lifterlms' ),
			'billing_address_2' => __( 'Billing Address 2', 'lifterlms' ),
			'billing_city' => __( 'Billing City', 'lifterlms' ),
			'billing_state' => __( 'Billing State', 'lifterlms' ),
			'billing_zip' => __( 'Billing Zip Code', 'lifterlms' ),
			'billing_country' => __( 'Billing Country', 'lifterlms' ),
			'billing_phone' => __( 'Phone', 'lifterlms' ),
			'user_ip_address' => __( 'IP Address', 'lifterlms' ),
		) );

		return apply_filters( 'llms_privacy_order_data_props', $props, $type );

	}

	/**
	 * Retrive an instance of an LLMS_Student from email address
	 * @param    string     $email  Email addres
	 * @return   false|LLMS_Student
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_student_by_email( $email ) {

		$user = get_user_by( 'email', $email );
		if ( is_a( $user, 'WP_User' ) ) {
			return llms_get_student( $user );
		}

		return false;

	}

	/**
	 * Retrieve student certificates
	 * @param    obj     $student  LLMS_Student
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_student_achievements( $student ) {
		return $student->get_achievements( 'updated_date', 'DESC', 'achievements' );
	}

	/**
	 * Retrieve student certificates
	 * @param    obj     $student  LLMS_Student
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_student_certificates( $student ) {
		return $student->get_certificates( 'updated_date', 'DESC', 'certificates' );
	}

	/**
	 * Retrieve an array of student data properties which should be exported & erased
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_student_data_props() {

		return apply_filters( 'llms_privacy_get_student_data_props', array(
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

	}

	/**
	 * Retrieve student course & membership enrollment data
	 * @param    obj     $student    LLMS_Student
	 * @param    int     $page       page number
	 * @param    string  $post_type  WP Post type (course/membership)
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_student_enrollments( $student, $page, $post_type ) {

		$limit = 250;

		$enrollments = $student->get_enrollments( $post_type, array(
			'limit' => $limit,
			'skip' => ( $page - 1 ) * $limit,
		) );

		return array(
			'done' => ( ! $enrollments['more'] ),
			'results' => $enrollments['results'],
		);

	}

	/**
	 * Retrive student orders
	 * @param    obj     $student    LLMS_Student
	 * @param    int     $page       page number
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected static function get_student_orders( $student, $page ) {

		$done = true;
		$results = array();

		$orders = $student->get_orders( array(
			'count' => 250,
			'page' => $page,
		) );
		if ( $orders && $orders['pages'] ) {
			$results = $orders['orders'];
			$done = ( $page == $orders['pages'] );
		}

		return array(
			'done' => $done,
			'orders' => $results,
		);

	}

	protected static function get_student_quizzes( $student, $page ) {

		return new LLMS_Query_Quiz_Attempt( array(
			'page' => $page,
			'per_page' => 500,
			'quiz_id' => array(),
			'student_id' => $student->get( 'id' ),
		) );

	}

}

return new LLMS_Privacy();
