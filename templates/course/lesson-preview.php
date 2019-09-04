<?php
/**
 * Template for a lesson preview element
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 * @since       1.0.0
 * @version     3.19.2
 */
defined( 'ABSPATH' ) || exit;

$restrictions = llms_page_restricted( $lesson->get( 'id' ), get_current_user_id() );
$data_msg     = $restrictions['is_restricted'] ? ' data-tooltip-msg="' . esc_html( strip_tags( llms_get_restriction_message( $restrictions ) ) ) . '"' : '';
?>

<div class="llms-lesson-preview<?php echo $lesson->get_preview_classes(); ?>">
	<a class="llms-lesson-link<?php echo $restrictions['is_restricted'] ? ' llms-lesson-link-locked' : ''; ?>" href="<?php echo ( ! $restrictions['is_restricted'] ) ? get_permalink( $lesson->get( 'id' ) ) : '#llms-lesson-locked'; ?>"<?php echo $data_msg; ?>>

		<?php if ( 'course' === get_post_type( get_the_ID() ) ) : ?>

			<?php if ( apply_filters( 'llms_display_outline_thumbnails', true ) ) : ?>
				<?php if ( has_post_thumbnail( $lesson->get( 'id' ) ) ) : ?>
					<div class="llms-lesson-thumbnail">
						<?php echo get_the_post_thumbnail( $lesson->get( 'id' ) ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<aside class="llms-extra">
				<span class="llms-lesson-counter"><?php printf( _x( '%1$d of %2$d', 'lesson order within section', 'lifterlms' ), $lesson->get_order(), $total_lessons ); ?></span>
				<?php echo $lesson->get_preview_icon_html(); ?>
			</aside>

		<?php endif; ?>

		<section class="llms-main">
			<?php if ( 'lesson' === get_post_type( get_the_ID() ) ) : ?>
				<h6 class="llms-pre-text"><?php echo $pre_text; ?></h6>
			<?php endif; ?>
			<h5 class="llms-h5 llms-lesson-title"><?php echo get_the_title( $lesson->get( 'id' ) ); ?></h5>
			<?php if ( apply_filters( 'llms_show_preview_excerpt', true ) && llms_get_excerpt( $lesson->get( 'id' ) ) ) : ?>
				<div class="llms-lesson-excerpt"><?php echo llms_get_excerpt( $lesson->get( 'id' ) ); ?></div>
			<?php endif; ?>
		</section>

		<div class="clear"></div>

		<?php if ( $restrictions['is_restricted'] ) : ?>
		<?php endif; ?>

	</a>
</div>
