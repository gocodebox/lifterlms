<?php
/**
 * Single Certificate Preview Template
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 3.14.0
 * @since [version] Link the certificate title instead of the whole card.
 * @version [version]
 *
 * @param LLMS_User_Certificate $certificate Certificate object being displayed.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="llms-certificate" data-id="<?php echo esc_attr( $certificate->get( 'id' ) ); ?>" id="<?php printf( 'llms-certificate-%d', esc_attr( $certificate->get( 'id' ) ) ); ?>">

	<?php do_action( 'lifterlms_before_certificate_preview', $certificate ); ?>

	<?php
	printf(
		'<%1$s class="llms-certificate-title"><a class="llms-certificate-link" href="%2$s">%3$s</a></%1$s>',
		tag_escape( llms_get_content_heading_tag( 'certificate' ) ),
		esc_url( get_permalink( $certificate->get( 'id' ) ) ),
		esc_html( $certificate->get( 'title' ) )
	);
	?>
	<div class="llms-certificate-date"><?php echo esc_html( $certificate->get_earned_date() ); ?></div>

	<?php do_action( 'lifterlms_after_certificate_preview', $certificate ); ?>

</div>

