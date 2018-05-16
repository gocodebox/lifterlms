<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Certificate Functions
 * @since    2.2.0
 * @version  2.2.0
 */

/**
 * Retrieve the content of a certificate
 * @param  integer $id WP Post ID of the cert (optional if used within a loop)
 * @return string
 * @since    2.2.0
 * @version  3.18.0
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
 * Retrive an array of image data for a certificate background image
 *
 * If no image found, will default to the LifterLMS placeholder (which can be filtered for a custom placeholder)
 *
 * @param  integer $id  WP Certificate Post ID
 * @return array        associative array of certificate image details
 * @since    2.2.0
 * @version  2.2.0
 */
function llms_get_certificate_image( $id = 0 ) {

	$id = ( $id ) ? $id : get_the_ID();

	$img_id = get_post_meta( $id, '_llms_certificate_image', true );

	// don't retrieve a size if legacy mode is enabled
	$size = ( 'yes' === get_option( 'lifterlms_certificate_legacy_image_size', 'yes' ) ) ? '' : 'lifterlms_certificate_background';

	$src = wp_get_attachment_image_src( $img_id, $size );

	if ( ! $src ) {

		$height = apply_filters( 'lifterlms_certificate_background_image_placeholder_height', 616, $id );
		$width = apply_filters( 'lifterlms_certificate_background_image_placeholder_width', 800, $id );
		$src = apply_filters( 'lifterlms_certificate_background_image_placeholder_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png', $id );

	} else {

		$height = apply_filters( 'lifterlms_certificate_background_image_height', $src[2], $id );
		$width = apply_filters( 'lifterlms_certificate_background_image_width', $src[1], $id );
		$src = apply_filters( 'lifterlms_certificate_background_image_src', $src[0], $id );

	}

	return array(
		'height' => $height,
		'src' => $src,
		'width' => $width,
	);

}


/**
 * Retrive the title of a certificate
 * @param  int    $id WP post id of the cert (optional if used within a loop)
 * @return string     title of the cert
 * @since    2.2.0
 * @version  2.2.0
 */
function llms_get_certificate_title( $id = 0 ) {

	$id = ( $id ) ? $id : get_the_ID();

	return apply_filters( 'lifterlms_certificate_title', get_post_meta( $id, '_llms_certificate_title', true ), $id );

}


/**
 * Register the custom "print_certificate" image size
 * @return void
 * @since    2.2.0
 * @version  2.2.0
 */
function llms_register_certificate_image_size() {

	$width  = get_option( 'lifterlms_certificate_bg_img_width', '800' );
	$height = get_option( 'lifterlms_certificate_bg_img_height', '616' );

	add_image_size( 'lifterlms_certificate_background', $width, $height, true );

}
add_action( 'after_setup_theme', 'llms_register_certificate_image_size' );
