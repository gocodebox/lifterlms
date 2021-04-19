<?php
/**
 * Certificate: Content
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 4.21.0 Make certificate background alt localizable.
 * @version 4.21.0
 */

defined( 'ABSPATH' ) || exit;

$cert  = new LLMS_User_Certificate( get_the_ID() );
$image = llms_get_certificate_image();
?>
<div class="llms-certificate-container" style="width:<?php echo $image['width']; ?>px; height:<?php echo $image['height']; ?>px;">
	<img src="<?php echo $image['src']; ?>" style="margin-bottom:-<?php echo $image['height']; ?>px;" alt="<?php esc_html_e( 'Certificate Background', 'lifterlms' ); ?>" class="certificate-background">
	<div id="certificate-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="llms-summary">

			<?php llms_print_notices(); ?>

			<?php do_action( 'before_lifterlms_certificate_main_content' ); ?>

			<h1><?php echo llms_get_certificate_title(); ?></h1>
			<?php echo llms_get_certificate_content(); ?>

			<?php do_action( 'after_lifterlms_certificate_main_content' ); ?>

		</div>
	</div>
</div>

<?php
if ( $cert->can_user_manage() ) {
	$is_sharing_enabled = $cert->is_sharing_enabled();
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
			<?php if ( get_post_type( $cert->get( 'id' ) ) === $cert->get( 'db_post_type' ) ) : ?>
				<button class="llms-button-secondary" type="submit" name="llms_enable_cert_sharing" value="<?php echo ! $is_sharing_enabled; ?>">
				<?php echo ( $is_sharing_enabled ? _e( 'Disable sharing', 'lifterlms' ) : _e( 'Enable sharing', 'lifterlms' ) ); ?>
					<i class="fa fa-share-alt" aria-hidden="true"></i>
				</button>
			<?php endif; ?>

			<input type="hidden" name="certificate_id" value="<?php echo get_the_ID(); ?>">
			<?php wp_nonce_field( 'llms-cert-actions', '_llms_cert_actions_nonce' ); ?>
		</form>
	</div>
	<?php
}
?>
