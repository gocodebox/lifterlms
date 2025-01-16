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
$data_msg     = $restrictions['is_restricted'] ? ' data-tooltip-msg="' . esc_html( strip_tags( llms_get_restriction_message( $restrictions ) ) ) . '"' : '';

// Get the section name for this lesson.
$section       = $lesson->get_parent_section() ? llms_get_post( $lesson->get_parent_section() ) : false;
$section_title = $section ? $section->post->post_title : '';
if ( isset( $total_lessons ) && $total_lessons ) {
	$lesson_screen_reader_msg = sprintf(
	/* translators: 1: lesson order, 2: total lessons, 3: section title */
		__( 'Lesson %1$d of %2$d within section %3$s.', 'lifterlms' ),
		isset( $order ) ? $order : $lesson->get( 'order' ),
		$total_lessons,
		$section_title
	);
} else {
	$lesson_screen_reader_msg = sprintf(
	/* translators: 1: lesson order, 2: section title */
		__( 'Lesson %1$d within section %2$s.', 'lifterlms' ),
		isset( $order ) ? $order : $lesson->get( 'order' ),
		$section_title
	);
}
?>

<div class="llms-lesson-preview<?php echo esc_attr( $lesson->get_preview_classes() ); ?>">
	<section
	<?php if ( $restrictions['is_restricted'] ) : ?>
		class="llms-lesson-locked"
		data-tooltip-msg="<?php echo esc_attr( strip_tags( llms_get_restriction_message( $restrictions ) ) ); ?>"
	<?php endif; ?>
	>
		<?php if ( $restrictions['is_restricted'] ) : ?>
			<div class="llms-lesson-link">
		<?php else : ?>
			<a class="llms-lesson-link" href="<?php echo esc_url( get_permalink( $lesson->get( 'id' ) ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $lesson->get( 'id' ) ) . ' ' . $lesson_screen_reader_msg ); ?>">
		<?php endif; ?>

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
						<span class="llms-lesson-counter" aria-hidden="true">
							<?php echo esc_html( sprintf( _x( '%1$d of %2$d', 'lesson order within section', 'lifterlms' ), isset( $order ) ? $order : $lesson->get( 'order' ), $total_lessons ) ); ?>
						</span>
						<?php echo wp_kses_post( $lesson->get_preview_icon_html() ); ?>
					</aside>

				<?php endif; ?>

				<div class="llms-main">
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

					<?php if ( apply_filters( 'llms_show_preview_excerpt', true ) && llms_get_excerpt( $lesson->get( 'id' ) ) ) : ?>
						<div class="llms-lesson-excerpt"><?php echo wp_kses_post( llms_get_excerpt( $lesson->get( 'id' ) ) ); ?></div>
					<?php endif; ?>
				</div>

				<span class="screen-reader-text"><?php echo esc_attr( $lesson_screen_reader_msg ); ?></span>

				<?php if ( $restrictions['is_restricted'] ) : ?>
					<span class="screen-reader-text"><?php echo esc_html( strip_tags( llms_get_restriction_message( $restrictions ) ) ); ?></span>
				<?php endif; ?>

			</div>

		<?php echo $restrictions['is_restricted'] ? '</div>' : '</a>'; ?>

		<div class="llms-lesson-meta">
			<?php
			/**
			 * Action fired after the lesson title in the lesson preview template.
			 *
			 * @since 7.5.0
			 *
			 * @param LLMS_Lesson $lesson The lesson's instance.
			 */
			do_action( 'llms_lesson_preview_after_title', $lesson )
			?>

			<?php
			if ( 'lesson' !== get_post_type( get_the_ID() ) ) :
				?>
				<?php
				if ( $lesson->is_quiz_enabled() ) :
					?>
					<span class="llms-lesson-has-quiz">
						<i class="fa fa-question-circle"></i>
					<?php esc_html_e( 'Has Quiz', 'lifterlms' ); ?>
					</span>
					<?php
				endif;
				?>

				<?php
				if ( function_exists( 'llms_lesson_has_assignment' ) && llms_lesson_has_assignment( $lesson->get( 'id' ) ) ) :
					?>
					<span class="llms-lesson-has-assignment">
						<i class="fa fa-pencil-square"></i>
					<?php esc_html_e( 'Has Assignment', 'lifterlms' ); ?>
					</span>
					<?php
				endif;
				?>
			<?php endif; ?>
		</div>
	</section>
</div>
