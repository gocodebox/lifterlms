<?php
/**
 * Single certificate actions.
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 6.0.0
 * @version 6.0.0
 *
 * @param LLMS_User_Certificate $certificate       Certificate object.
 * @param string                $back_link         URL for the back link.
 * @param string                $back_text         Text for the back link anchor.
 * @param boolean               $is_shaing_enabled Whether or not sharing is enabled for the certificate.
 * @param boolean               $is_template       Whether or not a certificate template is being displayed.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="llms-print-certificate no-print" id="llms-print-certificate">

	<?php if ( ! $is_template ) : ?>
		<a class="llms-cert-return-link" href="<?php echo esc_url( $back_link ); ?>">&larr; <?php echo $back_text; ?></a>
	<?php endif; ?>

	<button class="llms-button-secondary" onClick="window.print()" type="button">
		<?php echo _e( 'Print', 'lifterlms' ); ?>
		<i class="fa fa-print" aria-hidden="true"></i>
	</button>

	<form action="" method="POST">

		<button class="llms-button-secondary" type="submit" name="llms_generate_cert">
			<?php echo _e( 'Save', 'lifterlms' ); ?>
			<i class="fa fa-cloud-download" aria-hidden="true"></i>
		</button>

		<?php if ( ! $is_template ) : ?>
			<button class="llms-button-secondary" type="submit" name="llms_enable_cert_sharing" value="<?php echo ! $is_sharing_enabled; ?>">
			<?php echo ( $is_sharing_enabled ? _e( 'Disable sharing', 'lifterlms' ) : _e( 'Enable sharing', 'lifterlms' ) ); ?>
				<i class="fa fa-share-alt" aria-hidden="true"></i>
			</button>
		<?php endif; ?>

		<input type="hidden" name="certificate_id" value="<?php echo get_the_ID(); ?>">
		<?php wp_nonce_field( 'llms-cert-actions', '_llms_cert_actions_nonce' ); ?>

	</form>

</div>
