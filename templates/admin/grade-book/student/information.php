<?php
/**
 * Single Student View: Information Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>

<p>
	<h4><?php _e( 'Address', 'lifterlms' ); ?></h4>
	<?php echo $student->get( 'billing_address_1' ); ?><br>
	<?php echo $student->get( 'billing_address_2' ); ?><br>
	<?php echo $student->get( 'billing_city' ); ?>, <?php echo $student->get( 'billing_state' ); ?> <?php echo $student->get( 'billing_zip' ); ?><br>
	<?php echo $student->get( 'billing_country' ); ?>
</p>

<?php if ( $phone = $student->get( 'phone' ) ): ?>
	<p>
		<h4><?php _e( 'Phone', 'lifterlms' ); ?></h4>
			<a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a>
	</p>
<?php endif; ?>
