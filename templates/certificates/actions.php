<?php
/**
 * Single certificate actions.
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since [version]
 * @version [version]
 *
 * @param LLMS_User_Certificate $certificate       Certificate object.
 * @param boolean               $is_shaing_enabled Whether or not sharing is enabled for the certificate.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="llms-print-certificate no-print" id="llms-print-certificate">
	<button class="llms-button-secondary" onClick="window.print()" type="button">
		<?php echo _e( 'Print', 'lifterlms' ); ?>
		<i class="fa fa-print" aria-hidden="true"></i>
	</button>

	<form action="" method="POST">
		<button class="llms-button-secondary" type="submit" name="llms_generate_cert">
		<?php echo _e( 'Save', 'lifterlms' ); ?>
			<i class="fa fa-cloud-download" aria-hidden="true"></i>
		</button>
		<?php if ( get_post_type( $certificate->get( 'id' ) ) === $certificate->get( 'db_post_type' ) ) : ?>
			<button class="llms-button-secondary" type="submit" name="llms_enable_cert_sharing" value="<?php echo ! $is_sharing_enabled; ?>">
			<?php echo ( $is_sharing_enabled ? _e( 'Disable sharing', 'lifterlms' ) : _e( 'Enable sharing', 'lifterlms' ) ); ?>
				<i class="fa fa-share-alt" aria-hidden="true"></i>
			</button>
		<?php endif; ?>

		<input type="hidden" name="certificate_id" value="<?php echo get_the_ID(); ?>">
		<?php wp_nonce_field( 'llms-cert-actions', '_llms_cert_actions_nonce' ); ?>
	</form>
</div>
