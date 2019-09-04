<?php
/**
 * Achievements Loop
 *
 * @package LifterLMS/Templates
 *
 * @since    3.14.0
 * @version  3.14.0
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'llms_before_achievement_loop' ); ?>

	<?php if ( $achievements ) : ?>

		<ul class="llms-achievements-loop listing-achievements <?php printf( 'loop-cols-%d', $cols ); ?>">

			<?php foreach ( $achievements as $achievement ) : ?>

				<li class="llms-achievement-loop-item achievement-item">
					<?php do_action( 'llms_achievement_content', $achievement ); ?>
				</li>

			<?php endforeach; ?>

		</ul>

	<?php else : ?>

		<p><?php echo apply_filters( 'lifterlms_no_achievements_text', __( 'You do not have any achievements yet. Enroll in a course to get started!', 'lifterlms' ) ); ?></p>

	<?php endif; ?>

<?php do_action( 'llms_after_achievement_loop' ); ?>
