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
<ul class="llms-error">
	<?php foreach ( $messages as $message ) : ?>

		<li><?php echo wp_kses_post( $message ); ?></li>
		
	<?php endforeach; ?>
</ul>
