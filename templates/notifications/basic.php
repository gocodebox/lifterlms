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
<div class="<?php echo $classes; ?>"<?php echo $atts; ?> id="llms-notification-<?php echo $id; ?>">

	<?php do_action( 'llms_before_basic_notification', $id ); ?>

	<i class="llms-notification-dismiss fa fa-times-circle" aria-hidden="true"></i>

	<section class="llms-notification-content">
		<div class="llms-notification-main">
			<h4 class="llms-notification-title"><?php echo $title; ?></h4>
			<div class="llms-notification-body"><?php echo $body; ?></div>
		</div>

		<?php if ( $icon ) : ?>
			<aside class="llms-notification-aside">
				<img class="llms-notification-icon" alt="<?php echo $title; ?>" src="<?php echo $icon; ?>">
			</aside>
		<?php endif; ?>
	</section>

	<?php if ( is_string( $footer ) ) : ?>
		<footer class="llms-notification-footer">
			<?php echo $footer; ?>
			<?php if ( 'new' !== $status ) : ?>
				<span class="llms-notification-date"><?php echo $date; ?></span>
			<?php endif; ?>
		</footer>
	<?php endif; ?>

	<?php do_action( 'llms_after_basic_notification', $id ); ?>

</div>
