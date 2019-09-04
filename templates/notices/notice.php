<?php
/**
 * Show regular (info) notices
 *
 * @package     Lifterlms/Templates
 *
 * @since       1.0.0
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $messages ) {
	return;
}
?>
<?php do_action( 'lifterlms_before_notices' ); ?>
<?php foreach ( $messages as $message ) : ?>
	<div class="llms-notice"><?php print_r( $message ); ?></div>
<?php endforeach; ?>
<?php do_action( 'lifterlms_after_notices' ); ?>
