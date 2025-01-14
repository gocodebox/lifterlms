<?php
/**
 * Single certificate dynamic styles.
 *
 * Output in the header, via the `wp_head` action.
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 6.0.0
 * @version 6.0.0
 *
 * @param LLMS_User_Certificate $certificate      Certificate object.
 * @param string                $width            Width (with unit) accounting for the orientation value.
 * @param string                $height           Height (with unit) accounting for the orientation value.
 * @param string                $background_color Background color value.
 * @param string                $background_img   Image source URL for the background image.
 * @param string                $padding          Internal margin value with units, ready to be used in CSS.
 * @param array[]               $fonts            Array of custom certificate fonts used by the certificate.
 */

defined( 'ABSPATH' ) || exit;

$gfonts_preconnet = false;
?>

<!-- Certificates Dynamic Styles -->
<?php foreach ( $fonts as $font ) : ?>
	<?php if ( ! empty( $font['href'] ) ) : ?>
		<?php
		if ( ! $gfonts_preconnet && false !== strpos( $font['href'], 'fonts.googleapis.com' ) ) :
			$gfonts_preconnet = true;
			?>
			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<?php endif; ?>
	<link id="llms-font--<?php echo esc_attr( $font['id'] ); ?>" href="<?php echo esc_url( $font['href'] ); ?>" rel="stylesheet">
	<?php endif; ?>
<?php endforeach; ?>
<style type="text/css">
	html, body {
		background-color: <?php echo esc_html( $background_color ); ?> !important;
	}
	.llms-certificate-wrapper {
		height: <?php echo esc_html( $height ); ?>;
		width: <?php echo esc_html( $width ); ?>;
	}
	.llms-certificate-container {
		background-image: <?php echo "url( " . esc_url( $background_img ) . " )"; ?> !important;
		padding: <?php echo esc_html( $padding ); ?>;
	}
	<?php foreach ( $fonts as $font ) : ?>
	.has-<?php echo esc_html( $font['id'] ); ?>-font-family {
		font-family: <?php echo esc_html( $font['fontFamily'] ); ?>;
	}
	<?php endforeach; ?>
</style>
<style type="text/css" media="print">
	@page {
		size: <?php echo esc_html( $width ); ?> <?php echo esc_html( $height ); ?>;
		margin: 0;
	}
</style>
<!-- End Certificates Dynamic Styles -->
