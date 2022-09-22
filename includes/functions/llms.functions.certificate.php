<?php
/**
 * LifterLMS Certificate Functions
 *
 * @package LifterLMS/Functions
 *
 * @since 2.2.0
 * @version 6.11.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the LLMS_User_Certificate instance for a given post.
 *
 * Expects the input post to be either an `llms_my_certificate` post. An `llms_certificate` post can be used
 * when `$preview_template` is `true`.
 *
 * @since 6.0.0
 *
 * @param WP_Post|int|null $post             A WP_Post object or a WP_Post ID. A falsy value will use the current global `$post` object (if one exists).
 * @param boolean          $preview_template If `true`, allows loading an `llms_certificate` post type for previewing the template.
 * @return LLMS_User_Certificate|boolean Returns the LLMS_User_Certificate object for the given post. Returns `false` if the post doesn't exist or is
 *                                       not of the expected post type.
 */
function llms_get_certificate( $post = null, $preview_template = false ) {

	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	if ( 'llms_my_certificate' === $post->post_type || ( 'llms_certificate' === $post->post_type && $preview_template ) ) {
		return new LLMS_User_Certificate( $post );
	}

	return false;

}

/**
 * Retrieve the content of a certificate.
 *
 * This allows utilizing the `LLMS_User_Certificate` class with an `llms_certificate` post type to render a preview
 * of the certificate template. The saved `post_content` will be merged (using the current user's information).
 *
 * This function is intended for use on the certificate's front-end display template. In order to retrieve the
 * raw content use `LLMS_User_Certificate->get( 'content' )` or `WP_Post->post_content`.
 *
 * @since 2.2.0
 * @since 3.18.0 Unknown.
 * @since 6.0.0 Use `llms_get_certificate()` and `LLMS_User_Certificate` methods.
 *                If this function is used out of the intended certificate context this will now
 *                return an empty string, whereas previously it returned the content of the post.
 * @since 6.4.0 Fixed issue with merge codes in reusable blocks by merging *after* filtering the post content.
 *
 * @param integer $id WP Post ID of the cert (optional if used within a loop).
 * @return string
 */
function llms_get_certificate_content( $id = 0 ) {

	$content = '';

	$certificate = llms_get_certificate( $id, true );
	if ( $certificate ) {

		// If `$id` was empty to use the global, ensure an id is available in filter on the return.
		$id = $certificate->get( 'id' );

		// Get raw content because we filter it again below.
		$content = $certificate->get( 'content', true );
	}

	/** WordPress core filter documented at {@link https://developer.wordpress.org/reference/hooks/the_content/}. */
	$content = apply_filters( 'the_content', $content );

	// Get merged content for templates.
	if ( 'llms_certificate' === get_post_type( $id ) ) {
		$content = $certificate->merge_content( $content );
	}

	/**
	 * Filter the `post_content` of a certificate or certificate template.
	 *
	 * @since Unknown
	 * @since 6.0.0 Added the `$certificate` parameter.
	 *
	 * @param string                     $content     The certificate content.
	 * @param int                        $id          The ID of the certificate.
	 * @param bool|LLMS_User_Certificate $certificate Certificate object or `false` if the post couldn't be found.
	 */
	return apply_filters( 'lifterlms_certificate_content', $content, $id, $certificate );

}

/**
 * Retrieves a list of fonts available for use in certificates.
 *
 * @since 6.0.0
 * @since 6.11.0 Added internal call for certificate fonts, with external option enabled.
 *
 * @return array[] {
 *     Array of font definition arrays. The array key is the font's unique id.
 *
 *     @type string      $name The human-readable name of the font.
 *     @type string|null $href The href used to load the font or `null` for system or default fonts.
 *     @type string|null $css  The CSS `font-family` rule value.
 * }
 */
function llms_get_certificate_fonts() {
	/**
	 * Determines whether or not webfonts are loaded from Google CDNs.
	 *
	 * @since 6.11.0
	 *
	 * @param bool $use_g_fonts If `true`, fonts are loaded from Google, otherwise they are loaded from the local site.
	 */
	$use_g_fonts = apply_filters( 'llms_use_google_webfonts', false );
	$serif       = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';

	$fonts = array(

		// Default fonts.
		'sans'                => array(
			'name'       => __( 'Sans-serif', 'lifterlms' ),
			'href'       => null,
			// From https://systemfontstack.com.
			'fontFamily' => '-apple-system, BlinkMacSystemFont, "avenir next", avenir, "segoe ui", "helvetica neue", helvetica, Ubuntu, roboto, noto, arial, sans-serif',
		),
		'serif'               => array(
			'name'       => __( 'Serif', 'lifterlms' ),
			'href'       => null,
			// From https://systemfontstack.com.
			'fontFamily' => $serif,
		),

		// Newspaper-style display fonts.
		'pirata-one'          => array(
			'name'       => 'Pirata One',
			'href'       => $use_g_fonts ? 'https://fonts.googleapis.com/css2?family=Pirata+One&display=swap' : LLMS_PLUGIN_URL . 'assets/css/pirata-one.css?ver=v22',
			'fontFamily' => '"Pirata One", ' . $serif,
		),
		'unifraktur-maguntia' => array(
			'name'       => 'UnifrakturMaguntia',
			'href'       => $use_g_fonts ? 'https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap' : LLMS_PLUGIN_URL . 'assets/css/unifraktur-maguntia.css?ver=v16',
			'fontFamily' => '"UnifrakturMaguntia", ' . $serif,
		),

		// Cursive-style handwriting fonts.
		'dancing-script'      => array(
			'name'       => 'Dancing Script',
			'href'       => $use_g_fonts ? 'https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap' : LLMS_PLUGIN_URL . 'assets/css/dancing-script.css?ver=v24',
			'fontFamily' => '"Dancing Script", ' . $serif,
		),
		'imperial-script'     => array(
			'name'       => 'Imperial Script',
			'href'       => $use_g_fonts ? 'https://fonts.googleapis.com/css2?family=Imperial+Script&display=swap' : LLMS_PLUGIN_URL . 'assets/css/imperial-script.css?ver=v24',
			'fontFamily' => '"Imperial Script", ' . $serif,
		),

	);

	/**
	 * Filters the list of fonts available to certificates.
	 *
	 * @since 6.0.0
	 *
	 * @param array[] $fonts Array of font definitions, {@see llms_get_certificate_fonts()}.
	 */

	return apply_filters( 'llms_certificate_fonts', $fonts );

}

/**
 * Retrieve an array of image data for a certificate background image
 *
 * If no image found, will default to the LifterLMS placeholder (which can be filtered for a custom placeholder).
 *
 * @since 2.2.0
 * @since 6.0.0 Use `LLMS_User_Certificate::get_background_image()`.
 *
 * @param int $id Optional. WP Certificate Post ID. Default is 0.
 *                When not provide the current post id will be used.
 * @return array Associative array of certificate image details
 */
function llms_get_certificate_image( $id = 0 ) {

	$id   = ( $id ) ? $id : get_the_ID();
	$cert = new LLMS_User_Certificate( $id );
	return $cert->get_background_image();

}

/**
 * Retrieve a list of merge codes that can be used in certificate templates.
 *
 * @since 6.0.0
 * @since 6.1.0 Changed `{current_date}` label from 'Earned Date' to 'Current Date' and added `{earned_date}` merge code.
 *
 * @return string[] Associative array of merge codes where the array key is the merge code and the array value is a name / description of the merge code.
 */
function llms_get_certificate_merge_codes() {

	return array(
		'{site_title}'     => __( 'Site Title', 'lifterlms' ),
		'{site_url}'       => __( 'Site URL', 'lifterlms' ),
		'{current_date}'   => __( 'Current Date', 'lifterlms' ),
		'{earned_date}'    => __( 'Earned Date', 'lifterlms' ),
		'{first_name}'     => __( 'Student First Name', 'lifterlms' ),
		'{last_name}'      => __( 'Student Last Name', 'lifterlms' ),
		'{email_address}'  => __( 'Student Email', 'lifterlms' ),
		'{student_id}'     => __( 'Student User ID', 'lifterlms' ),
		'{user_login}'     => __( 'Student Username', 'lifterlms' ),
		'{certificate_id}' => __( 'Certificate ID', 'lifterlms' ),
		'{sequential_id}'  => __( 'Sequential Certificate ID', 'lifterlms' ),
	);

}

/**
 * Retrieves registered certificate orientations.
 *
 * @since 6.0.0
 *
 * @return array Key value array where the array key is the orientation ID and the value is the
 *               translated name of the orientation.
 */
function llms_get_certificate_orientations() {

	$orientations = array(
		'portrait'  => __( 'Portrait', 'lifterlms' ),
		'landscape' => __( 'Landscape', 'lifterlms' ),
	);

	/**
	 * Filters the list of available certificate orientations.
	 *
	 * @since 6.0.0
	 *
	 * @param array $orientations Array of orientations.
	 */
	return apply_filters( 'llms_certificate_orientations', $orientations );

}

/**
 * Retrieve the next sequential ID for a given certificate template and optionally increment it.
 *
 * If there's no existing ID, a default ID of 1 will be used. This can be customized using the filter `llms_certificate_sequential_id_starting_number`.
 *
 * When an increment is requested, the new incremented ID will be automatically persisted to the database.
 *
 * @since 6.0.0
 *
 * @param integer $template_id WP_Post ID of the certificate template (`llms_certificate`) post.
 * @param boolean $increment   Whether or not to increment the current ID.
 * @return int
 */
function llms_get_certificate_sequential_id( $template_id, $increment = false ) {

	$key    = '_llms_sequential_id';
	$update = $increment;
	$id     = absint( get_post_meta( $template_id, $key, true ) );

	// No id, get the initial ID.
	if ( ! $id ) {

		/**
		 * Determines the default starting number for the a certificate's sequential ID.
		 *
		 * The returned number *must* be an absolute integer (zero included). The returned value will be
		 * passed through `absint()` to sanitize the filtered value.
		 *
		 * @since 6.0.0
		 *
		 * @param int $starting_id The starting number.
		 * @param int $template_id WP_Post ID of the certificate template.
		 */
		$starting_id = apply_filters( 'llms_certificate_sequential_id_starting_number', 1, $template_id );
		$id          = absint( $starting_id );
		$update      = true;

	}

	if ( $update ) {
		update_post_meta( $template_id, $key, $increment ? $id + 1 : $id );
	}

	/**
	 * Filters the sequential ID number for a given certificate template.
	 *
	 * The returned number *must* be an absolute integer. The returned value will be
	 * passed through `absint()` to sanitize the filtered value.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id          The sequential ID.
	 * @param int $template_id WP_Post ID of the certificate template.
	 */
	return absint( apply_filters( 'llms_certificate_sequential_id', $id, $template_id ) );

}

/**
 * Retrieves a list of registered certificate sizes.
 *
 * @since 6.0.0
 *
 * @return {
 *     Array of sizes. The array key is the size's unique ID.
 *
 *     @type string $name   The translated name for the size.
 *     @type float  $width  The portrait width dimension of the size.
 *     @type float  $height The portrait height dimension of the size.
 *     @type string $unit   The unit used for the dimensions of the size. Must be the ID of a unit registered via {@see llms_get_certificate_units()}.
 * }
 */
function llms_get_certificate_sizes() {

	$sizes = array(
		// ISO 216 sizes.
		'A3'           => array(
			'name'   => _x( 'A3', 'Paper size name', 'lifterlms' ),
			'width'  => 297,
			'height' => 420,
			'unit'   => 'mm',
		),
		'A4'           => array(
			'name'   => _x( 'A4', 'Paper size name', 'lifterlms' ),
			'width'  => 210,
			'height' => 297,
			'unit'   => 'mm',
		),
		'A5'           => array(
			'name'   => _x( 'A5', 'Paper size name', 'lifterlms' ),
			'width'  => 148,
			'height' => 210,
			'unit'   => 'mm',
		),
		// North American sizes.
		'LETTER'       => array(
			'name'   => _x( 'Letter', 'Paper size name', 'lifterlms' ),
			'width'  => 8.5,
			'height' => 11,
			'unit'   => 'in',
		),
		'LEGAL'        => array(
			'name'   => _x( 'Legal', 'Paper size name', 'lifterlms' ),
			'width'  => 8.5,
			'height' => 14,
			'unit'   => 'in',
		),
		'LEDGER'       => array(
			'name'   => _x( 'Ledger', 'Paper size name', 'lifterlms' ),
			'width'  => 11,
			'height' => 17,
			'unit'   => 'in',
		),
		'USER_DEFINED' => array(
			'name'   => __( 'User defined', 'lifterlms' ),
			'width'  => get_option( 'lifterlms_certificate_default_user_defined_width', 400 ),
			'height' => get_option( 'lifterlms_certificate_default_user_defined_height', 400 ),
			'unit'   => get_option( 'lifterlms_certificate_default_user_defined_unit', 'mm' ),
		),
	);

	/**
	 * Filters registered certificate size options.
	 *
	 * @since 6.0.0
	 *
	 * @param array $sizes Array of registered sizes.
	 */
	return apply_filters( 'llms_certificate_sizes', $sizes );

}

/**
 * Retrieves units available for certificate dimensions.
 *
 * @since 6.0.0
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/CSS/length
 *
 * @return {
 *     Array of unit information. The array key is the unit ID, which should be a valid absolute length CSS unit.
 *
 *     @type string $name   Translated name of the unit.
 *     @type string $symbol Translated symbol used when displaying dimensions with the unit.
.* }
 */
function llms_get_certificate_units() {

	$units = array(
		'in' => array(
			'name'   => __( 'Inches', 'lifterlms' ),
			'symbol' => _x( '"', 'Symbol for inches', 'lifterlms' ),
		),
		'mm' => array(
			'name'   => __( 'Millimeters', 'lifterlms' ),
			'symbol' => _x( 'mm', 'Symbol for millimeters', 'lifterlms' ),
		),
	);

	/**
	 * Filters the list of certificate dimension units.
	 *
	 * @since 6.0.0
	 *
	 * @param array $units Array of available units.
	 */
	return apply_filters( 'llms_certificate_units', $units );

}

/**
 * Retrieve the title of a certificate
 *
 * This function is intended for use on the certificate's front-end display template.
 *
 * @since 2.2.0
 * @since 6.0.0 Use `LLMS_User_Certificate()` to retrieve the title for earned certificates.
 *
 * @param int $id WP Certificate Post ID. When not provide the current post id will be used.
 * @return string The title of the certificate.
 */
function llms_get_certificate_title( $id = 0 ) {

	$id          = $id ? $id : get_the_ID();
	$title       = '';
	$certificate = llms_get_certificate( $id, false );
	if ( $certificate ) {
		$title = $certificate->get( 'title' );
	} elseif ( 'llms_certificate' === get_post_type( $id ) ) {
		$title = get_post_meta( $id, '_llms_certificate_title', true );
	}

	/**
	 * Filter the title of a certificate or certificate template.
	 *
	 * @since Unknown
	 * @since 6.0.0 Added the `$certificate` parameter.
	 *
	 * @param string $title The certificate title.
	 * @param int    $id    The ID of the certificate.
	 */
	return apply_filters( 'lifterlms_certificate_title', $title, $id );

}

/**
 * Determines whether or not the block editor can be used to build certificates.
 *
 * The JS used for certificates in the block editor relies on WP functions and APIs available
 * since WordPress 5.8. Earlier versions of WordPress won't work.
 *
 * @since 6.0.0
 *
 * @return boolean
 */
function llms_is_block_editor_supported_for_certificates() {

	global $wp_version;
	$is_supported = version_compare( $wp_version, '5.8-src', '>=' );

	/**
	 * Filters whether or not the block editor can be used for building certificates.
	 *
	 * By default, `$is_supported` will be `true` for WordPress 5.8 or later and false for versions less than
	 * 5.8.
	 *
	 * This filter may be used to disable the block editor on later versions.
	 *
	 * @since 6.0.0
	 *
	 * @param boolean $is_supported Whether or not the block editor is supported.
	 */
	return apply_filters( 'llms_block_editor_supported_for_certificates', $is_supported );

}

/**
 * Register the custom "print_certificate" image size
 *
 * @since 2.2.0
 *
 * @return void
 */
function llms_register_certificate_image_size() {

	$width  = get_option( 'lifterlms_certificate_bg_img_width', '800' );
	$height = get_option( 'lifterlms_certificate_bg_img_height', '616' );

	add_image_size( 'lifterlms_certificate_background', $width, $height, true );

}
add_action( 'after_setup_theme', 'llms_register_certificate_image_size' );
