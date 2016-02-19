<?php
/**
 * Show error messages
 *
 * @author 		lifterLMS
 * @package 	lifterlms/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $messages ) { return; }
?>

<?php foreach ( $messages as $message ) : ?>

	<div class="llms-info"><?php echo wp_kses_post( $message ); ?></div>
	
<?php endforeach; ?>
