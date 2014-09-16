<?php
/**
 * Post Types Admin
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	lifterLMS/Admin
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'LLMS_Admin_Post_Types' ) ) :

/**
 * LLMS_Admin_Post_Types Class
 */
class LLMS_Admin_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'include_post_type_handlers' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		add_action( 'delete_post', array( $this, 'delete_post' ) );
		add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
		add_action( 'untrash_post', array( $this, 'untrash_post' ) );
	}

	/**
	 * Conditonally load classes and functions only when we need them. 
	 */
	public function include_post_type_handlers() {
		include( 'post-types/class.llms.admin.meta-boxes.php' );
	}

	/**
	 * Change messages when a custom post type is updated.
	 *
	 * @param  array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['course'] = array(
			0 => '',
			1 => sprintf( __( 'Course updated. <a href="%s">View Course</a>', 'lifterlms' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'lifterlms' ),
			3 => __( 'Custom field deleted.', 'lifterlms' ),
			4 => __( 'Course updated.', 'lifterlms' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Course restored to revision from %s', 'lifterlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Course published. <a href="%s">View Course</a>', 'lifterlms' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Course saved.', 'lifterlms' ),
			8 => sprintf( __( 'Course submitted. <a target="_blank" href="%s">Preview Course</a>', 'lifterlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Course scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Course</a>', 'lifterlms' ),
			  date_i18n( __( 'M j, Y @ G:i', 'lifterlms' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Course draft updated. <a target="_blank" href="%s">Preview Course</a>', 'lifterlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * Removes variations etc belonging to a deleted post, and clears transients
	 *
	 * @access public
	 * @param mixed $id ID of post being deleted
	 * @return void
	 */
	public function delete_post( $id ) {
		global $lifterlms, $wpdb;

		if ( ! current_user_can( 'delete_posts' ) )
			return;

		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			switch( $post_type ) {
				case 'course' :

					$child_course_variations = get_children( 'post_parent=' . $id . '&post_type=course_variation' );

					if ( $child_course_variations ) {
						foreach ( $child_course_variations as $child ) {
							wp_delete_post( $child->ID, true );
						}
					}

					$child_courses = get_children( 'post_parent=' . $id . '&post_type=course' );

					if ( $child_courses ) {
						foreach ( $child_courses as $child ) {
							$child_post = array();
							$child_post['ID'] = $child->ID;
							$child_post['post_parent'] = 0;
							wp_update_post( $child_post );
						}
					}

					llms_delete_course_transients();

				break;
				case 'course_variation' :

					llms_delete_course_transients();

				break;
			}
		}
	}
}

endif;

return new LLMS_Admin_Post_Types();