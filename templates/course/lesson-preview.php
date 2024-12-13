<?php
/**
 * Template for a lesson preview element
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 3.19.2 Unknown.
 * @since 4.4.0 Use the passed `$order` param if available, in favor of retrieving the lesson's order post meta.
 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_order()` method with `LLMS_Lesson::get( 'order' )`.
 * @since 7.5.0 Added `llms_lesson_preview_before_title` and `llms_lesson_preview_after_title` action hooks.
 * @version 7.5.0
 *
 * @var LLMS_Lesson $lesson        The lesson object.
 * @var string      $pre_text      The text to display before the lesson.
 * @var int         $total_lessons The number of lessons in the section.
 */
defined( 'ABSPATH' ) || exit;

$restrictions = llms_page_restricted( $lesson->get( 'id' ), get_current_user_id() );
?>

<div class="llms-lesson-preview<?php echo esc_attr( $lesson->get_preview_classes() ); ?>">
	<a class="llms-lesson-link<?php echo $restrictions['is_restricted'] ? ' llms-lesson-link-locked' : ''; ?>" href="<?php echo ( ! $restrictions['is_restricted'] ) ? esc_url( get_permalink( $lesson->get( 'id' ) ) ) : '#llms-lesson-locked'; ?>"<?php echo $restrictions['is_restricted'] ? ' aria-disabled="true" data-tooltip-msg="' . esc_attr( strip_tags( llms_get_restriction_message( $restrictions ) ) ) . '"' : ''; ?>>

		<?php if ( 'course' === get_post_type( get_the_ID() ) ) : ?>

			<?php if ( apply_filters( 'llms_display_outline_thumbnails', true ) ) : ?>
				<?php if ( has_post_thumbnail( $lesson->get( 'id' ) ) ) : ?>
					<div class="llms-lesson-thumbnail">
						<?php echo wp_kses_post( get_the_post_thumbnail( $lesson->get( 'id' ) ) ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

		<?php endif; ?>

		<div class="llms-lesson-preview-row">

			<?php if ( 'course' === get_post_type( get_the_ID() ) ) : ?>

				<aside class="llms-extra">
					<span class="llms-lesson-counter"><?php echo esc_html( sprintf( _x( '%1$d of %2$d', 'lesson order within section', 'lifterlms' ), isset( $order ) ? $order : $lesson->get( 'order' ), $total_lessons ) ); ?></span>
					<?php echo wp_kses_post( $lesson->get_preview_icon_html() ); ?>
				</aside>

			<?php endif; ?>

			<section class="llms-main">
				<?php if ( 'lesson' === get_post_type( get_the_ID() ) ) : ?>
					<div class="llms-pre-text"><?php echo wp_kses_post( $pre_text ); ?></div>
				<?php endif; ?>
				<?php
				/**
				 * Action fired before the lesson title in the lesson preview template.
				 *
				 * @since 7.5.0
				 *
				 * @param LLMS_Lesson $lesson The lesson's instance.
				 */
				do_action( 'llms_lesson_preview_before_title', $lesson )
				?>
				<div class="llms-lesson-title"><?php echo esc_html( get_the_title( $lesson->get( 'id' ) ) ); ?></div>
				<?php
				/**
				 * Action fired before the lesson title in the lesson preview template.
				 *
				 * @since 7.5.0
				 *
				 * @param LLMS_Lesson $lesson The lesson's instance.
				 */
				do_action( 'llms_lesson_preview_after_title', $lesson )
				?>
				<?php if ( apply_filters( 'llms_show_preview_excerpt', true ) && llms_get_excerpt( $lesson->get( 'id' ) ) ) : ?>
					<div class="llms-lesson-excerpt"><?php echo wp_kses_post( llms_get_excerpt( $lesson->get( 'id' ) ) ); ?></div>
				<?php endif; ?>
			</section>

			<?php if ( $restrictions['is_restricted'] ) : ?>
				<span class="sr-only">
					<?php echo esc_attr( strip_tags( llms_get_restriction_message( $restrictions ) ) ); ?>
				</span>
			<?php endif; ?>

		</div>
	</a>
</div>
