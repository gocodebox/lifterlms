<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin analytics Class
*
* analytics field Factory
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_Analytics {

	/**
	* analytics array
	* @access private
	* @var array
	*/
	private static $analytics = array();

	/**
	* Errors array
	* @access private
	* @var array
	*/
	private static $errors   = array();

	/**
	* Messages array
	* @access private
	* @var array
	*/
	private static $messages = array();

	/**
	* Inits $analytics and includes analytics base class.
	*
	* @return self::$analytics array
	*/
	public static function get_analytics_tabs() {

		if ( empty( self::$analytics ) ) {
			$analytics = array();

			include_once( 'analytics/class.llms.analytics.page.php' );

			self::$analytics = apply_filters( 'lifterlms_get_analytics_pages', $analytics );

			//$analytics[] = include( 'analytics/class.llms.analytics.dashboard.php' );
			if ( ! get_option( 'lifterlms_analytics_disable_sales' ) ) {

				$analytics[] = include( 'analytics/class.llms.analytics.sales.php' );

			}
			if ( ! get_option( 'lifterlms_analytics_disable_courses' ) ) {

				$analytics[] = include( 'analytics/class.llms.analytics.courses.php' );
			}

			if ( ! get_option( 'lifterlms_analytics_disable_memberships' ) ) {

				$analytics[] = include( 'analytics/class.llms.analytics.memberships.php' );

			}
		}

		return self::$analytics;
	}

	/**
	* Save method. Saves all fields on current tab
	*
	* @return void
	*/
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'lifterlms-analytics' ) ) {
			die( __( 'Whoa! something went wrong there!. Please refresh the page and retry.', 'lifterlms' ) );
		}

	   	do_action( 'lifterlms_analytics_save_' . $current_tab );
	    do_action( 'lifterlms_update_options_' . $current_tab );
	    do_action( 'lifterlms_update_options' );

		self::set_message( __( 'Your analytics have been saved.', 'lifterlms' ) );

		do_action( 'lifterlms_analytics_saved' );
	}

	/**
	* set message to messages array
	*
	* @param string $message
	* @return void
	*/
	public static function set_message( $message ) {
		self::$messages[] = $message;
	}

	/**
	* set message to messages array
	*
	* @param string $message
	* @return void
	*/
	public static function set_error( $message ) {
		self::$errors[] = $message;
	}

	/**
	* display messages in analytics
	*
	* @return void
	*/
	public static function display_messages_html() {

		if ( sizeof( self::$errors ) > 0 ) {

			foreach ( self::$errors as $error ) {
				echo '<div class="error"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		}
	}

	/**
	* analytics Page output tabs
	*
	* @return void
	*/
	public static function output() {
		global $current_tab;

		do_action( 'lifterlms_analytics_start' );

		self::get_analytics_tabs();

		$current_tab = empty( $_GET['tab'] ) ? 'sales' : sanitize_title( $_GET['tab'] );

	    if ( ! empty( $_POST ) ) {
	    	self::save(); }

	    if ( ! empty( $_GET['llms_error'] ) ) {
	    	self::set_error( stripslashes( $_GET['llms_error'] ) ); }

	    self::display_messages_html();

	    $tabs = apply_filters( 'lifterlms_analytics_tabs_array', array() );

		include 'views/html.admin.analytics.php';
	}

	/**
	* Output html for analytics tabs.
	*
	* @return void
	*/
	public static function output_html( $analytics ) {
		echo $analytics;
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the lifterlms options array and outputs each field.
	 *
	 * @param array $settings Opens array to output
	 *
	 * @return bool
	 */
	public static function save_search_fields( $analytics ) {
	    if ( empty( $_POST ) ) {
	    	return false; }

	    //sales analytics
	    if ( ! empty( $_POST['action'] ) && ( 'llms-analytics-sales' === $_POST['action'] ) && ! empty( $_POST['_wpnonce'] ) ) {

	 		$search = new stdClass;

	 		//validate fields
	 		if ( empty( $_POST['llms_product_select'] ) ) {
	 			self::set_error( __( 'You must choose a product option.' , 'lifterlms' ) );
	 		}
	 		if ( empty( $_POST['llms_date_filter'] ) ) {
	 			self::set_error( __( 'You must choose a date filter.' , 'lifterlms' ) );
	 		}
	 		if ( 'none' === $_POST['llms_date_filter'] && ( empty( $_POST['llms-start-date'] ) || empty( $_POST['llms-start-date'] ) ) ) {
	 			self::set_error( __( 'You must enter a start and end date.' , 'lifterlms' ) );
	 		}

	 		$search->product_id = llms_clean( $_POST['llms_product_select'] );
	 		$search->date_filter = llms_clean( $_POST['llms_date_filter'] );
	 		//$search->exclude_coupons = ( isset( $_POST[ 'llms_exclude_coupons' ] ) ? $_POST[ 'llms_exclude_coupons' ] : false );

	 		//get start and end date for date filter
	 		if ( 'none' !== $search->date_filter ) {

	 			$date_range = LLMS_Date::get_date_range_by_filter( $search->date_filter );
	 			$search->date_range = $date_range;
	 			$search->start_date = $date_range['start_date'];
	 			$search->end_date = $date_range['end_date'];
	 			$search->end_date_plus_one = LLMS_Date::db_date( $date_range['end_date'] . '+ 1 day' );

	 		} else {

	 			$search->start_date = LLMS_Date::db_date( llms_clean( $_POST['llms-start-date'] ) );
	 			$search->end_date = LLMS_Date::db_date( llms_clean( $_POST['llms-end-date'] ) );
	 			$search->end_date_plus_one = LLMS_Date::db_date( llms_clean( $_POST['llms-end-date'] ) . '+ 1 day' );
	 		}

	 		//set up search arguments
			$values = array(
				'0' => array(
				 	'key' => '_llms_order_date',
					'value' => $search->start_date,
					'compare' => '>=',
				),
				'1' => array(
				 	'key' => '_llms_order_date',
					'value' => $search->end_date_plus_one,
					'compare' => '<=',
				),
			);

			// if product id is not "all" then add product id to values
			if ( 'all_products' !== $search->product_id ) {

				$product_value = array(
				 	'key' => '_llms_order_product_id',
					'value' => $search->product_id,
					'compare' => '=',
				);
				array_push( $values, $product_value );

			}

			//get results and save to search object
	 		$search->results = LLMS_Analytics::get_orders( $values );

	 		//set search object as session object
		    LLMS()->session->set( 'llms_analytics_sales', $search );
	    }// End if().

	    //course analytics
	    if ( ! empty( $_POST['action'] ) && ( 'llms-analytics-course' === $_POST['action'] ) && ! empty( $_POST['_wpnonce'] ) ) {

	 		$search = new stdClass;

	 		//validate fields
	 		if ( empty( $_POST['llms_product_select'] ) ) {
	 			self::set_error( __( 'You must choose a product option.' , 'lifterlms' ) );
	 		}
	 		if ( empty( $_POST['llms_date_filter'] ) ) {
	 			self::set_error( __( 'You must choose a date filter.' , 'lifterlms' ) );
	 		}
	 		if ( 'none' === $_POST['llms_date_filter'] && ( empty( $_POST['llms-start-date'] ) || empty( $_POST['llms-start-date'] ) ) ) {
	 			self::set_error( __( 'You must enter a start and end date.' , 'lifterlms' ) );
	 		}

	 		$search->product_id = llms_clean( $_POST['llms_product_select'] );
	 		$search->date_filter = llms_clean( $_POST['llms_date_filter'] );
	 		//$search->exclude_coupons = ( isset( $_POST[ 'llms_exclude_coupons' ] ) ? $_POST[ 'llms_exclude_coupons' ] : false );

	 		//get start and end date for date filter
	 		if ( 'none' !== $search->date_filter ) {

	 			$date_range = LLMS_Date::get_date_range_by_filter( $search->date_filter );
	 			$search->date_range = $date_range;
	 			$search->start_date = $date_range['start_date'];
	 			$search->end_date = $date_range['end_date'];

	 		} else {

	 			$search->start_date = LLMS_Date::db_date( llms_clean( $_POST['llms-start-date'] ) );
	 			$search->end_date = LLMS_Date::db_date( llms_clean( $_POST['llms-end-date'] ) );

	 		}

			// if product id is not "all" then add product id to values
			if ( 'all_courses' === $search->product_id ) {

				$courses = LLMS_Analytics::get_posts( 'course' );

				//if any courses are returned loop through them and get the enrolled students
				if ( $courses ) {

					$search->courses = $courses;
					$students = array();

					foreach ( $courses as $course ) {
						$enrolled_users = LLMS_Analytics::get_total_users_all_time( $course->ID, $search->end_date );

						$students = array_merge( $students, $enrolled_users );
					}

					$search->students = $students;
				}
			} else {

				//get course data
				$course = array( get_post( $search->product_id ) );

				//get course enrollment data
				$enrolled_users = LLMS_Analytics::get_total_users_all_time( $search->product_id, $search->end_date );
				$search->students = $enrolled_users;
				$search->courses = $course;

				//create new course object
				$course_obj = new LLMS_Course( $search->product_id );
				$lesson_ids = $course_obj->get_lesson_ids();
				//$search->lesson_ids = $lesson_ids;

				//get all lessons in a course
				$search->lessons = array();
				foreach ( $lesson_ids as $id ) {

					array_push( $search->lessons, get_post( $id ) );

				}
			}// End if().

	 		//set search object as session object
		    LLMS()->session->set( 'llms_analytics_course', $search );
	    }// End if().

	    //membership analytics
	    if ( ! empty( $_POST['action'] ) && ( 'llms-analytics-membership' === $_POST['action'] ) && ! empty( $_POST['_wpnonce'] ) ) {

	 		$search = new stdClass;

	 		//validate fields
	 		if ( empty( $_POST['llms_product_select'] ) ) {
	 			self::set_error( __( 'You must choose a product option.' , 'lifterlms' ) );
	 		}
	 		if ( empty( $_POST['llms_date_filter'] ) ) {
	 			self::set_error( __( 'You must choose a date filter.' , 'lifterlms' ) );
	 		}
	 		if ( 'none' === $_POST['llms_date_filter'] && ( empty( $_POST['llms-start-date'] ) || empty( $_POST['llms-start-date'] ) ) ) {
	 			self::set_error( __( 'You must enter a start and end date.' , 'lifterlms' ) );
	 		}

	 		$search->product_id = llms_clean( $_POST['llms_product_select'] );
	 		$search->date_filter = llms_clean( $_POST['llms_date_filter'] );
	 		//$search->exclude_coupons = ( isset( $_POST[ 'llms_exclude_coupons' ] ) ? $_POST[ 'llms_exclude_coupons' ] : false );

	 		//get start and end date for date filter
	 		if ( 'none' !== $search->date_filter ) {

	 			$date_range = LLMS_Date::get_date_range_by_filter( $search->date_filter );
	 			$search->date_range = $date_range;
	 			$search->start_date = $date_range['start_date'];
	 			$search->end_date = $date_range['end_date'];

	 		} else {

	 			$search->start_date = LLMS_Date::db_date( llms_clean( $_POST['llms-start-date'] ) );
	 			$search->end_date = LLMS_Date::db_date( llms_clean( $_POST['llms-end-date'] ) );

	 		}

			// if product id is not "all" then add product id to values
			if ( 'all_memberships' === $search->product_id ) {

				$memberships = LLMS_Analytics::get_posts( 'llms_membership' );

				//if any courses are returned loop through them and get the enrolled students
				if ( $memberships ) {

					$search->memberships = $memberships;
					$members = array();

					foreach ( $memberships as $membership ) {
						$enrolled_users = LLMS_Analytics::get_total_users_all_time( $membership->ID, $search->end_date );

						$members = array_merge( $members, $enrolled_users );
					}

					$search->members = $members;
				}
			} else {

				//get course data
				$membership = array( get_post( $search->product_id ) );

				//get course enrollment data
				$enrolled_users = LLMS_Analytics::get_total_users_all_time( $search->product_id, $search->end_date );
				$search->members = $enrolled_users;
				$search->memberships = $membership;

				//$search->test_student_progress = $course_obj->get_student_progress( '147' );

			}

	 		//set search object as session object
		    LLMS()->session->set( 'llms_analytics_membership', $search );
	    }// End if().

	}

}

