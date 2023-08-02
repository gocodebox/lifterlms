<?php
/**
 * Admin notice template.
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @param int    $id            Unique notice ID.
 * @param string $type          Notice type (error|warning|success|info).
 * @param string $icon          Dashicon class name or `lifterlms`.
 * @param string $template      Template to use for the notice.
 * @param string $template_path Path to the template.
 * @param string $default_path  Path to the default template.
 * @param bool   $dismissible   Whether the notice is dismissible.
 * @param string $dismiss_url   URL to dismiss the notice.
 * @param bool   $flash         Whether the notice should be deleted after displaying.
 * @param string $html          Notice content.
 * @param bool   $remindable    Whether the notice is remindable.
 * @param string $remind_url    URL to remind the notice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$id            = $id ?? null;
$type          = $type ?? null;
$title         = $title ?? null;
$icon          = $icon ?? null;
$template      = $template ?? null;
$template_path = $template_path ?? null;
$default_path  = $default_path ?? null;
$dismissible   = $dismissible ?? true;
$dismiss_url   = $dismiss_url ?? null;
$flash         = $flash ?? null;
$html          = $html ?? null;
$remindable    = $remindable ?? false;
$remind_url    = $remind_url ?? null;

?>
<div class="notice notice-<?php echo esc_attr( $type ); ?> llms-admin-notice" id="llms-notice<?php echo absint( $id ); ?>" style="position:relative;">
	<div class="llms-admin-notice-icon">
		<?php if ( 'lifterlms' === $icon ) : ?>
			<div class="llms-admin-notice-lifterlms-icon">
				<span class="screen-reader-text">
					<?php esc_html_e( 'LifterLMS icon', 'lifterlms' ); ?>
				</span>
			</div>
		<?php else: ?>
			<div class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>">
				<span class="screen-reader-text">
					<?php echo esc_html( ucwords( str_replace( '-', ' ', $icon ) ) ); ?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<div class="llms-admin-notice-content">
		<?php if ( $dismissible ) : ?>
			<a class="notice-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss', 'lifterlms' ); ?></span>
			</a>
		<?php endif; ?>

		<?php if ( $title ) : ?>

			<h3><?php echo esc_html( $title ); ?></h3>

		<?php endif; ?>

		<?php if ( ! empty( $template ) ) : ?>

			<?php llms_get_template(
				$template,
				array(),
				$template_path,
				$default_path
			); ?>

		<?php elseif ( ! empty( $html ) ) : ?>

			<?php echo wpautop( wp_kses_post( $html ) ); ?>

		<?php endif; ?>

		<?php if ( $remindable ) : ?>
			<p style="text-align:right;">
				<a class="button" href="<?php echo esc_url( $remind_url ); ?>">
					<?php esc_html_e( 'Remind me later', 'lifterlms' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</div>
