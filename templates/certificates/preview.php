<?php
/**
 * Single Certificate Preview Template
 *
 * @since    3.14.0
 * @version  3.14.0
 */

defined( 'ABSPATH' ) || exit;

?>

<a class="llms-certificate" data-id="<?php echo $certificate->get( 'id' ); ?>" href="<?php echo esc_url( get_permalink( $certificate->get( 'id' ) ) ); ?>" id="<?php printf( 'llms-certificate-%d', $certificate->get( 'id' ) ); ?>">

	<?php do_action( 'lifterlms_before_certificate_preview', $certificate ); ?>

	<h4 class="llms-certificate-title"><?php echo $certificate->get( 'certificate_title' ); ?></h4>
	<div class="llms-certificate-date"><?php echo $certificate->get_earned_date(); ?></div>

	<?php do_action( 'lifterlms_after_certificate_preview', $certificate ); ?>

</a>

