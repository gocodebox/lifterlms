<?php
/**
 * Certificates Loop
 * @since    3.14.0
 * @version  3.14.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

<?php do_action( 'llms_before_certificate_loop' ); ?>

	<?php if ( $certificates ) : ?>

		<ul class="llms-certificates-loop listing-certificates <?php printf( 'loop-cols-%d', $cols ); ?>">

			<?php foreach ( $certificates as $certificate ) : ?>

				<li class="llms-certificate-loop-item certificate-item">
					<?php do_action( 'llms_certificate_preview', $certificate ); ?>
				</li>

			<?php endforeach; ?>

		</ul>

	<?php else : ?>

		<p><?php echo apply_filters( 'lifterlms_no_certificates_text', __( 'You do not have any certificates yet.', 'lifterlms' ) ); ?></p>

	<?php endif; ?>

<?php do_action( 'llms_after_certificate_loop' ); ?>
