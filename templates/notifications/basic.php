<?php
/**
 * Basic Notification Template
 *
 * @package LifterLMS/Templates
 *
 * @since 3.8.0
 * @version 3.29.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="<?php echo esc_attr( $classes ); ?>"
	<?php foreach ( (array) $attributes as $att => $val ) : ?>
			<?php echo esc_attr( 'data-' . $att ); ?>="<?php echo esc_attr( $val ); ?>"
	<?php endforeach; ?>
	id="<?php echo esc_attr( 'llms-notification-' . $id ); ?>">

	<?php do_action( 'llms_before_basic_notification', $id ); ?>

	<i class="llms-notification-dismiss fa fa-times-circle" aria-hidden="true"></i>

	<section class="llms-notification-content">
		<div class="llms-notification-main">
			<h4 class="llms-notification-title"><?php echo esc_html( $title ); ?></h4>
			<div class="llms-notification-body"><?php echo wp_kses_post( $body ); ?></div>
		</div>

		<?php if ( $icon ) : ?>
			<aside class="llms-notification-aside">
				<img class="llms-notification-icon" alt="<?php echo esc_attr( $title ); ?>" src="<?php echo esc_url( $icon ); ?>">
			</aside>
		<?php endif; ?>
	</section>

	<?php if ( is_string( $footer ) && ! empty( $footer ) ) : ?>
		<footer class="llms-notification-footer">
			<?php echo wp_kses_post( $footer ); ?>
			<?php if ( 'new' !== $status ) : ?>
				<span class="llms-notification-date"><?php echo esc_html( $date ); ?></span>
			<?php endif; ?>
		</footer>
	<?php endif; ?>

	<?php do_action( 'llms_after_basic_notification', $id ); ?>

</div>
