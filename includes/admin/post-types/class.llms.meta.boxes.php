<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin base MetaBox Class
*
* sets up base metabox functionality and global save.
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_Meta_Boxes {

	/**
	* array of collected errors.
	* @access public
	* @var string
	*/
	private static $errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'hide_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'refresh_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'get_meta_boxes' ), 10 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );

		// Save Course Meta Boxes
		add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_Product::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_Video::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_Course_Syllabus::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_General::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_Students::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_Course_Outline::save', 10, 2 );

		add_action( 'lifterlms_process_section_meta', 'LLMS_Meta_Box_Section_Tree::save', 10, 2 );
		add_action( 'lifterlms_process_lesson_meta', 'LLMS_Meta_Box_Lesson_Tree::save', 10, 2 );
		add_action( 'lifterlms_process_lesson_meta', 'LLMS_Meta_Box_Video::save', 10, 2 );
		add_action( 'lifterlms_process_lesson_meta', 'LLMS_Meta_Box_General::save', 10, 2 );
		add_action( 'lifterlms_process_lesson_meta', 'LLMS_Meta_Box_Lesson_Options::save', 10, 2 );
		add_action( 'lifterlms_process_llms_email_meta', 'LLMS_Meta_Box_Email_Settings::save', 10, 2 );
		add_action( 'lifterlms_process_llms_certificate_meta', 'LLMS_Meta_Box_Certificate_Options::save', 10, 2 );
		add_action( 'lifterlms_process_llms_achievement_meta', 'LLMS_Meta_Box_Achievement_Options::save', 10, 2 );
		add_action( 'lifterlms_process_llms_engagement_meta', 'LLMS_Meta_Box_Engagement_Options::save', 10, 2 );
		add_action( 'lifterlms_process_llms_membership_meta', 'LLMS_Meta_Box_Product::save', 10, 2 );
		add_action( 'lifterlms_process_llms_membership_meta', 'LLMS_Meta_Box_Expiration::save', 10, 2 );
		add_action( 'lifterlms_process_llms_membership_meta', 'LLMS_Meta_Box_Membership::save', 10, 2 );
		add_action( 'lifterlms_process_membership_access', 'LLMS_Meta_Box_Access::save', 10, 2 );
		add_action( 'lifterlms_process_llms_quiz_meta', 'LLMS_Meta_Box_Quiz::save', 10, 2 );
		add_action( 'lifterlms_process_llms_quiz_meta', 'LLMS_Meta_Box_Quiz_Questions::save', 10, 2 );
		add_action( 'lifterlms_process_llms_question_meta', 'LLMS_Meta_Box_Question_General::save', 10, 2 );
		add_action( 'lifterlms_process_llms_coupon_meta', 'LLMS_Meta_Box_Coupon_Options::save', 10, 2 );

		add_action( 'lifterlms_process_llms_voucher_meta', 'LLMS_Meta_Box_Voucher::save', 10, 2 );
		add_action( 'lifterlms_process_llms_voucher_meta', 'LLMS_Meta_Box_Voucher_Export::export', 10, 2 );

		//Error handling
		add_action( 'admin_notices', array( $this, 'display_errors' ) );
		add_action( 'shutdown', array( $this, 'set_errors' ) );
	}

	/**
	 * Get error messages from metaboxes
	 *
	 * @param string $text
	 */
	public static function get_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Save messages to the database
	 *
	 * @param string $text
	 */
	public function set_errors() {
		update_option( 'lifterlms_errors', self::$errors );
	}

	/**
	 * Display the messages in the error dialog box
	 *
	 * @param string $text
	 */
	public function display_errors() {
		$errors = get_option( 'lifterlms_errors' );

		if ( empty( $errors ) ) {
			return;
		}

		$errors = maybe_unserialize( $errors );

		echo '<div id="lifterlms_errors" class="error"><p>';

		foreach ( $errors as $error ) {

			echo esc_html( $error );
		}

		echo '</p></div>';

		delete_option( 'lifterlms_errors' );

	}

	/**
	* Add Metaboxes
	*
	* @return void
	*/
	public function get_meta_boxes() {

		$post_types = get_option( 'lifterlms_membership_restricted_box', array( 'course', 'page' ) );
		foreach ( $post_types  as $key => $post_type ) {
			add_meta_box( 'lifterlms-membership-access', __( 'Membership Access', 'lifterlms' ), 'LLMS_Meta_Box_Access::output', $post_type, 'side' );
		}

		//===================================
		// New meta box style
		//===================================
		add_meta_box( 'lifterlms-course-main', __( 'Course Options', 'lifterlms' ), 'LLMS_Meta_Box_Main::output', 'course', 'normal', 'high' );
		add_meta_box( 'lifterlms-email-settings', __( 'Email Settings', 'lifterlms' ), 'LLMS_Meta_Box_Email_Settings::output', 'llms_email', 'normal', 'high' );
		add_meta_box( 'lifterlms-membership-settings', __( 'Membership Settings', 'lifterlms' ), 'LLMS_Meta_Box_Membership::output', 'llms_membership', 'normal', 'high' );
		add_meta_box( 'lifterlms-lesson-settings', __( 'Lesson Settings', 'lifterlms' ), 'LLMS_Meta_Box_Lesson::output', 'lesson', 'normal', 'high' );
		add_meta_box( 'lifterlms-achievement-settings', __( 'Achievement Settings', 'lifterlms' ), 'LLMS_Meta_Box_Achievement::output', 'llms_achievement', 'normal', 'high' );
		add_meta_box( 'lifterlms-certificate-settings', __( 'Certificate Settings', 'lifterlms' ), 'LLMS_Meta_Box_Certificate::output', 'llms_certificate', 'normal', 'high' );
		//add_meta_box( 'lifterlms-engagement-settings', __( 'Engagement Settings', 'lifterlms' ), 'LLMS_Meta_Box_Engagement::output', 'llms_engagement', 'normal', 'high' );
		add_meta_box( 'lifterlms-coupon-settings', __( 'Coupon Settings', 'lifterlms' ), 'LLMS_Meta_Box_Coupon::output', 'llms_coupon', 'normal', 'high' );
		add_meta_box( 'lifterlms-voucher-settings', __( 'Voucher Settings', 'lifterlms' ), 'LLMS_Meta_Box_Voucher::output', 'llms_voucher', 'normal', 'high' );
		add_meta_box( 'lifterlms-quiz-settings', __( 'Quiz Settings', 'lifterlms' ), 'LLMS_Meta_Box_Quiz::output', 'llms_quiz', 'normal', 'high' );

		//===================================
		// Old meta box style
		//===================================
		add_meta_box( 'lifterlms-course-outline', __( 'Course Outline', 'lifterlms' ), 'LLMS_Meta_Box_Course_Outline::output', 'course', 'normal', 'high' );
		add_meta_box( 'lifterlms-section-tree', __( 'Section Tree', 'lifterlms' ), 'LLMS_Meta_Box_Section_Tree::output', 'section', 'side' );
		add_meta_box( 'lifterlms-lesson-tree', __( 'Course Outline', 'lifterlms' ), 'LLMS_Meta_Box_Lesson_Tree::output', 'lesson', 'side' );
		//add_meta_box( 'lifterlms-lesson-video', __( 'Featured Media', 'lifterlms' ), 'LLMS_Meta_Box_Video::output', 'lesson', 'normal', 'high' );
		//add_meta_box( 'lifterlms-lesson-general', __( 'General Settings', 'lifterlms' ), 'LLMS_Meta_Box_General::output', 'lesson', 'normal' );
		//add_meta_box( 'lifterlms-lesson-options', __( 'Lesson Options', 'lifterlms' ), 'LLMS_Meta_Box_Lesson_Options::output', 'lesson', 'normal' );
		//add_meta_box( 'lifterlms-certificate-options', __( 'Certificate Options', 'lifterlms' ), 'LLMS_Meta_Box_Certificate_Options::output', 'llms_certificate', 'normal' );
		//add_meta_box( 'lifterlms-achievement-options', __( 'Achievement Options', 'lifterlms' ), 'LLMS_Meta_Box_Achievement_Options::output', 'llms_achievement', 'normal' );
		add_meta_box( 'lifterlms-engagement-options', __( 'Engagement Options', 'lifterlms' ), 'LLMS_Meta_Box_Engagement_Options::output', 'llms_engagement', 'normal' );
		add_meta_box( 'lifterlms-voucher-export', __( 'Export CSV', 'lifterlms' ), 'LLMS_Meta_Box_Voucher_Export::output', 'llms_voucher', 'side', 'default' );
		//add_meta_box( 'lifterlms-expiration-options', __( 'Membership Expiration', 'lifterlms' ), 'LLMS_Meta_Box_Expiration::output', 'llms_membership', 'normal' );
		add_meta_box( 'lifterlms-order-general', __( 'Order Details', 'lifterlms' ), 'LLMS_Meta_Box_Order::output', 'order', 'normal', 'high' );
		add_meta_box( 'lifterlms-quiz-questions', __( 'Quiz Questions', 'lifterlms' ), 'LLMS_Meta_Box_Quiz_Questions::output', 'llms_quiz', 'normal' );
		add_meta_box( 'lifterlms-question-general', __( 'Question Settings', 'lifterlms' ), 'LLMS_Meta_Box_Question_General::output', 'llms_question', 'normal' );
		//add_meta_box( 'lifterlms-coupon-options', __( 'Coupon Options', 'lifterlms' ), 'LLMS_Meta_Box_Coupon_Options::output', 'llms_coupon', 'normal' );
	}

	/**
	* Remove Metaboxes
	*
	* @return void
	*/
	public function hide_meta_boxes() {
		remove_meta_box( 'postexcerpt', 'course', 'normal' );
		remove_meta_box( 'tagsdiv-course_difficulty','course','side' );
	}

	/**
	* Updates global $post variable
	*
	* @return void
	*/
	public function refresh_meta_boxes() {
		global $post;
	}

	/**
	* Validates post and metabox data before saving.
	*
	* @return bool
	* @param $post, $post_id
	*/
	public function validate_post( $post_id, $post ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		} elseif ( empty( $post_id ) || empty( $post ) ) {
			return false;
		} elseif ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return false;
		} elseif ( empty( $_POST['lifterlms_meta_nonce'] ) || ! wp_verify_nonce( $_POST['lifterlms_meta_nonce'], 'lifterlms_save_data' ) ) {
			return false;
		} elseif ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return false;
		}

		return true;
	}

	public function is_llms_post_type( $post ) {
		if ( in_array( $post->post_type, array( 'course', 'section', 'lesson', 'order', 'llms_email', 'llms_certificate', 'llms_achievement', 'llms_engagement', 'llms_membership', 'llms_quiz', 'llms_question', 'llms_coupon', 'llms_voucher' ) ) ) {
			return true;
		}
	}

	/**
	* Global Metabox Save
	*
	* @return void
	* @param $post, $post_id
	*/
	public function save_meta_boxes( $post_id, $post ) {
		if ( LLMS_Admin_Meta_Boxes::validate_post( $post_id, $post ) ) {
			if ( LLMS_Admin_Meta_Boxes::is_llms_post_type( $post ) ) {
				do_action( 'lifterlms_process_' . $post->post_type . '_meta', $post_id, $post );
				do_action( 'lifterlms_process_membership_access', $post_id, $post );
			} else {
				do_action( 'lifterlms_process_membership_access', $post_id, $post );
			}
		}

	}

}

new LLMS_Admin_Meta_Boxes();
