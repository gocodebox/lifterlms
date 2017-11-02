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
 * @version  3.7.5
 */
function llms_create_page( $slug, $title = '', $content = '', $option = '' ) {

	$option_val = get_option( $option );

	// see if there's a valid page already stored for the option we're trying to create
	if ( $option_val && is_numeric( $option_val ) ) {
		$page_object = get_post( $option_val );
		if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ) ) ) {
			return $page_object->ID;
		}
	}

	global $wpdb;

	// Search for an existing page with the specified page content like a shortcode
	if ( strlen( $content ) > 0 ) {
		$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$content}%" ) );
	} // End if().
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
	} // End if().
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
	} // End if().
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

/**
 * Add a "merge code" button that to auto-add merge codes to email & etc...
 * @todo  utilize this on certificates
 * @param    string     $target  target to add the merge code to
 *                               accepts the ID of a tinymce editor
 *                               a DOM ID (#element-id)
 *                               and fallsback to outputting an alert where the code can be copied from
 * @param    boolean    $echo    if truthy, echos the HTML, otherwise returns it
 * @param    array      $codes   optional array of custom codes to pass in, otherwise the codes are determined
 *                               what is available for the post type
 * @return   void|string
 * @since    3.1.0
 * @version  3.8.0
 */
function llms_merge_code_button( $target = 'content', $echo = true, $codes = array() ) {

	$screen = get_current_screen();

	if ( ! $codes && $screen ) {

		if ( isset( $screen->post_type ) ) {

			switch ( $screen->post_type ) {

				case 'llms_email':

					$codes = array(
						'{site_title}' => __( 'Website Title', 'lifterlms' ),
						'{site_url}' => __( 'Website URL', 'lifterlms' ),
						'{email_address}' => __( 'Student Email Address', 'lifterlms' ),
						'{user_login}' => __( 'Student Username', 'lifterlms' ),
						'{first_name}' => __( 'Student First Name', 'lifterlms' ),
						'{last_name}' => __( 'Student Last Name', 'lifterlms' ),
						'{current_date}' => __( 'Current Date', 'lifterlms' ),
					);

				break;

				default:

					$codes = array();

			}
		}
	}

	$codes = apply_filters( 'llms_merge_codes_for_button', $codes, $screen, $target );

	if ( ! $codes ) {
		return;
	}

	ob_start();

	echo '<div class="llms-merge-code-wrapper">';

	echo '<button class="button llms-merge-code-button" type="button"><img alt="LifterLMS" src="' . LLMS()->plugin_url() . '/assets/images/lifterlms-rocket-grey.png">' . __( 'Merge Codes', 'lifterlms' ) . '</button>';

	?>
	<div class="llms-merge-codes" data-target="<?php echo $target; ?>">
		<ul>
		<?php if ( $codes ) : ?>
			<?php foreach ( $codes as $code => $desc ) : ?>
				<li data-code="<?php echo $code; ?>"><?php echo $desc; ?></li>
			<?php endforeach; ?>
		<?php else : ?>
			<li><?php _e( 'No merge codes found.', 'lifterlms' ); ?></li>
		<?php endif; ?>
		</ul>
	</div>
	<?php

	echo '</div><!-- .llms-merge-code-wrapper -->';

	$html = ob_get_clean();

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}

}
