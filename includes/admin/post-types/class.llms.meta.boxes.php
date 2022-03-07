<?php
/**
 * Admin base Metabox.
 *
 * @package LifterLMS/Admin/PostTypes/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Meta_Boxes class
 *
 * Sets up base metabox functionality and global save.
 *
 * @since 1.0.0
 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
 */
class LLMS_Admin_Meta_Boxes {

	/**
	 * Array of collected errors.
	 *
	 * @access public
	 * @var string
	 */
	private static $errors = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 3.16.0 Unknown.
	 * @since 6.0.0 Instantiate award engagement submit meta box.
	 *               Instantiate meta boxes to sync awarded certificates and achievements with their templates.
	 *
	 * @return void
	 */
	public function __construct() {

		// Achievements.
		new LLMS_Meta_Box_Achievement();
		new LLMS_Meta_Box_Achievement_Sync();

		// Certs.
		new LLMS_Meta_Box_Certificate();
		new LLMS_Meta_Box_Certificate_Sync();

		// Emails.
		new LLMS_Meta_Box_Email_Settings();

		// Engagements.
		new LLMS_Meta_Box_Engagement();

		// Award Engagements.
		new LLMS_Meta_Box_Award_Engagement_Submit();

		// Membership restriction metabox.
		new LLMS_Meta_Box_Access();

		// Courses.
		new LLMS_Meta_Box_Course_Options();

		// Memberships.
		new LLMS_Meta_Box_Membership();

		// Courses & memberships.
		require_once 'meta-boxes/class.llms.meta.box.course.builder.php';
		require_once 'meta-boxes/class.llms.meta.box.visibility.php';
		require_once 'meta-boxes/class.llms.meta.box.instructors.php';
		new LLMS_Meta_Box_Product();
		new LLMS_Meta_Box_Students();

		// Lessons.
		require_once 'meta-boxes/class.llms.meta.box.lesson.php';

		// Coupons.
		new LLMS_Meta_Box_Coupon();

		// Orders.
		new LLMS_Meta_Box_Order_Submit();
		new LLMS_Meta_Box_Order_Details();
		new LLMS_Meta_Box_Order_Transactions();
		new LLMS_Meta_Box_Order_Enrollment();
		new LLMS_Meta_Box_Order_Notes();

		// Vouchers.
		new LLMS_Meta_Box_Voucher();

		add_action( 'add_meta_boxes', array( $this, 'hide_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'refresh_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'get_meta_boxes' ), 10 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );

		add_action( 'lifterlms_process_llms_voucher_meta', 'LLMS_Meta_Box_Voucher_Export::export', 10, 2 );

		// Error handling.
		add_action( 'admin_notices', array( $this, 'display_errors' ) );
		add_action( 'shutdown', array( $this, 'set_errors' ) );

		// Modify the title placeholder text for achievement and certificate templates.
		add_filter( 'enter_title_here', array( $this, 'maybe_modify_title_placeholder' ), 10, 2 );

		// Add default image information for achievement and certificate templates.
		add_filter( 'admin_post_thumbnail_html', array( $this, 'maybe_modify_post_thumbnail_html' ), 10, 3 );

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
	 */
	public function set_errors() {
		update_option( 'lifterlms_errors', self::$errors );
	}

	/**
	 * Display the messages in the error dialog box
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
	 * @return   void
	 * @since    1.0.0
	 * @version  3.16.0
	 */
	public function get_meta_boxes() {

		add_action( 'media_buttons', 'llms_merge_code_button' );

		/**
		 * @todo Transition to new style metaboxes.
		 */
		add_meta_box( 'lifterlms-voucher-export', __( 'Export CSV', 'lifterlms' ), 'LLMS_Meta_Box_Voucher_Export::output', 'llms_voucher', 'side', 'default' );

	}

	/**
	 * Remove Metaboxes
	 *
	 * @return void
	 * @since    3.4.0
	 * @version  3.13.0
	 */
	public function hide_meta_boxes() {

		// Remove some defaults from orders.
		remove_meta_box( 'commentstatusdiv', 'llms_order', 'normal' );
		remove_meta_box( 'commentsdiv', 'llms_order', 'normal' );
		remove_meta_box( 'slugdiv', 'llms_order', 'normal' );

		// Remove the default submit box in favor of our custom box.
		remove_meta_box( 'submitdiv', 'llms_order', 'side' );

		// Remove some defaults from the course.
		remove_meta_box( 'postexcerpt', 'course', 'normal' );
		remove_meta_box( 'tagsdiv-course_difficulty', 'course', 'side' );

	}

	/**
	 * Modifies the featured image metabox for achievement and certificate templates.
	 *
	 * Displays the default image, text denoting that the default image is being used,
	 * and a link to the settings page where the default image can be changed.
	 *
	 * This additional content is only displayed when there's no featured image set
	 * for the template.
	 *
	 * @since 6.0.0
	 *
	 * @param string $content  Default metabox HTML.
	 * @param int    $post_id  WP_Post ID of the post being edited.
	 * @param int    $image_id Attachment ID for the saved featured image.
	 * @return string
	 */
	public function maybe_modify_post_thumbnail_html( $content, $post_id, $image_id ) {

		$post_types = array(
			'llms_achievement',
			'llms_my_achievement',
			'llms_certificate',
			'llms_my_certificate',
		);
		$post_type  = get_post_type( $post_id );
		if ( ! $image_id && in_array( $post_type, $post_types, true ) ) {

			$add_content = '';

			$class = str_replace( array( 'llms_', 'my_' ), '', $post_type ) . 's';

			$image_id = llms()->$class()->get_default_image_id();
			$alt      = $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : __( 'Default image', 'lifterlms' );

			$add_content = '<p><img alt="' . trim( wp_strip_all_tags( $alt ) ) . '" src="' . esc_url( llms()->$class()->get_default_image( $post_id ) ) . '" /></p>';

			$settings_url = admin_url( 'admin.php?page=llms-settings&tab=engagements' );
			$add_content .= '<p class="howto">' . __( 'Using the global default.', 'lifterlms' ) . ' <a href="' . esc_url( $settings_url ) . '">' . __( 'Edit', 'lifterlms' ) . '</a></p>';

			$content = $add_content . $content;
		}

		return $content;

	}

	/**
	 * Modifies the placeholder text for the post title field.
	 *
	 * This is used to denote that the achievement and certificate template title fields
	 * are for internal use only to help avoid confusion as to why there are two separate
	 * titles.
	 *
	 * @since 6.0.0
	 *
	 * @param string  $placeholder Default placeholder text.
	 * @param WP_Post $post        Post object.
	 * @return string
	 */
	public function maybe_modify_title_placeholder( $placeholder, $post ) {
		$post_types = array(
			'llms_achievement',
			'llms_certificate',
		);
		if ( in_array( $post->post_type, $post_types, true ) ) {
			$placeholder = sprintf(
				'%1$s (%2$s)',
				$placeholder,
				_x( 'for internal use only', 'added achievement and certificate template post title placeholder', 'lifterlms' )
			);
		}

		return $placeholder;
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
	 * @since Unknown
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @param int     $post_id WP Post ID.
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	public function validate_post( $post_id, $post ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		} elseif ( empty( $post_id ) || empty( $post ) ) {
			return false;
		} elseif ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return false;
		} elseif ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return false;
		} elseif ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return false;
		}

		return true;
	}

	/**
	 * Check whether the post is a LifterLMS post type.
	 *
	 * @since unknown
	 * @since 6.0.0 Added 'llms_my_achievement' and 'llms_my_certificate'.
	 *
	 * @param WP_Post $post WP_Post instance.
	 * @return boolean
	 */
	public function is_llms_post_type( $post ) {
		$post_types = array(
			'course',
			'section',
			'lesson',
			'llms_order',
			'llms_email',
			'llms_certificate',
			'llms_my_certificate',
			'llms_achievement',
			'llms_my_achievement',
			'llms_engagement',
			'llms_membership',
			'llms_quiz',
			'llms_question',
			'llms_coupon',
			'llms_voucher',
		);

		/**
		 * Filters the post type names that are secific of LifterLMS.
		 *
		 * Used to determine whether or not fire actions of the type "lifterlms_process_{$post->post_type}_meta" on save.
		 *
		 * @since 6.0.0
		 *
		 * @param string[] $post_types Array of post type names.
		 * @param WP_Post  $post       WP_Post instance.
		 */
		$post_types = apply_filters( 'llms_metaboxes_llms_post_types', $post_types, $post );

		if ( in_array( $post->post_type, $post_types, true ) ) {
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

		if ( self::validate_post( $post_id, $post ) ) {

			if ( self::is_llms_post_type( $post ) ) {

				do_action( 'lifterlms_process_' . $post->post_type . '_meta', $post_id, $post );

			}
		}

	}

}

new LLMS_Admin_Meta_Boxes();
