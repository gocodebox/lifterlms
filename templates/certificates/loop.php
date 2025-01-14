<?php
/**
 * Certificates Loop
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 3.14.0
 * @since 6.0.0 Add pagination.
 * @version 3.14.0
 *
 * @param LLMS_User_Certificate[] $certificates Array of certificates to display.
 * @param int                     $cols         Number of columns.
 * @param false|array             $pagination   Pagination arguments to pass to {@see llms_paginate_links()} or `false`
 *                                              when pagination is disabled.
 */

defined( 'ABSPATH' ) || exit;
?>

<?php
	/**
	 * Action run prior to the certificate loop template.
	 *
	 * @since 3.14.0
	 */
	do_action( 'llms_before_certificate_loop' );
?>

<?php if ( $certificates ) : ?>

	<ul class="llms-certificates-loop listing-certificates <?php printf( 'loop-cols-%d', esc_attr( $cols ) ); ?>">

		<?php foreach ( $certificates as $certificate ) : ?>

			<li class="llms-certificate-loop-item certificate-item">
				<?php
					/**
					 * Action run to display the preview for a single certificate.
					 *
					 * @since 3.14.0
					 *
					 * @param LLMS_User_Certificate $certificate Certificate object being displayed.
					 */
					do_action( 'llms_certificate_preview', $certificate );
				?>
			</li>

		<?php endforeach; ?>

	</ul>

<?php else : ?>

	<p>
	<?php
		/**
		 * Filters the message displayed when the student hasn't earned any certificates.
		 *
		 * @since 3.14.0
		 *
		 * @param string $message The message text.
		 */
		echo wp_kses_post( apply_filters( 'lifterlms_no_certificates_text', esc_html__( 'You do not have any certificates yet.', 'lifterlms' ) ) );
	?>
	</p>

<?php endif; ?>

<?php if ( $pagination ) : ?>
	<?php
		// HTML output is escaped in the function.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo llms_paginate_links( $pagination );
	?>
<?php endif; ?>

<?php
	/**
	 * Action run after to the certificate loop template.
	 *
	 * @since 3.14.0
	 */
	do_action( 'llms_after_certificate_loop' );
?>
