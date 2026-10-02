<?php
/**
 * Single Achievement Template
 *
 * @package LifterLMS/Templates/Achievements
 *
 * @since 1.0.0
 * @since [version] Open the achievement from a title button instead of a link around the card.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="llms-achievement" data-id="<?php echo esc_attr( $achievement->get( 'id' ) ); ?>" id="<?php printf( 'llms-achievement-%d', intval( $achievement->get( 'id' ) ) ); ?>">

	<?php do_action( 'lifterlms_before_achievement', $achievement ); ?>

	<div class="llms-achievement-image"><?php echo $achievement->get_image_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in method. ?></div>

	<?php
	printf(
		'<%1$s class="llms-achievement-title"><button type="button" class="llms-achievement-trigger" aria-haspopup="dialog">%2$s</button></%1$s>',
		tag_escape( llms_get_content_heading_tag( 'achievement' ) ),
		esc_html( $achievement->get( 'title' ) )
	);
	?>

	<div class="llms-achievement-info">
		<div class="llms-achievement-content"><?php echo wp_kses_post( $achievement->get( 'content' ) ); ?></div>
		<div class="llms-achievement-date"><?php printf( esc_html_x( 'Awarded on %s', 'achievement earned date', 'lifterlms' ), esc_html( $achievement->get_earned_date() ) ); ?></div>
	</div>

	<?php do_action( 'lifterlms_after_achievement', $achievement ); ?>

</div>

