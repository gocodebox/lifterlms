<?php
/**
 * Achievements Loop
 *
 * @package LifterLMS/Templates/Achievements
 *
 * @since 3.14.0
 * @version 6.0.0
 *
 * @param LLMS_User_Achievements[] $achievements List of achievements to display.
 * @param int                      $cols         Number of columns to display.
 * @param false|array             $pagination   Pagination arguments to pass to {@see llms_paginate_links()} or `false`
 *                                              when pagination is disabled.
 */

defined( 'ABSPATH' ) || exit;
?>

<?php
	/**
	 * Action run prior to the output of an achievement list.
	 *
	 * @since 3.14.0
	 */
	do_action( 'llms_before_achievement_loop' );
?>

<?php if ( $achievements ) : ?>

	<ul class="llms-achievements-loop listing-achievements <?php echo esc_attr( sprintf( 'loop-cols-%d', $cols ) ); ?>">

		<?php foreach ( $achievements as $achievement ) : ?>

			<li class="llms-achievement-loop-item achievement-item">
				<?php
					/**
					 * Action run to display a single achievement.
					 *
					 * @since 3.14.0
					 *
					 * @see llms_the_achievement() Hooked at priority 10.
					 *
					 * @param LLMS_User_Achievement $achievement Achievement object to display.
					 */
					do_action( 'llms_achievement_content', $achievement );
				?>
			</li>

		<?php endforeach; ?>

	</ul>

<?php else : ?>

	<p>
	<?php
		/**
		 * Filters the message displayed when no achievements have been earned by the student.
		 *
		 * @since 3.14.0
		 *
		 * @param string $message Message text..
		 */
		echo wp_kses_post( apply_filters( 'lifterlms_no_achievements_text', __( 'You do not have any achievements yet. Enroll in a course to get started!', 'lifterlms' ) ) );
	?>
	</p>

<?php endif; ?>

<?php if ( $pagination ) : ?>
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo llms_paginate_links( $pagination );
	?>
<?php endif; ?>

<?php
	/**
	 * Action run after to the output of an achievement list.
	 *
	 * @since 3.14.0
	 */
	do_action( 'llms_after_achievement_loop' );
?>
