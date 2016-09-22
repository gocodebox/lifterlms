<?php
/**
 * The Template for displaying Certificates.
 *
 * @author 		codeBOX
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$image = llms_get_certificate_image();
?>
<div class="llms-certificate-container" style="width:<?php echo $image['width']; ?>px; height:<?php echo $image['height']; ?>px;">
	<img src="<?php echo $image['src']; ?>" alt="Cetrificate Background" class="certificate-background">
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
<div id="llms-print-certificate" class="no-print">
	<input type="button" class="llms-button-primary" onClick="window.print()" value="<?php echo _e( 'Print Certificate', 'lifterlms' ) ?>"/>
</div>
