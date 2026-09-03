<?php
/**
 * Lesson Progression actions
 * Mark Complete & Mark Incomplete buttons
 * Take Quiz Button when quiz attached
 *
 * @since 1.0.0
 * @since 3.33.0 Only render on lesson post types.
 * @since 10.0.7 Use `llms_can_user_complete_lesson()` to gate rendering.
 * @since 10.1.0 Added `wp-element-button` class to the Take Quiz button so it inherits theme button styling.
 * @since [version] Disable Mark Complete / Take Quiz in markup when minimum time is not yet met.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = llms_get_post( $post );
if ( ! $lesson || ! is_a( $lesson, 'LLMS_Lesson' ) ) {
	return;
}

if ( ! llms_can_user_complete_lesson( get_current_user_id(), $lesson ) ) {
	return;
}

$student = llms_get_student( get_current_user_id() );

$time_gated = $student
	&& $lesson->has_minimum_time()
	&& ! $student->is_complete( $lesson->get( 'id' ), 'lesson' )
	&& ! llms_has_met_lesson_minimum_time( get_current_user_id(), $lesson );
?>

<div class="clear"></div>
<div class="llms-lesson-button-wrapper">

	<?php do_action( 'llms_before_lesson_buttons', $lesson, $student ); ?>

	<?php if ( $student->is_complete( $lesson->get( 'id' ), 'lesson' ) ) : ?>

		<?php if ( llms_show_mark_complete_button( $lesson ) ) : ?>

			<?php echo wp_kses_post( apply_filters( 'llms_lesson_complete_text', esc_html__( 'Lesson Complete', 'lifterlms' ) ) ); ?>
			<?php do_action( 'llms_after_lesson_complete_text', $lesson ); ?>

			<?php if ( 'yes' === get_option( 'lifterlms_retake_lessons', 'no' ) || apply_filters( 'lifterlms_retake_lesson_' . $lesson->get( 'parent_course' ), false ) ) : ?>

				<form action="" class="llms-incomplete-lesson-form" method="POST" name="mark_incomplete">

					<?php do_action( 'lifterlms_before_mark_incomplete_lesson' ); ?>

					<input type="hidden" name="mark-incomplete" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
					<input type="hidden" name="action" value="mark_incomplete" />
					<?php wp_nonce_field( 'mark_incomplete' ); ?>

					<?php
					llms_form_field(
						array(
							'columns'     => 12,
							'classes'     => 'llms-button-secondary auto button',
							'id'          => 'llms_mark_incomplete',
							'value'       => apply_filters( 'lifterlms_mark_lesson_incomplete_button_text', __( 'Mark Incomplete', 'lifterlms' ), $lesson ),
							'last_column' => true,
							'name'        => 'mark_incomplete',
							'required'    => false,
							'type'        => 'submit',
						)
					);
					?>

					<?php do_action( 'lifterlms_after_mark_incomplete_lesson' ); ?>

				</form>

			<?php endif; ?>

		<?php endif; ?>

	<?php else : ?>

		<?php if ( llms_show_mark_complete_button( $lesson ) ) : ?>

			<form action="" class="llms-complete-lesson-form" method="POST" name="mark_complete">

				<?php do_action( 'lifterlms_before_mark_complete_lesson' ); ?>

				<input type="hidden" name="mark-complete" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
				<input type="hidden" name="action" value="mark_complete" />
				<?php wp_nonce_field( 'mark_complete' ); ?>

				<?php
				$complete_classes = 'llms-button-primary auto button';
				$complete_atts    = array();
				if ( $time_gated ) {
					$complete_classes                    .= ' llms-lesson-time-disabled';
					$complete_atts['data-llms-lock-time'] = '1';
				}
				llms_form_field(
					array(
						'columns'     => 12,
						'classes'     => $complete_classes,
						'attributes'  => $complete_atts,
						'disabled'    => $time_gated,
						'id'          => 'llms_mark_complete',
						'value'       => apply_filters( 'lifterlms_mark_lesson_complete_button_text', __( 'Mark Complete', 'lifterlms' ), $lesson ),
						'last_column' => true,
						'name'        => 'mark_complete',
						'required'    => false,
						'type'        => 'submit',
					)
				);
				?>

				<?php do_action( 'lifterlms_after_mark_complete_lesson' ); ?>

			</form>

		<?php endif; ?>

	<?php endif; ?>

	<?php if ( llms_show_take_quiz_button( $lesson ) ) : ?>

		<?php do_action( 'llms_before_start_quiz_button' ); ?>

		<?php
		$quiz_classes = array( 'llms-button-action', 'auto', 'button', 'wp-element-button' );
		$quiz_atts    = array();
		if ( $time_gated ) {
			$quiz_classes[]                   = 'llms-lesson-time-disabled';
			$quiz_atts['data-llms-lock-time'] = '1';
		}

		/**
		 * Filters HTML attributes for the Take Quiz button.
		 *
		 * Add-ons may add `data-llms-lock-{id}` attributes so the button stays
		 * disabled until every progression requirement is met.
		 *
		 * @since [version]
		 *
		 * @param array       $quiz_atts Attribute key/value pairs.
		 * @param LLMS_Lesson $lesson    Lesson object.
		 */
		$quiz_atts = apply_filters( 'llms_start_quiz_button_attributes', $quiz_atts, $lesson );

		/**
		 * Filters CSS classes for the Take Quiz button.
		 *
		 * @since [version]
		 *
		 * @param string[]    $quiz_classes CSS class names.
		 * @param LLMS_Lesson $lesson       Lesson object.
		 */
		$quiz_classes = apply_filters( 'llms_start_quiz_button_classes', $quiz_classes, $lesson );

		?>
		<a class="<?php echo esc_attr( implode( ' ', $quiz_classes ) ); ?>" id="llms_start_quiz" href="<?php echo esc_url( get_permalink( $lesson->get( 'quiz' ) ) ); ?>"
			<?php
			foreach ( $quiz_atts as $quiz_attr => $quiz_attr_val ) {
				printf( '%s="%s" ', esc_attr( $quiz_attr ), esc_attr( $quiz_attr_val ) );
			}
			?>
		>
			<?php echo wp_kses_post( apply_filters( 'lifterlms_start_quiz_button_text', esc_html__( 'Take Quiz', 'lifterlms' ), $lesson->get( 'quiz' ), $lesson ) ); ?>
		</a>

		<?php do_action( 'llms_after_start_quiz_button' ); ?>

	<?php endif; ?>

	<?php do_action( 'llms_after_lesson_buttons', $lesson, $student ); ?>

</div>
