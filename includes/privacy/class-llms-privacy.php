<?php
/**
 * Main Privacy Class
 *
 * @package LifterLMS/Privacy/Classes
 *
 * @since 3.18.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Privacy class
 *
 * Hooks into WP Core data exporters and erasers to export / erase LifterLMS data.
 *
 * @since 3.18.0
 * @since 3.37.9 Update CSS classes used in privacy text suggestions.
 */
class LLMS_Privacy extends LLMS_Abstract_Privacy {

	/**
	 * Constructor.
	 *
	 * @since 3.18.0
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct( __( 'LifterLMS', 'lifterlms' ) );

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
		// This eraser should always be last because some of the items above rely on postmeta data to function.
		$this->add_eraser( 'lifterlms-postmeta-data', __( 'Postmeta Data', 'lifterlms' ), array( 'LLMS_Privacy_Erasers', 'postmeta_data' ) );

		/**
		 * Hooks
		 */
		// Add individual cert HTML files to the export directory.
		add_action( 'wp_privacy_personal_data_export_file_created', array( 'LLMS_Privacy_Exporters', 'maybe_add_export_files' ), 100, 4 );

		// Anonymize erased order properties.
		add_filter( 'llms_privacy_get_anon_prop_value', array( 'LLMS_Privacy_Erasers', 'anonymize_prop' ), 10, 3 );
	}

	/**
	 * Anonymize a property value
	 *
	 * @since 3.18.0
	 *
	 * @param string $prop Property name.
	 * @param object $obj  Associated object (if any).
	 * @return string
	 */
	public static function get_anon_prop_value( $prop, $obj = null ) {
		return apply_filters( 'llms_privacy_get_anon_prop_value', '', $prop, $obj );
	}

	/**
	 * Retrieve an array of student data properties which should be exported & erased
	 *
	 * @since 3.18.0
	 *
	 * @param string $type Request type [export|erasure].
	 * @return array
	 */
	protected static function get_order_data_props( $type ) {

		$props = array();

		// don't erase these fields, only export them
		if ( 'export' === $type ) {
			$props = array(
				'id'            => __( 'Order Number', 'lifterlms' ),
				'date'          => __( 'Order Date', 'lifterlms' ),
				'product_title' => __( 'Product', 'lifterlms' ),
				'plan_title'    => __( 'Plan', 'lifterlms' ),
			);
		} elseif ( 'erasure' === $type ) {
			$props = array(
				'user_id' => __( 'User ID', 'lifterlms' ),
			);
		}

		$props = array_merge(
			$props,
			array(
				'billing_first_name' => __( 'Billing First Name', 'lifterlms' ),
				'billing_last_name'  => __( 'Billing Last Name', 'lifterlms' ),
				'billing_email'      => __( 'Billing Email', 'lifterlms' ),
				'billing_address_1'  => __( 'Billing Address 1', 'lifterlms' ),
				'billing_address_2'  => __( 'Billing Address 2', 'lifterlms' ),
				'billing_city'       => __( 'Billing City', 'lifterlms' ),
				'billing_state'      => __( 'Billing State', 'lifterlms' ),
				'billing_zip'        => __( 'Billing Zip Code', 'lifterlms' ),
				'billing_country'    => __( 'Billing Country', 'lifterlms' ),
				'billing_phone'      => __( 'Phone', 'lifterlms' ),
				'user_ip_address'    => __( 'IP Address', 'lifterlms' ),
			)
		);

		return apply_filters( 'llms_privacy_order_data_props', $props, $type );
	}

	/**
	 * Get the privacy message sample content
	 *
	 * This stub can be overloaded.
	 *
	 * @since 3.18.0
	 * @since 3.37.9 Replaced deprecated `.wp-policy-help` class with `.privacy-policy-tutorial`.
	 *
	 * @return string
	 */
	public function get_privacy_message() {
		$content = '
			<div class="wp-suggested-text">' .
				'<p class="privacy-policy-tutorial">' .
					__( 'This sample language includes the basics around what personal data your learning platform may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional add-ons are used, the specific information shared by your site will vary. We recommend consulting with a lawyer when deciding what information to disclose on your privacy policy.', 'lifterlms' ) .
				'</p>' .
				'<p>' . __( 'We collect information about you during the registration, enrollment, and checkout processes on our site.', 'lifterlms' ) . '</p>' .
				'<h2>' . __( 'What we collect and store', 'lifterlms' ) . '</h2>' .
				'<p>' . __( 'When you register an account with us, we’ll ask you to provide information including your name, billing address, email address, phone number, credit card/payment details and optional account information like username and password. We’ll use this information for purposes, such as, to:', 'lifterlms' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Send you information about your account, orders, courses, and memberships', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Communicate with you about courses and memberships that you’re enrolled in', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Respond to your requests, including refunds and complaints', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Process payments and prevent fraud', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Set up your account for our site', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Comply with any legal obligations we have', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Improve our site’s offerings', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Send you marketing messages, if you choose to receive them', 'lifterlms' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'When you create an account, we will store your name, address, email and phone number, which will be used to populate the enrollment and checkout for future purchases and enrollments.', 'lifterlms' ) . '</p>' .
				'<p>' . __( 'We generally store information about you for as long as we need the information for the purposes for which we collect and use it, and we are not legally required to continue to keep it. For example, we will store order information for XXX years for tax and accounting purposes. This includes your name, email address and billing address.', 'lifterlms' ) . '</p>' .
				'<p>' . __( 'We will also store comments or reviews, if you chose to leave them.', 'lifterlms' ) . '</p>' .
				'<h2>' . __( 'Who on our team has access', 'lifterlms' ) . '</h2>' .
				'<p>' . __( 'Members of our team have access to the information you provide us. For example, both Administrators and Site Managers can access:', 'lifterlms' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Order information like what was purchased, when it was purchased and where it should be sent, and', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Customer information like your name, email address, and billing information.', 'lifterlms' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Course and membership instructors can access your course progress and activities including:', 'lifterlms' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Enrollment dates for their courses and memberships', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Course progress and status information for their courses', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Quiz and assignments answers and grades for their courses', 'lifterlms' ) . '</li>' .
					'<li>' . __( 'Comments and reviews made on their memberships and courses', 'lifterlms' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds, and support you.', 'lifterlms' ) . '</p>' .
				'<h2>' . __( 'What we share with others', 'lifterlms' ) . '</h2>' .
				'<p class="privacy-policy-tutorial">' . __( 'In this section you should list who you’re sharing data with, and for what purpose. This could include, but may not be limited to, analytics, marketing, payment gateways, and third party embeds.', 'lifterlms' ) . '</p>' .
				'<p>' . __( 'We share information with third parties who help us provide our orders and store services to you; for example --', 'lifterlms' ) . '</p>' .
			'</div>';

		/**
		 * Customize the default privacy policy content provided by LifterLMS.
		 *
		 * @since 3.18.0
		 *
		 * @param string $content Privacy policy content as an html string.
		 */
		return apply_filters( 'llms_privacy_policy_content', $content );
	}

	/**
	 * Retrieve student achievements.
	 *
	 * @since 3.18.0
	 * @since 6.0.0 Updated the use of `LLMS_Student::get_achievements()` with its new behavior.
	 *
	 * @param LLMS_Student $student Student object.
	 * @return LLMS_User_Achievement[]
	 */
	protected static function get_student_achievements( $student ) {

		$query = $student->get_achievements( array( 'sort' => array( 'date' => 'DESC' ) ) );

		return $query->get_awards();
	}

	/**
	 * Retrieve student certificates.
	 *
	 * @since 3.18.0
	 * @since 6.0.0 Updated the use of `LLMS_Student::get_certificates()` with its new behavior.
	 *
	 * @param LLMS_Student $student Student object.
	 * @return LLMS_User_Certificate[]
	 */
	protected static function get_student_certificates( $student ) {

		$query = $student->get_certificates( array( 'sort' => array( 'date' => 'DESC' ) ) );

		return $query->get_awards();
	}

	/**
	 * Retrieve an array of student data properties which should be exported & erased
	 *
	 * @since 3.18.0
	 *
	 * @return array
	 */
	protected static function get_student_data_props() {

		return apply_filters(
			'llms_privacy_get_student_data_props',
			array(
				'billing_address_1' => __( 'Billing Address 1', 'lifterlms' ),
				'billing_address_2' => __( 'Billing Address 2', 'lifterlms' ),
				'billing_city'      => __( 'Billing City', 'lifterlms' ),
				'billing_state'     => __( 'Billing State', 'lifterlms' ),
				'billing_zip'       => __( 'Billing Zip Code', 'lifterlms' ),
				'billing_country'   => __( 'Billing Country', 'lifterlms' ),
				'phone'             => __( 'Phone', 'lifterlms' ),
				'ip_address'        => __( 'IP Address', 'lifterlms' ),
				'last_login'        => __( 'Last Login Date', 'lifterlms' ),
			)
		);
	}

	/**
	 * Retrieve student course & membership enrollment data
	 *
	 * @since    3.18.0
	 *
	 * @param LLMS_Student $student    Student object.
	 * @param int          $page       Page number.
	 * @param string       $post_type  WP Post type (course/membership).
	 * @return   array
	 */
	protected static function get_student_enrollments( $student, $page, $post_type ) {

		$limit = 250;

		$enrollments = $student->get_enrollments(
			$post_type,
			array(
				'limit' => $limit,
				'skip'  => ( $page - 1 ) * $limit,
			)
		);

		return array(
			'done'    => ( ! $enrollments['more'] ),
			'results' => $enrollments['results'],
		);
	}

	/**
	 * Retrieve student orders
	 *
	 * @since 3.18.0
	 *
	 * @param LLMS_Student $student Student object.
	 * @param int          $page    Page number.
	 * @return array
	 */
	protected static function get_student_orders( $student, $page ) {

		$done    = true;
		$results = array();

		$orders = $student->get_orders(
			array(
				'count' => 250,
				'page'  => $page,
			)
		);
		if ( $orders && $orders['pages'] ) {
			$results = $orders['orders'];
			$done    = ( absint( $page ) === absint( $orders['pages'] ) );
		}

		return array(
			'done'   => $done,
			'orders' => $results,
		);
	}

	/**
	 * Retrieve student quizzes.
	 *
	 * @since  3.18.0
	 *
	 * @param LLMS_Student $student Student object.
	 * @param int          $page    Page number.
	 * @return LLMS_Query_Quiz_Attempt
	 */
	protected static function get_student_quizzes( $student, $page ) {

		return new LLMS_Query_Quiz_Attempt(
			array(
				'page'       => $page,
				'per_page'   => 500,
				'quiz_id'    => array(),
				'student_id' => $student->get( 'id' ),
			)
		);
	}
}

function llms_load_privacy() {
	return new LLMS_Privacy();
}
add_action( 'init', 'llms_load_privacy' );
