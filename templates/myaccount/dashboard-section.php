<?php
/**
 * Section template for dashboard index
 *
 * @since 3.14.0
 * @since 3.30.1 Added dynamic filter on the `$more` var to allow customization of the URL and text on the "More" button.
 * @version  3.30.1
 */

defined( 'ABSPATH' ) || exit;

$more = apply_filters( 'llms_' . $action . '_more', $more );
?>

<section class="llms-sd-section <?php echo $slug; ?>">

	<?php if ( $title ) : ?>
		<h3 class="llms-sd-section-title">
			<?php echo apply_filters( 'lifterlms_' . $action . '_title', $title ); ?>
		</h3>
	<?php endif; ?>

	<?php do_action( 'lifterlms_before_' . $action ); ?>

	<?php echo $content; ?>

	<?php if ( $more ) : ?>
		<footer class="llms-sd-section-footer">
			<a class="llms-button-secondary" href="<?php echo esc_url( $more['url'] ); ?>"><?php echo $more['text']; ?></a>
		</footer>
	<?php endif; ?>

	<?php do_action( 'lifterlms_after_' . $action ); ?>

</section>
