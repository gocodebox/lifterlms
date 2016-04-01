<?php
/**
 * Display a list of Student's earned certificates on the My Account page
 *
 * @updated 2.4.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$s = new LLMS_Student();
$certificates = $s->get_certificates();
?>

<div class="llms-my-certificates">
	<?php echo '<h3>' . apply_filters( 'lifterlms_my_certificates_title', __( 'My Certificates', 'lifterlms' ) ) . '</h3>'; ?>
	<?php if ( $certificates ) : ?>
		<ul class="listing-certificates">
		<?php foreach ( $certificates as $c ) : ?>
			<li class="certificate-item">
				<div>
					<h4><?php echo get_the_title( $c->certificate_id ); ?></h4>
				</div>

				<div>
					<p><?php echo date( 'F d, Y', strtotime( $c->earned_date ) ); ?></p>
				</div>

				<div>
					<span><a href="<?php echo get_permalink( $c->certificate_id ); ?>" target="_blank"><?php _e( 'View Certificate','lifterlms' );?></a></span>
				</div>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<?php echo  '<p>' .__( 'Complete courses and lessons to earn certificates.', 'lifterlms' ) . '</p>'; ?>
	<?php endif; ?>
</div>
