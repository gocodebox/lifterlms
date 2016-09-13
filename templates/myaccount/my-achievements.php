<?php
/**
 * User Achievements Template
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$user = new LLMS_Person;
$count = ( empty( $count ) ) ? 1000 : $count; // shortcodes will define $count and load the contents of this template
$user_id = ( empty( $user_id ) ) ? get_current_user_id() : $user_id;
$achievements = $user->get_user_achievements( $count, $user_id );
?>
<div class="llms-sd-section llms-my-achievements">
	<h3 class="llms-sd-section-title llms-my-achievements-title"><?php echo apply_filters( 'lifterlms_my_achievements_title', __( 'My Achievements', 'lifterlms' ) ); ?></h3>

	<?php do_action( 'lifterlms_before_achievements' ); ?>

	<?php if ($achievements) : ?>
		<ul class="listing-achievements">
			<?php foreach ( $achievements as $achievement ) : ?>
				<li class="achievement-item">

					<?php do_action( 'lifterlms_before_achievement', $achievement ); ?>

					<div class="llms-achievement-image"><img alt="<?php echo esc_attr( $achievement['title'] ); ?>" src="<?php echo $achievement['image']; ?>"></div>

					<h4 class="llms-achievement-title"><?php echo $achievement['title']; ?></h4>

					<?php if ( $achievement['content'] ) : ?>
						<div class="llms-achievement-content"><p><?php echo $achievement['content']; ?></p></div>
					<?php endif; ?>

					<div class="llms-achievement-date"><p><?php echo $achievement['date']; ?></p></div>

					<?php do_action( 'lifterlms_after_achievement', $achievement ); ?>

				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p><?php echo apply_filters( 'lifterlms_no_achievements_text', __( 'Complete courses and lessons to earn achievements.', 'lifterlms' ) ); ?></p>
	<?php endif; ?>

	<?php do_action( 'lifterlms_after_achievements' ); ?>

</div>
