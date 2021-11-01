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
 * Retrieve the content of a certificate
 *
 * @since 2.2.0
 * @since 3.18.0 Unknown.
 *
 * @param integer $id WP Post ID of the cert (optional if used within a loop).
 * @return string
 */
function llms_get_certificate_content( $id = 0 ) {

	$id = ( $id ) ? $id : get_the_ID();

	$cert = LLMS()->certificates();

	if ( 'llms_certificate' == get_post_type( $id ) ) {

		$cert->certs['LLMS_Certificate_User']->init( $id, get_current_user_id(), $id );
		$certificate_content = $cert->certs['LLMS_Certificate_User']->get_content_html();

	} else {

		$certificate_content = get_the_content();

	}

	$content = apply_filters( 'the_content', $certificate_content );

	return apply_filters( 'lifterlms_certificate_content', $content, $id );

}


/**
 * Retrieve an array of image data for a certificate background image
 *
 * If no image found, will default to the LifterLMS placeholder (which can be filtered for a custom placeholder).
 *
 * @since 2.2.0
 *
 * @param int $id Optional. WP Certificate Post ID. Default is 0.
 *                When not provide the current post id will be used.
 * @return array Associative array of certificate image details
 */
function llms_get_certificate_image( $id = 0 ) {

	$id = ( $id ) ? $id : get_the_ID();

	$img_id = get_post_meta( $id, '_llms_certificate_image', true );

	// Don't retrieve a size if legacy mode is enabled.
	$size = ( 'yes' === get_option( 'lifterlms_certificate_legacy_image_size', 'yes' ) ) ? '' : 'lifterlms_certificate_background';

	$src = wp_get_attachment_image_src( $img_id, $size );

	if ( ! $src ) {

		$height = apply_filters( 'lifterlms_certificate_background_image_placeholder_height', 616, $id );
		$width  = apply_filters( 'lifterlms_certificate_background_image_placeholder_width', 800, $id );
		$src    = apply_filters( 'lifterlms_certificate_background_image_placeholder_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png', $id );

	} else {

		$height = apply_filters( 'lifterlms_certificate_background_image_height', $src[2], $id );
		$width  = apply_filters( 'lifterlms_certificate_background_image_width', $src[1], $id );
		$src    = apply_filters( 'lifterlms_certificate_background_image_src', $src[0], $id );

	}

	return array(
		'height' => $height,
		'src'    => $src,
		'width'  => $width,
	);

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
		$id = absint( $starting_id );

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
 * Retrieve the title of a certificate
 *
 * @since 2.2.0
 *
 * @param int $id Optional. WP Certificate Post ID. Default is 0.
 *                When not provide the current post id will be used.
 * @return string The title of the certificate.
 */
function llms_get_certificate_title( $id = 0 ) {

	$id = ( $id ) ? $id : get_the_ID();

	return apply_filters( 'lifterlms_certificate_title', get_post_meta( $id, '_llms_certificate_title', true ), $id );

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
