<?php
/**
 * LifterLMS Certificate Functions
 *
 * @package LifterLMS/Functions
 *
 * @since 2.2.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the LLMS_User_Certificate instance for a given post.
 *
 * Expects the input post to be either an `llms_my_certificate` post. An `llms_certificate` post can be used
 * when `$preview_template` is `true`.
 *
 * @since [version]
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
 * @since [version] Use `llms_get_certificate()` and `LLMS_User_Certificate` methods.
 *                If this function is used out of the intended certificate context this will now
 *                return an empty string, whereas previously it returned the content of the post.
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

		// Get merged content for templates or the already-merged content of the earned cert, retrieve the raw because we filter it again below.
		$content = 'llms_certificate' === get_post_type( $id ) ? $certificate->merge_content() : $certificate->get( 'content', true );

	}

	/** WordPress core filter documented at {@link https://developer.wordpress.org/reference/hooks/the_content/}. */
	$content = apply_filters( 'the_content', $content );

	/**
	 * Filter the `post_content` of a certificate or certificate template.
	 *
	 * @since Unknown
	 * @since [version] Added the `$certificate` parameter.
	 *
	 * @param string                     $content     The certificate content.
	 * @param int                        $id          The ID of the certificate.
	 * @param bool|LLMS_User_Certificate $certificate Certificate object or `false` if the post couldn't be found.
	 */
	return apply_filters( 'lifterlms_certificate_content', $content, $id, $certificate );

}

/**
 * Retrieve an array of image data for a certificate background image
 *
 * If no image found, will default to the LifterLMS placeholder (which can be filtered for a custom placeholder).
 *
 * @since 2.2.0
 * @since [version] Use `LLMS_User_Certificate::get_background_image()`.
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
 * @since [version]
 *
 * @return array[] Associative array of merge codes where the array key is the merge code and the array value is a name / description of the merge code.
 */
function llms_get_certificate_merge_codes() {

	return array(
		'{site_title}'     => __( 'Site Title', 'lifterlms' ),
		'{site_url}'       => __( 'Site URL', 'lifterlms' ),
		'{current_date}'   => __( 'Earned Date', 'lifterlms' ),
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
 * @since [version]
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
	 * @since [version]
	 *
	 * @param array $orientations Array of orientations.
	 */
	return apply_filters( 'llms_certificate_orientations', $orientations );

}

/**
 * Retrieve the current or next sequential ID for a given certificate template.
 *
 * If there's no existing ID, the ID starts at 1 and will *not* be incremented.
 *
 * When the ID is incremented the new value is automatically persisted to the database.
 *
 * @since [version]
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
		 * @since [version]
		 *
		 * @param int $starting_id The starting number.
		 * @param int $template_id WP_Post ID of the certificate template.
		 */
		$starting_id = apply_filters( 'llms_certificate_sequential_id_starting_number', 1, $template_id );
		$id          = absint( $starting_id );

		// Don't increment the starting ID!
		$increment = false;
		$update    = true;

	}

	if ( $increment ) {
		++$id;
	}

	/**
	 * Filters the sequential ID number for a given certificate template.
	 *
	 * The returned number *must* be an absolute integer (zero included). The returned value will be
	 * passed through `absint()` to sanitize the filtered value.
	 *
	 * @since [version]
	 *
	 * @param int $id          The sequential ID.
	 * @param int $template_id WP_Post ID of the certificate template.
	 */
	$id = absint( apply_filters( 'llms_certificate_sequential_id', $id, $template_id ) );

	if ( $update ) {
		update_post_meta( $template_id, $key, $id );
	}

	return $id;

}

/**
 * Retrieves a list of registered certificate sizes.
 *
 * @since [version]
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
		'A3'     => array(
			'name'   => _x( 'A3', 'Paper size name', 'lifterlms' ),
			'width'  => 297,
			'height' => 420,
			'unit'   => 'mm',
		),
		'A4'     => array(
			'name'   => _x( 'A4', 'Paper size name', 'lifterlms' ),
			'width'  => 210,
			'height' => 297,
			'unit'   => 'mm',
		),
		'A5'     => array(
			'name'   => _x( 'A5', 'Paper size name', 'lifterlms' ),
			'width'  => 148,
			'height' => 210,
			'unit'   => 'mm',
		),
		// North American sizes.
		'LETTER' => array(
			'name'   => _x( 'Letter', 'Paper size name', 'lifterlms' ),
			'width'  => 8.5,
			'height' => 11,
			'unit'   => 'in',
		),
		'LEGAL'  => array(
			'name'   => _x( 'Legal', 'Paper size name', 'lifterlms' ),
			'width'  => 8.5,
			'height' => 14,
			'unit'   => 'in',
		),
		'LEDGER' => array(
			'name'   => _x( 'Ledger', 'Paper size name', 'lifterlms' ),
			'width'  => 11,
			'height' => 17,
			'unit'   => 'in',
		),
	);

	/**
	 * Filters registered certificate size options.
	 *
	 * @since [version]
	 *
	 * @param array $sizes Array of registered sizes.
	 */
	return apply_filters( 'llms_certificate_sizes', $sizes );

}

/**
 * Retrieves units available for certificate dimensions.
 *
 * @since [version]
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
	 * @since [version]
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
 * @since [version] Use `LLMS_User_Certificate()` to retrieve the title for earned certificates.
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
	 * @since [version] Added the `$certificate` parameter.
	 *
	 * @param string $title The certificate title.
	 * @param int    $id    The ID of the certificate.
	 */
	return apply_filters( 'lifterlms_certificate_title', $title, $id );

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
