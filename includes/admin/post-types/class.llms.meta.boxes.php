<?php
/**
* Admin base Metabox Class
*
* sets up base metabox functionality and global save.
*
* @since   1.0.0
* @version 3.16.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Meta_Boxes {

	/**
	* array of collected errors.
	* @access public
	* @var string
	*/
	private static $errors = array();

	/**
	 * Constructor
	 * @return   void
	 * @since    1.0.0
	 * @version  3.16.0
	 */
	public function __construct() {

		// achievements
		new LLMS_Meta_Box_Achievement();

		// certs
		new LLMS_Meta_Box_Certificate();

		// emails
		new LLMS_Meta_Box_Email_Settings();

		// engagements
		new LLMS_Meta_Box_Engagement();

		// membership restriction metabox
		new LLMS_Meta_Box_Access();

		// courses
		new LLMS_Meta_Box_Course_Options();

		// memberships
		new LLMS_Meta_Box_Membership();

		// courses & memberships
		require_once 'meta-boxes/class.llms.meta.box.course.builder.php';
		require_once 'meta-boxes/class.llms.meta.box.visibility.php';
		require_once 'meta-boxes/class.llms.meta.box.instructors.php';
		new LLMS_Meta_Box_Product();
		new LLMS_Meta_Box_Students();

		// lessons
		require_once 'meta-boxes/class.llms.meta.box.lesson.php';

		// coupons
		new LLMS_Meta_Box_Coupon();

		// orders
		new LLMS_Meta_Box_Order_Submit();
		new LLMS_Meta_Box_Order_Details();
		new LLMS_Meta_Box_Order_Transactions();
		new LLMS_Meta_Box_Order_Enrollment();
		new LLMS_Meta_Box_Order_Notes();

		// vouchers
		new LLMS_Meta_Box_Voucher();

		add_action( 'add_meta_boxes', array( $this, 'hide_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'refresh_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'get_meta_boxes' ), 10 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );

		add_action( 'lifterlms_process_llms_voucher_meta', 'LLMS_Meta_Box_Voucher_Export::export', 10, 2 );

		//Error handling
		add_action( 'admin_notices', array( $this, 'display_errors' ) );
		add_action( 'shutdown', array( $this, 'set_errors' ) );

	}

	/**
	 * Add error messages from metaboxes
	 *
	 * @param string $text
	 */
	public static function add_error( $text ) {
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
	* @return   void
	* @since    1.0.0
	* @version  3.16.0
	*/
	public function get_meta_boxes() {

		add_action( 'media_buttons', 'llms_merge_code_button' );

		/**
		 * @todo  transition to new style metaboxes
		 */
		add_meta_box( 'lifterlms-voucher-export', __( 'Export CSV', 'lifterlms' ), 'LLMS_Meta_Box_Voucher_Export::output', 'llms_voucher', 'side', 'default' );

	}

	/**
	* Remove Metaboxes
	* @return void
	* @since    3.4.0
	* @version  3.13.0
	*/
	public function hide_meta_boxes() {

		// remove some defaults from orders
		remove_meta_box( 'commentstatusdiv', 'llms_order', 'normal' );
		remove_meta_box( 'commentsdiv', 'llms_order', 'normal' );
		remove_meta_box( 'slugdiv', 'llms_order', 'normal' );

		// remove the default submit box in favor of our custom box
		remove_meta_box( 'submitdiv', 'llms_order', 'side' );

		// remove some defaults from the course
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
		if ( in_array( $post->post_type, array( 'course', 'section', 'lesson', 'llms_order', 'llms_email', 'llms_certificate', 'llms_achievement', 'llms_engagement', 'llms_membership', 'llms_quiz', 'llms_question', 'llms_coupon', 'llms_voucher' ) ) ) {
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

			}
		}

	}

}

new LLMS_Admin_Meta_Boxes();
