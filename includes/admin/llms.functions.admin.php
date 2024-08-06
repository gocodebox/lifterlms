<?php
/**
 * Core functions used exclusively on the admin panel
 *
 * @package LifterLMS/Admin/Functions
 *
 * @since 3.0.0
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create a Page & save it's id as an option.
 *
 * @since 3.0.0
 * @since 3.7.5 Unknown.
 * @since 7.3.0 Strip all tags from the page title, slash the page data prior to inserting the page in the db via `wp_insert_post`.
 *              Prefer strict type comparison when using `in_array()`.
 *
 * @param string $slug    Page slug.
 * @param string $title   Page title.
 * @param string $content Page content
 * @param string $option  Option name.
 * @return int Page id.
 */
function llms_create_page( $slug, $title = '', $content = '', $option = '' ) {

	$option_val = get_option( $option );

	// See if there's a valid page already stored for the option we're trying to create.
	if ( $option_val && is_numeric( $option_val ) ) {
		$page_object = get_post( $option_val );
		if ( $page_object && 'page' === $page_object->post_type &&
				! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
			return $page_object->ID;
		}
	}

	global $wpdb;

	// Search for an existing page with the specified page content like a shortcode.
	if ( strlen( $content ) > 0 ) {
		$page_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;",
				"%{$content}%"
			)
		);// no-cache ok.
	} else {
		$page_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;",
				$slug
			)
		);// no-cache ok.
	}

	/**
	 * Filters the ID of the page to be created.
	 *
	 * @since 3.0.0
	 *
	 * @param int     $page_id The WP_Post ID of the page.
	 * @param string  $slug    The page slug.
	 * @param string  $content THe content of the page.
	 */
	$page_id = apply_filters( 'llms_create_page_id', $page_id, $slug, $content );
	if ( $page_id ) {
		if ( $option ) {
			update_option( $option, $page_id );
		}
		return $page_id;
	}

	// Look in the trashed page by content.
	if ( strlen( $content ) > 0 ) {
		$trashed_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;",
				"%{$content}%"
			)
		);// no-cache ok.
	} else {
		$trashed_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;",
				$slug
			)
		);// no-cache ok.
	}

	// If we find it in the trash move it out of the trash.
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
			'post_title'     => wp_strip_all_tags( $title ),
			'post_content'   => $content,
			'comment_status' => 'closed',
		);
		$page_id   = wp_insert_post(
			wp_slash(
				/**
				 * Filters the page data passed to create a page.
				 *
				 * The output of this filter will be slashed via `wp_slash` prior
				 * to being passed to `wp_insert_post` to prevent slashes from
				 * being stripped from the page title.
				 *
				 * @since 3.0.0
				 *
				 * @param array $page_data Array of page data.
				 */
				apply_filters( 'llms_create_page', $page_data )
			)
		);
	}
	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

/**
 * Retrieve available products from the LifterLMS.com API
 *
 * @since 3.22.0
 *
 * @return array {
 *     Array of LifterLMS add-on data from the LifterLMS.com products api.
 *
 *     @type array   $categories Associative array of add-on category information, mapping ID to Title.
 *     @type array[] $items      List of add-ons definition arrays.
 * }
 */
function llms_get_add_ons( $use_cache = true ) {

	$data = $use_cache ? get_transient( 'llms_products_api_result' ) : false;

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
 * Instantiate a new LLMS_Add_On object
 *
 * @since 3.22.0
 *
 * @param string|array $addon      Add-on data array or a string (such as an ID or update file path) used to lookup the addon.
 * @param string       $lookup_key If $addon is a string, this determines how to lookup the addon from the available list of addons.
 * @return LLMS_Add_On|LLMS_Helper_Add_On
 */
function llms_get_add_on( $addon = array(), $lookup_key = 'id' ) {
	if ( class_exists( 'LLMS_Helper_Add_On' ) ) {
		return new LLMS_Helper_Add_On( $addon, $lookup_key );
	}
	return new LLMS_Add_On( $addon, $lookup_key );
}

/**
 * Retrieves HTML for a Dashicon wrapped in an anchor.
 *
 * A utility for adding links to external documentation.
 *
 * @since 7.0.0
 *
 * @param string $url The URL of the anchor tag.
 * @param array  $args {
 *     An array of optional configuration options.
 *
 *     @type integer $size  The size of the icon. Default 18.
 *     @type string  $title The title attribute of the anchor tag. Default: "More information".
 *     @type string  $icon  The Dashicon icon to use, {@see @link https://developer.wordpress.org/resource/dashicons/}. Default: "external".
 * }
 * @return string
 */
function llms_get_dashicon_link( $url, $args = array() ) {

	$args = wp_parse_args(
		$args,
		array(
			'size'  => 18,
			'title' => esc_attr__( 'More information', 'lifterlms' ),
			'icon'  => 'external',
		)
	);

	$dashicon = sprintf(
		'<span class="dashicons dashicons-%1$s" style="font-size:%2$dpx;width:%2$dpx;height:%2$dpx"></span>',
		esc_attr( $args['icon'] ),
		$args['size']
	);

	return sprintf(
		'<a href="%1$s" style="text-decoration:none;" target="_blank" rel="noreferrer" title="%2$s">%3$s</a>',
		esc_url( $url ),
		esc_attr( $args['title'] ),
		$dashicon
	);
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
 * @since 3.1.0
 * @since 3.17.4 Unknown.
 * @since 6.0.0 Move HTML into view file: `includes/admin/views/merge-code-editor-button.php`.
 *                Move certificate merge code list to `llms_get_certificate_merge_codes()`.
 *
 * @param string  $target Target to add the merge code to. Accepts the ID of a tinymce editor or a DOM ID (#element-id).
 * @param boolean $echo   If `true`, echos the HTML output.
 * @param array   $codes  Optional array of custom codes to pass in, otherwise the codes are determined
 *                        what is available for the post type.
 * @return string Returns the HTML for the merge code button.
 */
function llms_merge_code_button( $target = 'content', $echo = true, $codes = array() ) {

	$screen = get_current_screen();

	if ( ! $codes && $screen && isset( $screen->post_type ) ) {

		switch ( $screen->post_type ) {

			case 'llms_certificate':
				$codes = llms_get_certificate_merge_codes();
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

		}
	}

	/**
	 * Filters the list of available merge codes in the specified context.
	 *
	 * @since Unknown
	 *
	 * @param array[]        $codes  Associative array of merge codes where the array key is the merge code and the array value is a name / description of the merge code.
	 * @param WP_Screen|null $screen The screen object from `get_current_screen().
	 * @param string         $target Target to add the merge code to. Accepts the ID of a tinymce editor or a DOM ID (#element-id).
	 */
	$codes = apply_filters( 'llms_merge_codes_for_button', $codes, $screen, $target );

	$html = '';
	if ( $codes ) {
		ob_start();
		include LLMS_PLUGIN_DIR . 'includes/admin/views/merge-code-button.php';
		$html = ob_get_clean();
	}

	if ( $echo ) {
		// PHPCS ignore reason: Escaped in the view file.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	return $html;
}

/**
 * Retrieve the precision for round function for floating values.
 *
 * @since 7.1.3
 *
 * @return int
 */
function llms_get_floats_rounding_precision() {

	// Used `static` to store precision value so `apply_filters()` run only once per request.
	static $precision = null;

	if ( is_null( $precision ) ) {
		/**
		 * Filters the precision for round function for floating values.
		 *
		 * @since 7.1.3
		 *
		 * @param int $precision Precision for round function for floating values.
		 */
		$precision = apply_filters( 'lifterlms_floats_rounding_precision', 2 );
	}

	return $precision;
}
