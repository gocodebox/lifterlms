<?php
/**
 * lifterLMS Meta Boxes
 *
 * Sets up the write panels used by the custom post types
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	lifterlMS/Admin/Meta Boxes
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * LLMS_Admin_Meta_Boxes
 */
class LLMS_Admin_Meta_Boxes {

	private static $meta_box_errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes'	, array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes'	, array( $this, 'rename_meta_boxes' ), 20 );
		add_action( 'add_meta_boxes'	, array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post'			, array( $this, 'save_meta_boxes' ), 1, 2 );

		// Save Course Meta Boxes
		add_action( 'lifterlms_process_course_meta'			, 'LLMS_Meta_Box_Course_Data::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta'			, 'LLMS_Meta_Box_Course_Video::save', 10, 2 );
		add_action( 'lifterlms_process_course_meta'		, 'LLMS_Meta_Box_Course_Syllabus::save', 10, 2 );
		//add_action( 'lifterlms_process_course_meta', 'LLMS_Meta_Box_Course_Images::save', 20, 2 );

		//Error handling
		add_action( 'admin_notices'		, array( $this, 'output_errors' ) );
		add_action( 'shutdown'			, array( $this, 'save_errors' ) );
	}

	/**
	 * Add an error message
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option
	 */
	public function save_errors() {
		update_option( 'lifterlms_meta_box_errors', self::$meta_box_errors );
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'lifterlms_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="lifterlms_errors" class="error fade">';
			foreach ( $errors as $error ) {
				echo '<p>' . esc_html( $error ) . '</p>';
			}
			echo '</div>';

			// Clear
			delete_option( 'lifterlms_meta_box_errors' );
		}
	}

	/**
	 * Add Meta boxes
	 */
	public function add_meta_boxes() {
		
		//new LLMS_Meta_Box_Course_Short_Description();
		// Courses
		add_meta_box( 'postexcerpt', __( 'Course Short Description', 'lifterlms' ), 'LLMS_Meta_Box_Course_Short_Description::output', 'course', 'normal' );
		add_meta_box( 'lifterlms-course-data', __( 'Course Data', 'lifterlms' ), 'LLMS_Meta_Box_Course_Data::output', 'course', 'normal', 'high' );
		add_meta_box( 'lifterlms-course-video', __( 'Course Video', 'lifterlms' ), 'LLMS_Meta_Box_Course_Video::output', 'course', 'normal');
		add_meta_box( 'lifterlms-course-syllabus', __( 'Course Syllabus', 'lifterlms' ), 'LLMS_Meta_Box_Course_Syllabus::output', 'course', 'normal');
		//add_meta_box( 'lifterlms-course-images', __( 'Course Gallery', 'lifterlms' ), 'LLMS_Meta_Box_Course_Images::output', 'course', 'side' );
	}

	/**
	 * Remove meta boxes we don't need.
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'postexcerpt', 'course', 'normal' );
		remove_meta_box('tagsdiv-course_difficulty','course','side');
	}

	/**
	 * Rename WP meta boxes.
	 */
	public function rename_meta_boxes() {
		global $post;
	}

	/**
	 * Check if we're saving, then trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		
		// Check the nonce
		if ( empty( $_POST['lifterlms_meta_nonce'] ) || ! wp_verify_nonce( $_POST['lifterlms_meta_nonce'], 'lifterlms_save_data' ) ) {
			return;
		} 

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type
		if ( ! in_array( $post->post_type, array( 'course', 'section', 'lesson' ) ) ) {
			return;
		}

		do_action( 'lifterlms_process_' . $post->post_type . '_meta', $post_id, $post );
	}

}

new LLMS_Admin_Meta_Boxes();
