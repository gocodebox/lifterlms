<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Students
*
* Allows users to add and remove students from a course. Only displays on course post.
*/
class LLMS_Meta_Box_Students {

	/**
	 * Enroll a student in the course
	 *
	 * @param int $user_id  WP User ID of the student
	 * @param int $post_id  WP Post ID of the course
	 *
	 * @return void
	 */
	public static function add_student( $user_id, $post_id ) {

		if ( empty( $user_id ) || empty( $post_id ) ) {
			return false;
		}

		// create a free order
		self::create_order( $user_id, $post_id );

		// enroll the student
		llms_enroll_student( $user_id, $post_id );

		// trigger an action
		do_action( 'lifterlms_student_added_by_admin', $user_id, $post_id );

	}


	/**
	 * Removes the student from the course by setting the date to 0:00:00
	 * @param int $user_id [ID of the user]
	 * @param int $post_id [ID of the post]
	 *
	 * @return void
	 */
	public static function remove_student( $user_id, $post_id ) {
		global $wpdb;

		if ( empty( $user_id ) || empty( $post_id ) ) {
				return;
		}

		$user_metadatas = array(
			'_start_date' => 'yes',
			'_status' => 'Enrolled',
		);

		$table_name = $wpdb->prefix . 'lifterlms_order';

		$order_id = $wpdb->get_results( $wpdb->prepare( 'SELECT order_post_id FROM '.$table_name.' WHERE user_id = %s and product_id = %d', $user_id, $post_id ) );

		foreach ($order_id as $key => $value) {
			if ($order_id[ $key ]->order_post_id) {
				wp_delete_post( $order_id[ $key ]->order_post_id );
			}
		}

		foreach ( $user_metadatas as $key => $value ) {
			$update_user_postmeta = $wpdb->delete( $wpdb->prefix .'lifterlms_user_postmeta',
				array(
				'user_id' 			=> $user_id,
				'post_id' 			=> $post_id,
				'meta_key'			=> $key,
				'meta_value'		=> $value,
				)
			);
		}

		do_action( 'lifterlms_student_removed_by_admin', $user_id, $post_id );
	}

	/**
	 * Creates a order post to associate with the enrollment of the user.
	 * @param int $user_id [ID of the user]
	 * @param int $post_id [ID of the post]
	 *
	 * @return void
	 */
	public static function create_order( $user_id, $post_id ) {

		$handle = LLMS()->checkout();
		$handle->create( $user_id, $post_id );

	}


	/**
	 * Static save method
	 *
	 * Triggers add or remove method based on selection values.
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {

		if ( isset( $_POST['_add_new_user'] ) && $_POST['_add_new_user'] != '') {

			//triggers add_student static method
			foreach ($_POST['_add_new_user'] as $user_id) {
				self::add_student( $user_id, $post_id );
			}

		}

		if ( isset( $_POST['_remove_student'] ) && $_POST['_remove_student'] != '') {

			//triggers remove_student static method
			foreach ($_POST['_remove_student'] as $user_id) {
				self::remove_student( $user_id, $post_id );
			}

		}

	}

}
