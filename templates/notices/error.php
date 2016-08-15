<?php
/**
 * Show error notices
 *
 * @author 		LifterLMS
 * @package 	Lifterlms/Templates
 * @since       1.0.0
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! $messages ) { return; }
?>
<?php do_action( 'lifterlms_before_error_notices' ); ?>
<ul class="llms-notice llms-error">
	<?php foreach ( $messages as $message ) : ?>
		<li><?php echo wp_kses_post( $message ); ?></li>
	<?php endforeach; ?>
</ul>
<?php do_action( 'lifterlms_after_error_notices' ); ?>
