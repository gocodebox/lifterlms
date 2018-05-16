<?php
/**
 * Certificate: Content
 * @since   1.0.0
 * @version 3.18.0
 */

defined( 'ABSPATH' ) || exit;

$cert = new LLMS_User_Certificate( get_the_ID() );
$uid = get_current_user_id();

if ( $uid != $cert->get_user_id() && ! llms_can_user_bypass_restrictions( $uid ) ) {
	return _e( 'Certificate not found.', 'lifterlms' );
}

$image = llms_get_certificate_image();
?>
<div class="llms-certificate-container" style="width:<?php echo $image['width']; ?>px; height:<?php echo $image['height']; ?>px;">
	<img src="<?php echo $image['src']; ?>" style="margin-bottom:-<?php echo $image['height']; ?>px;" alt="Cetrificate Background" class="certificate-background">
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

<div class="llms-print-certificate no-print" id="llms-print-certificate">
	<button class="llms-button-secondary" onClick="window.print()" type="button">
		<?php echo _e( 'Print', 'lifterlms' ) ?>
		<i class="fa fa-print" aria-hidden="true"></i>
	</button>

	<form action="" method="POST">
		<button class="llms-button-secondary" type="submit" name="llms_generate_cert">
			<?php echo _e( 'Save', 'lifterlms' ) ?>
			<i class="fa fa-cloud-download" aria-hidden="true"></i>
		</button>

		<input type="hidden" name="certificate_id" value="<?php echo get_the_ID(); ?>">
		<?php wp_nonce_field( 'llms-cert-actions', '_llms_cert_actions_nonce' ); ?>
	</form>
</div>
