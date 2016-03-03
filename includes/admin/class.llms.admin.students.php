<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }


/**
* Admin students Class
*
* students field Factory
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_Students {

	/**
	* students array
	* @access private
	* @var array
	*/
	private static $students = array();

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
	* Inits $students and includes students base class.
	*
	* @return self::$students array
	*/
	public static function get_students_tabs() {

		if ( empty( self::$students ) ) {
			$students = array();

			include_once( 'students/class.llms.students.page.php' );

			$students[] = include( 'students/class.llms.students.dashboard.php' );
			$students[] = include( 'students/class.llms.students.profile.php' );
			//$students[] = include( 'students/class.llms.students.courses.php' );
			//$students[] = include( 'students/class.llms.students.memberships.php' );

			self::$students = apply_filters( 'lifterlms_get_students_pages', $students );

		}

		return self::$students;
	}

	/**
	* Save method. Saves all fields on current tab
	*
	* @return void
	*/
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'lifterlms-students' ) ) {
			die( __( 'Whoa! something went wrong there!. Please refresh the page and retry.', 'lifterlms' ) );
		}

	   	do_action( 'lifterlms_students_save_' . $current_tab );
	    do_action( 'lifterlms_update_options_' . $current_tab );
	    do_action( 'lifterlms_update_options' );

		self::set_message( __( 'Your students have been saved.', 'lifterlms' ) );

		do_action( 'lifterlms_students_saved' );
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
	* display messages in students
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
	* students Page output tabs
	*
	* @return void
	*/
	public static function output() {
		global $current_tab;

		do_action( 'lifterlms_students_start' );

		self::get_students_tabs();

		$current_tab = empty( $_GET['tab'] ) ? 'dashboard' : sanitize_title( $_GET['tab'] );

	    if ( ! empty( $_POST ) ) {
	    	self::save(); }

	    if ( ! empty( $_GET['llms_error'] ) ) {
	    	self::set_error( stripslashes( $_GET['llms_error'] ) ); }

	    self::display_messages_html();

	    $tabs = apply_filters( 'lifterlms_students_tabs_array', array() );

		include 'views/html.admin.students.php';
	}

	/**
	* Output html for students tabs.
	*
	* @return void
	*/
	public static function output_html( $students ) {
		echo $students;
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
	public static function save_search_fields( $students ) {
	    if ( empty( $_POST ) ) {
	    	return false; }

	    //sales analytics
	    if ( ! empty( $_POST['action'] ) && ( 'llms-students-search' === $_POST['action'] ) && ! empty( $_POST['_wpnonce'] ) ) {

	 		$search = new stdClass;

	 		//validate fields
	 		if ( empty( $_POST['llms_product_select'] ) ) {
	 			self::set_error( __( 'You must choose a product option.' , 'lifterlms' ) );
	 		}

	 		$search->product_id = llms_clean( $_POST['llms_product_select'] );
	 		$search->include_expired = isset( $_POST['llms_include_expired_users'] ) ? true : false;

	 		$search->students = LLMS_Analytics::get_users( $search->product_id, $search->include_expired );

	 		//set search object as session object
		    LLMS()->session->set( 'llms_students_search', $search );
	    }

	}

}
