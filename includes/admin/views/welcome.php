<?php
/**
 * Admin Welcome Screen HTML
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap lifterlms lifterlms-settings">

	<header class="llms-header">
		<div class="llms-inside-wrap">
			<h1>
				Welcome to <img class="lifterlms-logo" src="<?php echo LLMS()->plugin_url(); ?>/assets/images/lifterlms-logo.png" alt="<?php esc_attr_e( 'LifterLMS', 'lifterlms' ); ?>">
			</h1>
		</div>
	</header>

	<nav class="llms-nav-tab-wrapper llms-nav-secondary">
		<div class="llms-inside-wrap">

			<?php do_action( 'lifterlms_before_welcome_links' ); ?>

			<ul class="llms-nav-items">
			<?php foreach ( $links as $link ) : ?>
				<li class="llms-nav-item">
					<a class="llms-nav-link" href="<?php echo esc_url( $link['url'] ); ?>" title="<?php echo esc_attr( $link['title'] ); ?>" target="_blank">
						<span class="dashicons dashicons-<?php echo $link['icon']; ?>"></span>
						<?php echo $link['text']; ?>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>

			<?php do_action( 'lifterlms_after_welcome_links' ); ?>

		</div>
	</nav>

	<div class="llms-inside-wrap">



	</div>

</div>
