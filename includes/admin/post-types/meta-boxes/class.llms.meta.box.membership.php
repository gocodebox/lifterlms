<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Membership extends LLMS_Admin_Metabox{

	public static $prefix = '_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$thepostid = $post->ID;

		$sku = get_post_meta( $thepostid, '_sku', true );
		$regular_price = get_post_meta( $thepostid, '_regular_price', true );
		$sale_price = get_post_meta( $thepostid, '_sale_price', true );

		$sale_price_dates_from 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_from', true ) ) ? $date : '';
		$sale_price_dates_to 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_to', true ) ) ? $date : '';

		$recurring_enabled  		= get_post_meta( $thepostid, '_llms_recurring_enabled', true );
		$subscription_price 		= get_post_meta( $thepostid, '_llms_subscription_price', true );
		$subscription_first_payment = get_post_meta( $thepostid, '_llms_subscription_first_payment', true );
		$billing_period 			= get_post_meta( $thepostid, '_llms_billing_period', true );
		$billing_freq 				= get_post_meta( $thepostid, '_llms_billing_freq', true );
		$billing_cycle				= get_post_meta( $thepostid, '_llms_billing_cycle', true );

		//billing period options
		////needs to move to paypal class
		$billing_periods = array(
			array(
				'key' 	=> 'day',
				'title' => 'Day',
			),
			array(
				'key' 	=> 'week',
				'title' => 'Week',
			),
			array(
				'key' 	=> 'month',
				'title' => 'Month',
			),
			array(
				'key' 	=> 'year',
				'title' => 'Year',
			),
		);

		$membership_expiration_periods = array(
			array(
				'key' 	=> 'day',
				'title' => 'Day',
			),
			array(
				'key' 	=> 'month',
				'title' => 'Month',
			),
			array(
				'key' 	=> 'year',
				'title' => 'Year',
			),
		);

		$llms_meta_fields_llms_membership_settings = array(
			array(
				'title' 	=> 'Description',
				'fields' 	=> array(
					array(
						'type'		=> 'post-content',
						'label'		=> 'Enrolled user and non-enrolled visitor description',
						'desc' 		=> 'This content will be displayed to enrolled users. If the non-enrolled users description
							field is left blank the content will be displayed to both enrolled users and non-logged / restricted
							visitors.',
						'id' 		=> '',
						'class' 	=> '',
						'value' 	=> '',
						'desc_class' => '',
						'group' 	=> '',
					),
					array(
						'type'		=> 'post-excerpt',
						'label'		=> 'Restricted Access Description',
						'desc' 		=> 'Enter content in this field if you would like visitors that
							are not enrolled or are restricted to view different content from
							enrolled users. Visitors who are not enrolled in the course
							or are restricted from the course will see this description if it contains content.',
						'id' 		=> '',
						'class' 	=> '',
						'value' 	=> '',
						'desc_class' => '',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Price Single',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'SKU',
						'desc' 		=> 'Enter a SKU for your membership.',
						'id' 		=> self::$prefix . 'sku',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'Single Payment Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter a price to offer your membership for a one time purchase.',
						'id' 		=> self::$prefix . 'regular_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Membership is on sale',
						'desc' 		=> 'Enable single payment sale for this membership.',
						'id' 		=> self::$prefix . 'on_sale',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'Sale Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter a sale price for the membership.',
						'id' 		=> self::$prefix . 'sale_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Sale Price Start Date',
						'desc' 		=> 'Enter the date your sale will begin.',
						'id' 		=> self::$prefix . 'sale_price_dates_from',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Sale Price End Date',
						'desc' 		=> 'Enter the date your sale will end.',
						'id' 		=> self::$prefix . 'sale_price_dates_to',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Price Recurring',
				'fields' 	=> array(
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Enable Recurring Payment',
						'desc' 		=> 'Enable recurring payment options.',
						'id' 		=> self::$prefix . 'llms_recurring_enabled',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'Recurring Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter the amount you will bill at set intervals.',
						'id' 		=> self::$prefix . 'llms_subscription_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'First Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter the payment amount you will charge on product purchase. This can be 0 to give users a free trial period.',
						'id' 		=> self::$prefix . 'llms_subscription_first_payment',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Billing Period',
						'desc' 		=> 'Combine billing period and billing frequency set billing interval. IE: Billing period =  week and frequency 2 will bill every 2 weeks.',
						'id' 		=> self::$prefix . 'llms_billing_period',
						'class' 	=> 'input-full',
						'value' 	=> $billing_periods,
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Billing Frequency',
						'desc' 		=> 'Use with billing period to set billing interval',
						'id' 		=> self::$prefix . 'llms_billing_freq',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Billing Cycles',
						'desc' 		=> 'Enter 0 to charge indefinitely. IE: 12 would bill for 12 months.',
						'id' 		=> self::$prefix . 'llms_billing_cycle',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Expiration',
				'fields' 	=> array(
					array(
						'label' 	=> 'Interval',
						'desc' 		=> 'Enter the interval. IE: enter 1 and select year below to set expiration to 1 year.',
						'id' 		=> self::$prefix . 'llms_expiration_interval',
						'type'  	=> 'text',
						'section' 	=> 'expiration_interval',
						'group' 	=> '',
						'desc_class' => 'd-all',
						'class' 	=> 'input-full',
					),
					array(
						'label' 	=> 'Expiration Period',
						'desc' 		=> 'Combine the period with the interval above to set an expiration time line.',
						'id' 		=> self::$prefix . 'llms_expiration_period',
						'type'  	=> 'select',
						'section' 	=> 'expiration_period',
						'value' 	=> $membership_expiration_periods,
						'group' 	=> '',
						'desc_class' => 'd-all',
						'class' 	=> 'input-full',
					),
				),
			),
			array(
				'title' 	=> 'Students',
				'fields' 	=> array(
					array(
						'type'		=> 'select',
						'label'		=> 'Add Student',
						'desc'		=> 'Add a user to the course.',
						'id'		=> self::$prefix . 'add_new_user',
						'class'		=> 'add-student-select',
						'value' 	=> array(),
						'desc_class' => 'd-all',
						'group' 	=> '',
						'multi'		=> true,
					),
					array(
						'type'		=> 'button',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> self::$prefix . 'add_student_submit',
						'class' 	=> 'llms-button-primary',
						'value' 	=> 'Add Student',
						'desc_class' => '',
						'group' 	=> '',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Remove Student',
						'desc'		=> 'Remove a user from the course.',
						'id'		=> self::$prefix . 'remove_student',
						'class' 	=> 'remove-student-select',
						'value' 	=> array(),
						'desc_class' => 'd-all',
						'group' 	=> '',
						'multi'		=> true,
					),
					array(
						'type'		=> 'button',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> self::$prefix . 'remove_student_submit',
						'class' 	=> 'llms-button-primary',
						'value' 	=> 'Remove Student',
						'desc_class' => '',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Enrollment',
				'fields' 	=> array(
					array(
						'label' 	=> 'Courses',
						'desc' 		=> 'Add course to membership.',
						'id' 		=> self::$prefix . 'llms_course_membership',
						'type'  	=> 'select',
						'value' 	=> self::get_courses_not_in_membership_list(),
						'group' 	=> '',
						'multi'		=> true,
						'desc_class' => 'd-all',
						'class' 	=> 'input-full add-course-to-membership',
					),
					array(
						'type'		=> 'button',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> self::$prefix . 'add_course_submit',
						'class' 	=> 'llms-button-primary',
						'value' 	=> 'Add Courses',
						'desc_class' => '',
						'group' 	=> '',
					),
					array(
						'label' 	=> 'Added Courses',
						'desc' 		=> 'Remove course from membership.',
						'id' 		=> self::$prefix . 'llms_remove_course_membership',
						'type'  	=> 'select',
						'value' 	=> self::get_courses_in_membership_list(),
						'group' 	=> '',
						'multi'		=> true,
						'desc_class' => 'd-all',
						'class' 	=> 'input-full remove-course-from-membership',
					),
					array(
						'type'		=> 'button',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> self::$prefix . 'remove_course_submit',
						'class' 	=> 'llms-button-primary',
						'value' 	=> 'Remove Courses',
						'desc_class' => '',
						'group' 	=> '',
					),
					array(
						'label' 	=> 'Courses',
						'desc' 		=> 'Automatically enroll users in the selected courses on successful membership registration',
						'id' 		=> self::$prefix . 'llms_course_membership_table',
						'titles'	=> array( 'Course Name', 'Auto Enroll' ),
						'type'  	=> 'table',
						'table_data' => self::get_courses_table_data(),
						'group' 	=> '',
						'class' 	=> '',
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_llms_membership_settings' )) {
			//Add Fields to the membership Meta Box
			$llms_meta_fields_llms_membership_settings = apply_filters( 'llms_meta_fields_llms_membership_settings', $llms_meta_fields_llms_membership_settings );
		}

		return $llms_meta_fields_llms_membership_settings;
	}


	/**
	 * Retrieve a list of courses that are not currently restricted to the membership
	 * @return array
	 */
	public static function get_courses_not_in_membership_list() {

		$exclude = array();
		foreach ( self::get_courses_in_membership_list() as $c ) {

			$exclude[] = $c['key'];

		}

		$args = array(
			'post_type' 	=> 'course',
			'nopaging'		=> true,
			'post_status'   => 'publish',
			'number'		=> 1000,
			'exclude'		=> $exclude,
		);

		$courses_list = array();

		$courses = get_posts( $args );

		foreach ( $courses as $course ) {
			$courses_list[] = array(
				'key' => $course->ID,
				'title' => $course->post_title,
			);
		}

		return $courses_list;

	}

	/**
	 * Retrive a list of courses that are currently restricted by the membership
	 * @return array
	 */
	public static function get_courses_in_membership_list() {
		global $wpdb, $post;
		$courses_list = array();
		$posts_table = $wpdb->prefix . 'posts';
		$postmeta = $wpdb->prefix . 'postmeta';

		$postmeta_select = '%"' . $post->ID . '"%';

		$select_courses = "SELECT ID, post_title FROM $posts_table
					JOIN $postmeta
					ON $posts_table.ID = $postmeta.post_id
					WHERE $posts_table.post_type = 'course'
					AND $postmeta.meta_key = '_llms_restricted_levels'
					AND $postmeta.meta_value LIKE '$postmeta_select'";
		$courses = $wpdb->get_results( $select_courses );

		foreach ($courses as $course) {
			$courses_list[] = array(
				'key' => $course->ID,
				'title' => $course->post_title,
			);
		}

		return $courses_list;
	}


	/**
	 * Retrieve the HTML for the "Courses" table on the enrollment tab
	 * @return array
	 */
	public static function get_courses_table_data() {
		global $post;
		$membership_courses = self::get_courses_in_membership_list();

		$table_data = array();
		$auto_enroll_checkboxes = get_post_meta( $post->ID, '_llms_auto_enroll', true );

		if ( ! $auto_enroll_checkboxes ) {
			$auto_enroll_checkboxes = array();
		}

		foreach ($membership_courses as $course) {
			$auto_enroll_checkbox = in_array( $course['key'], $auto_enroll_checkboxes ) ? 'checked' : '';

			$table_data[] = array(
				'<a href="' . admin_url( 'post.php?post=' . $course['key'] . '&action=edit' ) . ' ">' . $course['title'] . '</a>',
				'<input type="checkbox" name="autoEnroll[]" ' . $auto_enroll_checkbox . ' value="' . $course['key'] . '"',
			);
		}

		return $table_data;

	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		if (isset( $_POST['_llms_course_membership'] )) {
			foreach ($_POST['_llms_course_membership'] as $course_id) {
				$levels = get_post_meta( $course_id, '_llms_restricted_levels', true );
				if ( ! $levels ) {
					$levels = array();
				}
				$memberships = array_merge( $levels, array( ( string ) $post_id ) );

				update_post_meta( $course_id, '_llms_is_restricted', true );
				update_post_meta( $course_id, '_llms_restricted_levels', $memberships );
			}
		}

		if (isset( $_POST['_llms_remove_course_membership'] )) {
			foreach ($_POST['_llms_remove_course_membership'] as $course_id) {
				$memberships = array_diff( get_post_meta( $course_id, '_llms_restricted_levels', true ), array( $post_id ) );

				if ( ! count( $memberships )) {
					update_post_meta( $course_id, '_llms_is_restricted', false );
				}
				update_post_meta( $course_id, '_llms_restricted_levels', $memberships );
			}
		}

		$auto_enroll = isset( $_POST['autoEnroll'] ) ? $_POST['autoEnroll'] : array();
		update_post_meta( $post->ID, '_llms_auto_enroll', $auto_enroll );
	}

}
