<?php

/**
 * Template for favorite button
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since [version]
 *
 * @var LLMS_Lesson     $lesson        The lesson object.
 * @var LLMS_Student    $student       A LLMS_Student object.
 */
defined( 'ABSPATH' ) || exit;

global $post;

$lesson  = new LLMS_Lesson( $post->ID );
$student = llms_get_student( get_current_user_id() );
$total_favorites = get_total_favorites( $lesson->get( 'id' ) );
?>

<div class="llms-favorite-wrapper">

	<?php do_action( 'llms_before_favorite_button', $lesson, $student ); ?>
	
	<?php if ( $student->is_favorite( $lesson->get( 'id' ), 'lesson' ) ) : ?>

		<!-- TODO: Dynamic data-type [Lesson, Course, Instructor] value -->
		<i data-action="unfavorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart llms-unfavorite-btn llms-favorite-action"></i>

		<form action="" class="llms-unfavorite-lesson-form" method="POST" name="mark_unfavorite">

			<?php do_action( 'lifterlms_before_mark_unfavorite_lesson' ); ?>

			<input type="hidden" name="mark-unfavorite" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
			<!-- TODO: Dynamic [Lesson, Course, Instructor] value -->
			<input type="hidden" name="type" value="lesson" />
			<input type="hidden" name="action" value="mark_unfavorite" />
			<?php wp_nonce_field( 'mark_unfavorite' ); ?>

			<?php
			llms_form_field(
				array(
					'columns'     => 12,
					'classes'     => 'llms-button-secondary',
					'id'          => 'llms_mark_unfavorite',
					'value'       => apply_filters( 'lifterlms_mark_lesson_unfavorite_button_text', sprintf( '<i class="fa fa-heart"></i> %d', $total_favorites ), $lesson ),
					'last_column' => true,
					'name'        => 'mark_unfavorite',
					'required'    => false,
					'type'        => 'submit',
				)
			);
			?>

			<?php do_action( 'lifterlms_after_mark_unfavorite_lesson' ); ?>

		</form>

	<?php else : ?>
		
		<!-- TODO: Dynamic data-type [Lesson, Course, Instructor] value -->
		<i data-action="favorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart-o llms-favorite-btn llms-favorite-action"></i>

		<form action="" class="llms-favorite-lesson-form" method="POST" name="mark_favorite">

			<?php do_action( 'lifterlms_before_mark_favorite_lesson' ); ?>

			<input type="hidden" name="mark-favorite" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
			<!-- TODO: Dynamic [Lesson, Course, Instructor] value -->
			<input type="hidden" name="type" value="lesson" />
			<input type="hidden" name="action" value="mark_favorite" />
			<?php wp_nonce_field( 'mark_favorite' ); ?>

			<?php
			llms_form_field(
				array(
					'columns'     => 12,
					'classes'     => 'llms-button-primary button llms-favorite',
					'id'          => 'llms_mark_favorite',
					'value'       => apply_filters( 'lifterlms_mark_lesson_favorite_button_text', sprintf( '<i class="fa fa-heart-o"></i> %d', $total_favorites ), $lesson ),
					'last_column' => true,
					'name'        => 'mark_favorite',
					'required'    => false,
					'type'        => 'submit',
				)
			);
			?>

			<?php do_action( 'lifterlms_after_mark_favorite_lesson' ); ?>

		</form>

	<?php endif; ?>

	<?php do_action( 'llms_after_favorite_button', $lesson, $student ); ?>

</div>
