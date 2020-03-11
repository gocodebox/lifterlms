<?php
/**
 * Course Handler Class
 *
 * Main Handler for course management in LifterLMS
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Course_Handler
 *
 * @since 1.0.0
 * @deprecated 3.37.12
 */
class LLMS_Course_Handler {

	/**
	 * Constructor
	 *
	 * @since 3.37.12
	 * @deprecated 3.37.12
	 *
	 * @param mixed $lesson Lesson.
	 * @return void
	 */
	public function __construct( $lesson ) {}

	/**
	 * Get users not enrolled
	 *
	 * @since 1.0.0
	 * @deprecated 3.37.12
	 *
	 * @param int   $post_id           Post ID.
	 * @param int[] $enrolled_students Array of WP_User IDs.
	 * @return array
	 */
	public static function get_users_not_enrolled( $post_id, $enrolled_students = array() ) {

		llms_deprecated_function( 'LLMS_Course_Handler::get_users_not_enrolled', '3.37.12', $replacement );

		if ( empty( $post_id ) ) {
			return false;
		}

		$enrolled_student_ids = array();

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
