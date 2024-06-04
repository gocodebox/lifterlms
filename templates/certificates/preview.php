<?php
/**
 * Single Certificate Preview Template
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 3.14.0
 * @version 6.0.0
 *
 * @param LLMS_User_Certificate $certificate Certificate object being displayed.
 */

defined( 'ABSPATH' ) || exit;
?>
<a class="llms-certificate" data-id="<?php echo esc_attr( $certificate->get( 'id' ) ); ?>" href="<?php echo esc_url( get_permalink( $certificate->get( 'id' ) ) ); ?>" id="<?php printf( 'llms-certificate-%d', esc_attr( $certificate->get( 'id' ) ) ); ?>">

	<?php do_action( 'lifterlms_before_certificate_preview', $certificate ); ?>

	<h4 class="llms-certificate-title"><?php echo esc_html( $certificate->get( 'title' ) ); ?></h4>
	<div class="llms-certificate-date"><?php echo esc_html( $certificate->get_earned_date() ); ?></div>

	<?php do_action( 'lifterlms_after_certificate_preview', $certificate ); ?>

</a>

