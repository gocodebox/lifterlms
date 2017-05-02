<?php
/**
 * Single Student View: Information Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>

<?php do_action( 'llms_reporting_student_tab_info_stab_before_content' ); ?>

<div class="llms-widget-row">

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Registered', 'lifterlms' ); ?></p>
			<h2><?php echo $student->get_registration_date( 'M d, Y' ); ?></h2>
		</div>
	</div>

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Overall Progress', 'lifterlms' ); ?></p>
			<h2><?php echo $student->get_overall_progress(); ?>%</h2>
		</div>
	</div>

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Overall Grade', 'lifterlms' ); ?></p>
			<h2>
				<?php $grade = $student->get_overall_grade(); ?>
				<?php echo is_numeric( $grade ) ? $grade . '%' : $grade; ?>
			</h2>
		</div>
	</div>

</div>

<div class="d-1of4">
	<ul>
		<li><strong><?php _e( 'Address', 'lifterlms' ); ?></strong></li>
		<?php $address = $student->get( 'billing_address_1' );
		if ( $address ) : ?>
			<li>
				<?php echo $address; ?><br>
				<?php echo $student->get( 'billing_address_2' ); ?><br>
				<?php echo $student->get( 'billing_city' ); ?>, <?php echo $student->get( 'billing_state' ); ?> <?php echo $student->get( 'billing_zip' ); ?><br>
				<?php echo $student->get( 'billing_country' ); ?>
			</li>
		<?php endif; ?>
		<?php $phone = $student->get( 'phone' );
		if ( $phone ) : ?>
			<li><strong><?php _e( 'Phone', 'lifterlms' ); ?></strong>: <a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></li>
		<?php endif; ?>
	</ul>

</div>

<div class="clear">

<?php do_action( 'llms_reporting_student_tab_info_stab_after_content' ); ?>
