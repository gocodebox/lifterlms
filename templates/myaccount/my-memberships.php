<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$person = new LLMS_Person();
$memberships = $person->get_user_memberships_data( get_current_user_id(), '_status' );
?>

<div class="llms-sd-section llms-my-memberships">
	<h3 class="llms-sd-section-title"><?php echo apply_filters( 'lifterlms_my_memberships_title', __( 'My Memberships', 'lifterlms' ) ); ?></h3>

	<?php do_action( 'lifterlms_before_my_memberships' ); ?>

	<?php if ( $memberships ) : ?>

		<ul class="listing-memberships">

		<?php foreach ( $memberships as $mid => $data ) : ?>

			<?php $m = get_post( $mid ); ?>

			<li class="membership-item">

				<?php do_action( 'lifterlms_sd_before_membership', $mid ); ?>

				<strong><a href="<?php echo get_permalink( $mid ); ?>"><?php echo get_the_title( $mid ); ?></a></strong><br>
				<?php echo sprintf( __( 'Enrolled: %s', 'lifterlms' ), LLMS_Date::pretty_date( $data['_start_date']->updated_date ) ); ?><br>
				<?php echo sprintf( __( 'Status: %s', 'lifterlms' ), llms_get_enrollment_status_name( $data['_status']->meta_value ) ); ?>

				<?php do_action( 'lifterlms_sd_after_membership', $mid ); ?>

			</li>

		<?php endforeach; ?>

		</ul>

	<?php else : ?>

		<p><?php echo __( 'You are not currently enrolled in any memberships.', 'lifterlms' ); ?></p>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_my_memberships' ); ?>

</div>
