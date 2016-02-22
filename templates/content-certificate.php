<?php
/**
 * The Template for displaying Certificates.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post;
if ( is_user_admin() ) {
	show_admin_bar(false);
}

$cert = new LLMS_Certificates();
$cert->certs['LLMS_Certificate_User']->init($post->ID, get_current_user_id(), $post->ID);
$certificate_content = $cert->certs['LLMS_Certificate_User']->get_content_html();

add_filter('lifterlms_certificate_title', 'llms_filter_certificate_title', 1, 1);
add_filter('lifterlms_certificate_image', 'llms_filter_certificate_image', 1, 1);

function llms_filter_certificate_title($id) {
	$postmeta = get_post_meta($id);

	return $postmeta['_llms_certificate_title'][0];
}

function llms_filter_certificate_image($id) {
	$postmeta = get_post_meta($id);

	$certimage_id = $postmeta['_llms_certificate_image'][0]; // Get Image Meta
	$certimage = wp_get_attachment_image_src($certimage_id, 'print_certificate'); //Get Right Size Image for Print Template

	if ($certimage == '') {
		return apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_certificate.png' );
	}
	else {
		return $certimage[0];
	}
}

function llms_get_image_size( $name ) {
	global $_wp_additional_image_sizes;

	if ( isset( $_wp_additional_image_sizes[$name] ) )
		return $_wp_additional_image_sizes[$name];

	return array();
}

$image_size = llms_get_image_size('print_certificate');

if (empty($image_size)) {
	$image_size['width'] = 800;
	$image_size['height'] = 616;
}

?>
<!DOCTYPE html>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width">

	<title><?php echo get_the_title(); ?></title>

</head>
<body>
	<main role="main">
		<div class="llms-certificate-container" style="width:<?php echo $image_size['width']; ?>px; height:<?php echo $image_size['height']; ?>px;">
			<img src="<?php echo apply_filters( 'lifterlms_certificate_image', $post->ID); ?>" alt="Cetrificate Background" class="certificate-background">
			<div id="certificate-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="llms-summary">
					<?php llms_print_notices(); ?>

					<?php do_action('before_lifterlms_certificate_main_content'); ?>

					<h1><?php echo apply_filters( 'lifterlms_certificate_title', $post->ID); ?></h1>
					<p><?php echo $certificate_content; ?></p>

					<?php do_action('after_lifterlms_certificate_main_content'); ?>

				</div>
			</div>
		</div>
		<div id="llms-print-certificate" class="no-print">
			<input type="button" class="llms-button" onClick="window.print()" value="<?php echo _e('Print Certificate', 'lifterlms') ?>"/>
		</div>
</main>

</body>
