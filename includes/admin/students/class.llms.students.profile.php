<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin analytics Page, sales Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Students_Profile extends LLMS_Students_Page {

	/**
	* Constructor
	*
	* executes analytics tab actions
	*/
	public function __construct() {
		$this->id    = 'profile';
		$this->label = __( 'Profile', 'lifterlms' );

		add_filter( 'lifterlms_students_tabs_array', array( $this, 'add_students_page' ), 20 );
		add_action( 'lifterlms_students_' . $this->id, array( $this, 'output' ) );
		//add_action( 'admin_init', array( $this, 'search_students_sales' ) );
		add_action( 'lifterlms_students_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Main students page builder
	 * Collects elements and calls get_page_contents to wrap html
	 * Called from html.admin.students to build page.
	 *
	 * @return [html]
	 */
	public function get_students() {
		//get student tabs

		//only display page contents student exists
		if ( empty( $_GET['student'] ) || ! get_user_by( 'id', sanitize_title( $_GET['student'] ) ) ) {

			$title = __( 'Student not found', 'lifterlms' );
			$html = '';

			return $this->get_page_contents( $title, $html );

		} else {

			$user = $this->get_user_data( sanitize_title( $_GET['student'] ) );

			//set title to user's name
			$title = sprintf( __( '%s', 'lifterlms' ), $user->name );

			//user profile data
			$html = self::full_width_widget( self::user_profile( $user ), 'top' );

			$html .= '<div class="llms-widget-row top">';

			//total courses enrolled
			$html .= self::quarter_width_widget( self::total_courses_enrolled( $user ) );

			//total memberships enrolled
			$html .= self::quarter_width_widget( self::total_memberships_enrolled( $user ) );

			//total purchases
			$html .= self::quarter_width_widget( self::total_purchases( $user ) );

			//total money spent
			$html .= self::quarter_width_widget( self::total_money_spent( $user ) );

			$html .= '</div>'; //end widget row

			//course list
			$html .= self::full_width_widget( $this->student_course_list( $user ) );

			$html .= self::full_width_widget( $this->student_membership_list( $user ) );

			return $this->get_page_contents( $title, $html );

		}

		//$search = LLMS()->session->get( 'llms_student_search' );
		//var_dump( $search);

		//search form
		//$html = $this->search_form();

		// //WIDGET ROW
		// $html .= '<div class="llms-widget-row">';
		// //total Students ever
		// $html .= self::quarter_width_widget( $this->total_students_all_time( $search ) );

		// // total currently enrolled students
		// $html .= self::quarter_width_widget( $this->total_current_students( $search ) );

		// //course completion percentage
		// $html .= self::quarter_width_widget( $this->completion_percentage( $search ) );

		// //total certificates issued
		// $html .= self::quarter_width_widget( $this->total_certificates_issued( $search ) );

		// $html .= '</div>'; //end widget row

		// //sales volumn line chart
		// $html .= self::full_width_widget( $this->sales_chart( $search ) );

		// if ( $search->product_id !== 'all_courses' ) {
		// 	$html .= self::full_width_widget( $this->lesson_completion_chart( $search ) );

		// }

		//return contents

	}

	public function get_user_data( $user_id ) {
		//create object to pass user data around
		$user = new stdClass();

		$user->id = $user_id;

		//get user data
		$user_info = get_userdata( $user_id );

		//name
		if ( empty( $user_info->first_name ) ) {
			$user->name = $user_info->display_name;
		} else {
			$user->name = $user_info->first_name . ' ' . $user_info->last_name;
		}

		//email
		$user->email = $user_info->user_email;

		//registerd date
		$user->registered_date = LLMS_Date::pretty_date( $user_info->user_registered );

		//last login date
		$last_login_date = LLMS_Date::get_last_login_date( $user->id );
		if ( $last_login_date ) {
			$user->last_login = LLMS_Date::pretty_date( LLMS_Date::get_last_login_date( $user->id ) );
		} else {
			$user->last_login = '';
		}

		//get enrollment data
		$enrollments = LLMS_Analytics::get_user_enrollments( $user->id );
		$user->courses_enrolled = 0;
		$user->memberships_enrolled = 0;
		$user->courses = array();
		$user->memberships = array();

		if ( $enrollments ) {

			foreach ( $enrollments as $enrollment ) {

				if ( $enrollment->post_type === 'course' ) {
					$user->courses_enrolled++;
					array_push( $user->courses, $enrollment );
				} elseif ( $enrollment->post_type === 'llms_membership' ) {
					$user->memberships_enrolled++;
					array_push( $user->memberships, $enrollment );
				}
			}
		}

		//get order data
		$user->orders = LLMS_Analytics::get_orders_by_user( $user_id );

		//get total number of purchases
		$user->number_of_purchases = 0;
		$user->total_money_spent = 0;

		if ( $user->orders ) {
			foreach ( $user->orders as $order ) {
				// only count purchases "none" means it was a free item
				if ( $order->order_type !== 'none' ) {

					$user->number_of_purchases++;
					$user->total_money_spent += $order->order_total;

				}

			}

		}

		return $user;

	}


	public static function user_profile( $user ) {

		$html = '<p class="llms-label">' . __( 'Account Info', 'lifterlms' ) . '</p>';

		$html .= '<div class="llms-user-profile">';

		$html .= '<p>' . $user->email . '</p>';
		$html .= '<p>Registered: ' . $user->registered_date . '</p>';
		$html .= '<p>Last Login: ' . $user->last_login . '</p>';
		$html .= '<p><a href="' . get_edit_user_link( $user->id ) . '">Edit Profile</a></p>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Total courses enrolled in
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_courses_enrolled( $user ) {
		$html = '<p class="llms-label">' . __( 'Courses', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $user->courses_enrolled . '</h1>';

		return $html;
	}

	/**
	 * Total memberships enrolled in
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_memberships_enrolled( $user ) {

		$html = '<p class="llms-label">' . __( 'Memberships', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $user->memberships_enrolled . '</h1>';

		return $html;
	}

	/**
	 * Total purchases
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_purchases( $user ) {
		$html = '<p class="llms-label">' . __( 'Purchases', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $user->number_of_purchases . '</h1>';

		return $html;
	}

	/**
	 * Total money spent
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_money_spent( $user ) {
		$html = '<p class="llms-label">' . __( 'Money Spent', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Number::format_money_no_decimal( $user->total_money_spent ) . '</h1>';

		return $html;
	}



	public function student_course_list( $user ) {

		//$headers = array( 'Last', 'First', 'Enrolled', 'Completion', 'Last Lesson Completed' );

		if ( $user ) {

			$courses_arrays = LLMS_Analytics::get_courses_by_user_table( $user );

			//array_unshift($students, $headers);
		}

		$html = '<p class="llms-label">' . __( 'Courses', 'lifterlms' ) . '</p>';
		$html .= '<script>
			var student_course_list = ' . json_encode( $courses_arrays ) . '
			</script>';
		$html .= '<div id="student_course_table" class="llms-chart"></div>';

		return $html;

	}


	public function student_membership_list( $user ) {

		if ( $user ) {

			$memberships_arrays = LLMS_Analytics::get_memberships_by_user_table( $user );

		}

		$html = '<p class="llms-label">' . __( 'Memberships', 'lifterlms' ) . '</p>';
		$html .= '<script>
			var student_membership_list = ' . json_encode( $memberships_arrays ) . '
			</script>';
		$html .= '<div id="student_membership_table" class="llms-chart"></div>';

		return $html;

	}

	/**
	 * Total sales html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_students_all_time( $search ) {

		$html = '<p class="llms-label">' . __( 'All Students', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_users( $search->students ) . '</h1>';

		return $html;
	}

	/**
	 * Total units sold html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_current_students( $search ) {

		$html = '<p class="llms-label">' . __( 'Current Students', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_current_enrolled_users( $search->students ) . '</h1>';

		return $html;
	}

	/**
	 * Total coupons used html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function completion_percentage( $search ) {

		$html = '<p class="llms-label">' . __( 'Completion %', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Number::whole_number( LLMS_Analytics::course_completion_percentage( $search->students ) ) . '%</h1>';

		return $html;
	}

	/**
	 * Total coupon $ used
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_certificates_issued( $search ) {

		$html = '<p class="llms-label">' . __( 'Certificates Issued', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_certs_issued( $search->product_id ) . '</h1>';

		return $html;
	}





	/**
	 * save students to the database
	 *
	 * @return LLMS_Admin_students::save_fields
	 */
	public function save() {
		$students = $this->get_students();

		LLMS_Admin_Students::save_search_fields( $students );

	}

	/**
	 * get students from the database
	 *
	 * @return array
	 */
	public function output() {
		$students = $this->get_students( );

			LLMS_Admin_Students::output_html( $students );
	}

}

return new LLMS_Students_Profile();
