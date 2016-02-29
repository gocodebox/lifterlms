<?php
/**
 * Certificate related functions
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve the content of a certificate
 * @param  integer $id WP Post ID of the cert (optional if used within a loop)
 * @return string
 */
function llms_get_certificate_content( $id = 0 )
{

	$id = ( $id ) ? $id : get_the_ID();

	$cert = new LLMS_Certificates();

	if( 'llms_certificate' == get_post_type( $id ) ) {

		$cert->certs['LLMS_Certificate_User']->init( $id, get_current_user_id(), $id ) ;
		$certificate_content = $cert->certs['LLMS_Certificate_User']->get_content_html();

	} else {

		$certificate_content = get_the_content();

	}

	$content = apply_filters('the_content', $certificate_content);

	return apply_filters( 'lifterlms_certificate_content', $content );

}


/**
 * Retrive an array of image data for a certificate background image
 *
 * If no image found, will default to the LifterLMS placeholder (which can be filtered for a custom placeholder)
 *
 * @param  integer $id  WP Certificate Post ID
 * @return array        associative array of certificate image details
 */
function llms_get_certificate_image( $id = 0 )
{

	$id = ( $id ) ? $id : get_the_ID();

	$img_id = get_post_meta( $id, '_llms_certificate_image', true );

	// don't retrieve a size if legacy mode is enabled
	$size = ( 'yes' === get_option( 'lifterlms_certificate_legacy_image_size', 'yes' ) ) ? '' : 'print_certificate';

	$src = wp_get_attachment_image_src( $img_id, $size );

	if ( ! $src ) {

		$src = apply_filters( 'lifterlms_certificate_background_image_placeholder_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png', $id );

		$width = 800;
		$height = 616;


	} else {

		$width = $src[1];
		$height = $src[2];
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
 */
function llms_get_certificate_title( $id = 0 )
{

	$id = ( $id ) ? $id : get_the_ID();

	return apply_filters( 'lifterlms_certificate_title', get_post_meta( $id, '_llms_certificate_title', true ) );

}


/**
 * Register the custom "print_certificate" image size
 * @return void
 */
function llms_register_certificate_image_size()
{

	$width  = apply_filters( 'lifterlms_print_certificate_width', get_option( 'lifterlms_certificate_bg_img_width', '800' ) );
	$height = apply_filters( 'lifterlms_print_certificate_height', get_option( 'lifterlms_certificate_bg_img_height', '616' ) );
	$crop =   apply_filters( 'lifterlms_print_certificate_crop', ( 'yes' === get_option( 'lifterlms_certificate_bg_img_crop' ) ) ? true : false );

	add_image_size( 'print_certificate', $width, $height, true );

}
add_action( 'init', 'llms_register_certificate_image_size' );


