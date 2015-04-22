<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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

		//no post id no deal!
		if ( empty($post_id) ) {
			return false;
		}

		$enrolled_student_ids = array();
		$users_not_enrolled = array();

		//if no enrolled users are supplied query them and generate array of user ids
		if ( empty($enrolled_students) ) {

	    	$user_args = array(
	    		'blog_id'      => $post_id,
				'include'      => array(),
				'exclude'      => $enrolled_students,
				'orderby'      => 'display_name',
				'order'        => 'ASC',
				'count_total'  => false,
				'fields'       => 'all',
	    	);
	    	$all_users = get_users( $user_args );

	    	foreach ( $all_users as $key => $value  ) {
	    		if ( llms_is_user_enrolled( $value->ID, $post_id ) ) {
	    			$enrolled_students[$value->ID] = $value->display_name;
	    			array_push($enrolled_student_ids, $value->ID);

	    		} 
	    	}

	    } else {

	    	foreach ( $enrolled_students as $user  ) {
	    			array_push($enrolled_student_ids, $user->ID);
	    	}

	    }

	    //query users not enrolled
		$user_args = array(
    		'blog_id'      => $GLOBALS['blog_id'],
			'include'      => array(),
			'exclude'      => $enrolled_student_ids,
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'count_total'  => false,
			'fields'       => 'all',
    	);
    	
    	return get_users( $user_args );

	}
	

	// public static function duplicate_lesson($course_id, $section_id, $lesson_id ) {

	// 	if ( !isset($course_id ) || !isset($section_id ) || !isset($lesson_id ) ) {
	// 		return false;
	// 	}

	// 	//duplicate the lesson
	// 	$new_lesson_id = self::duplicate( $lesson_id );

	// 	if ( !$new_lesson_id ) {
	// 		return false;
	// 	}

	// 	//add parent section and course to new lesson
	// 	update_post_meta( $new_lesson_id, '_parent_section', $section_id );
	// 	update_post_meta( $new_lesson_id, '_parent_course', $course_id );

	// 	return $new_lesson_id;
		
	// }

	// public static function duplicate( $post_id ) {

	// 	//make sure we have a post id and it returns a post
	// 	if ( !isset($post_id ) ) {
	// 		return false;
	// 	}

	// 	$postObj = get_post( $post_id );
	// 	//last check...
	// 	if ( !isset( $postObj ) || $postObj == null ) {
	// 		return false;
	// 	}

	// 	//no going back now...
 
	// 	//create duplicate post
	// 	$args = array(
	// 		'comment_status' => $postObj->comment_status,
	// 		'ping_status'    => $postObj->ping_status,
	// 		'post_author'    => $postObj->post_author,
	// 		'post_content'   => $postObj->post_content,
	// 		'post_excerpt'   => $postObj->post_excerpt,
	// 		'post_name'      => $postObj->post_name,
	// 		'post_parent'    => $postObj->post_parent,
	// 		'post_status'    => 'publish',
	// 		'post_title'     => $postObj->post_title,
	// 		'post_type'      => $postObj->post_type,
	// 		'to_ping'        => $postObj->to_ping,
	// 		'menu_order'     => $postObj->menu_order,
	// 		'post_password'  => $postObj->post_password
	// 	);
 
	// 	//create the duplicate post
	// 	$new_post_id = wp_insert_post( $args );
 
 // 		if ( $new_post_id ) {

	// 		//get all current post terms and set them to the new post
	// 		$taxonomies = get_object_taxonomies($postObj->post_type);
	// 		foreach ($taxonomies as $taxonomy) {
	// 			$post_terms = wp_get_object_terms($postObj->ID, $taxonomy, array('fields' => 'slugs'));
	// 			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
	// 		}
	 
	//  	    // duplicate meta
	// 		$insert_meta = self::duplicate_meta( $post_id, $new_post_id );

	// 	}

	// 	return $new_post_id;

	// }

	// public static function duplicate_meta( $post_id, $new_post_id) {
	// 	global $wpdb;

	// 	//duplicate all post meta
	// 	$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		
	// 	if (count($post_meta_infos)!=0) {

	// 		$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			
	// 		foreach ($post_meta_infos as $meta_info) {
				
	// 			if ( $meta_info->meta_key !== '_parent_section'
	// 				|| $meta_info->meta_key !== '_parent_course'
	// 				|| $meta_info->meta_key !== '_prerequisite'
	// 			) {

	// 				$meta_key = $meta_info->meta_key;
	// 				$meta_value = addslashes($meta_info->meta_value);
	// 				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				
	// 			}

	// 		}

	// 		$sql_query.= implode(" UNION ALL ", $sql_query_sel);
	// 		$insert_post_meta = $wpdb->query($sql_query);

	// 		return $insert_post_meta;
	// 	}
	// }

} //end LLMS_POST_Handler
