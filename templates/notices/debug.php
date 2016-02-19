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

	<div class="llms-message"><?php print_r( $message ); ?></div>
	
<?php endforeach; ?>
