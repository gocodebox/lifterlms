<?php
/**
 * Core functions used exclusively on the admin panel
 *
 * @since    3.0.0
 * @version  3.30.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create a Page & save it's id as an option
 *
 * @param    string $slug     page slug
 * @param    string $title    page title
 * @param    string $content  page content
 * @param    string $option   option name
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
	} else {
		$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	$page_id = apply_filters( 'llms_create_page_id', $page_id, $slug, $content );
	if ( $page_id ) {
		if ( $option ) {
			update_option( $option, $page_id );
		}
		return $page_id;
	}

	// look in the trashed page by content
	if ( strlen( $content ) > 0 ) {
		$trashed_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$content}%" ) );
	} else {
		$trashed_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
	}

	// if we find it in the trash move it out of the trash
	if ( $trashed_id ) {
		$page_id   = $trashed_id;
		$page_data = array(
			'ID'          => $page_id,
			'post_status' => 'publish',
		);
		wp_update_post( $page_data );
	} else {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => get_current_user_id() ? get_current_user_id() : 1,
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_content'   => $content,
			'comment_status' => 'closed',
		);
		$page_id   = wp_insert_post( apply_filters( 'llms_create_page', $page_data ) );
	}
	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;

}

/**
 * Retrieve available products from the LifterLMS.com API
 *
 * @return   array
 * @since    3.22.0
 * @version  3.22.0
 */
function llms_get_add_ons( $use_cache = true ) {

	$data = false;
	if ( $use_cache ) {
		$data = get_transient( 'llms_products_api_result' );
	}

	if ( false === $data ) {

		$req  = new LLMS_Dot_Com_API( '/products', array(), 'GET' );
		$data = $req->get_result();

		if ( $req->is_error() ) {
			return $data;
		}

		set_transient( 'llms_products_api_result', $data, DAY_IN_SECONDS );

	}

	return $data;

}

/**
 * Instantiate a new LLMS_Add_On
 *
 * @param    array  $addon       add-on data
 * @param    string $lookup_key  if $addon is a string, this determines how to lookup the addon from the available list of addons
 * @return   obj
 * @since    3.22.0
 * @version  3.22.0
 */
function llms_get_add_on( $addon = array(), $lookup_key = 'id' ) {
	if ( class_exists( 'LLMS_Helper_Add_On' ) ) {
		return new LLMS_Helper_Add_On( $addon, $lookup_key );
	}
	return new LLMS_Add_On( $addon, $lookup_key );
}

/**
 * Get an array of available course/membership sales page options
 *
 * @return   array
 * @since    3.23.0
 * @version  3.23.0
 */
function llms_get_sales_page_types() {
	return apply_filters(
		'llms_sales_page_types',
		array(
			'none'    => __( 'Display default course content', 'lifterlms' ),
			'content' => __( 'Show custom content', 'lifterlms' ),
			'page'    => __( 'Redirect to WordPress Page', 'lifterlms' ),
			'url'     => __( 'Redirect to custom URL', 'lifterlms' ),
		)
	);
}

/**
 * Get an array of available course/membership checkout redirection options
 *
 * @since    3.30.0
 * @version  3.30.0
 *
 * @param    string $product_type The product type, Course or Membership
 * @return   array
 */
function llms_get_checkout_redirection_types( $product_type = '' ) {

	$product_type = empty( $product_type ) ? __( 'Course/Membership', 'lifterlms' ) : $product_type;

	return apply_filters(
		'llms_checkout_redirection_types',
		array(
			'self' => sprintf( __( '(Default) Return to %s', 'lifterlms' ), $product_type ),
			'page' => __( 'Redirect to a WordPress Page', 'lifterlms' ),
			'url'  => __( 'Redirect to a custom URL', 'lifterlms' ),
		)
	);
}

/**
 * Add a "merge code" button that to auto-add merge codes to email & etc...
 *
 * @param    string  $target  target to add the merge code to
 *                            accepts the ID of a tinymce editor
 *                            a DOM ID (#element-id)
 *                            and fallback to outputting an alert where the code can be copied from
 * @param    boolean $echo    if truthy, echos the HTML, otherwise returns it
 * @param    array   $codes   optional array of custom codes to pass in, otherwise the codes are determined
 *                            what is available for the post type
 * @return   void|string
 * @since    3.1.0
 * @version  3.17.4
 */
function llms_merge_code_button( $target = 'content', $echo = true, $codes = array() ) {

	$screen = get_current_screen();

	if ( ! $codes && $screen ) {

		if ( isset( $screen->post_type ) ) {

			switch ( $screen->post_type ) {

				case 'llms_certificate':
					$codes = array(
						'{site_title}'    => __( 'Site Title', 'lifterlms' ),
						'{site_url}'      => __( 'Site URL', 'lifterlms' ),
						'{current_date}'  => __( 'Earned Date', 'lifterlms' ),
						'{first_name}'    => __( 'Student First Name', 'lifterlms' ),
						'{last_name}'     => __( 'Student Last Name', 'lifterlms' ),
						'{email_address}' => __( 'Student Email', 'lifterlms' ),
						'{student_id}'    => __( 'Student User ID', 'lifterlms' ),
						'{user_login}'    => __( 'Student Username', 'lifterlms' ),
					);

					break;

				case 'llms_email':
					$codes = array(
						'{site_title}'    => __( 'Website Title', 'lifterlms' ),
						'{site_url}'      => __( 'Website URL', 'lifterlms' ),
						'{email_address}' => __( 'Student Email Address', 'lifterlms' ),
						'{user_login}'    => __( 'Student Username', 'lifterlms' ),
						'{first_name}'    => __( 'Student First Name', 'lifterlms' ),
						'{last_name}'     => __( 'Student Last Name', 'lifterlms' ),
						'{current_date}'  => __( 'Current Date', 'lifterlms' ),
					);

					break;

				default:
					$codes = array();

			}// End switch().
		}// End if().
	}// End if().

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
