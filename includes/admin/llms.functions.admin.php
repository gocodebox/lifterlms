<?php
/**
 * Core functions used exlusively on the admin panel
 */

/**
 * Create a Page & save it's id as an option
 * @param    string     $slug     page slug
 * @param    string     $title    page title
 * @param    string     $content  page content
 * @param    string     $option   option name
 * @return   int                  page id
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_create_page( $slug, $title = '', $content = '', $option = '' ) {

	$option_val = get_option( $option );

	// see if there's a valid page already stored for the option we're trying to create
	if ( $option_val && is_numeric( $option_val ) && ( $page_object = get_post( $option_val ) ) ) {
		if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ) ) ) {
			return $page_object->ID;
		}
	}

	global $wpdb;

	// Search for an existing page with the specified page content like a shortcode
	if ( strlen( $content ) > 0 ) {
		$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$content}%" ) );
	}
	// Search for an existing page with the specified page slug
	else {
		$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	$page_id = apply_filters( 'llms_create_page_id', $page_id, $slug, $content );
	if ( $page_id ) {
		if ( $option ) {
			update_option( $option, $page_id );
		}
		return $page_id;
	}

	// look in the trashd trashed page by content
	if ( strlen( $content ) > 0 ) {
		$trashed_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$content}%" ) );
	}
	// look in the trashd trashed page by slug
	else {
		$trashed_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
	}

	// if we find it in the trash move it out of the trash
	if ( $trashed_id ) {
		$page_id   = $trashed_id;
		$page_data = array(
			'ID'             => $page_id,
			'post_status'    => 'publish',
		);
	 	wp_update_post( $page_data );
	}
	// otherwise create it
	else {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => get_current_user_id() ? get_current_user_id() : 1,
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_content'   => $content,
			'comment_status' => 'closed',
		);
		$page_id = wp_insert_post( apply_filters( 'llms_create_page', $page_data ) );
	}
	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;

}
