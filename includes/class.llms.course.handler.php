<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Course Handler Class
 *
 * Main Handler for course management in LifterLMS
 *
 * @author codeBOX
 */
class LLMS_Course_Handler {

	public function __construct( $lesson ) {}


	public static function get_users_not_enrolled( $post_id, $enrolled_students = array() ) {

		// no post id no deal!
		if ( empty( $post_id ) ) {
			return false;
		}

		$enrolled_student_ids = array();

		// if no enrolled users are supplied query them and generate array of user ids
		if ( empty( $enrolled_students ) ) {

			$user_args = array(
				'blog_id'     => $post_id,
				'include'     => array(),
				'exclude'     => $enrolled_students,
				'orderby'     => 'display_name',
				'order'       => 'ASC',
				'count_total' => false,
				'fields'      => 'all',
				'number'      => 500,
			);
			$all_users = get_users( $user_args );

			foreach ( $all_users as $key => $value ) {
				if ( llms_is_user_enrolled( $value->ID, $post_id ) ) {
					$enrolled_students[ $value->ID ] = $value->display_name;
					array_push( $enrolled_student_ids, $value->ID );

				}
			}
		} else {

			foreach ( $enrolled_students as $user ) {
					array_push( $enrolled_student_ids, $user->ID );
			}
		}

		// query users not enrolled
		$user_args = array(
			'blog_id'     => $GLOBALS['blog_id'],
			'include'     => array(),
			'exclude'     => $enrolled_student_ids,
			'orderby'     => 'display_name',
			'order'       => 'ASC',
			'count_total' => false,
			'fields'      => 'all',
			'number'      => 500,
		);

		return get_users( $user_args );

	}

}
