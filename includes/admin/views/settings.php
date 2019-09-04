<?php
/**
 * Admin Settings Page HTML
 *
 * @since    1.0.0
 * @version  3.29.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap lifterlms lifterlms-settings">

	<form action="" method="POST" id="mainform" enctype="multipart/form-data">

		<header class="llms-header">
			<div class="llms-inside-wrap">
				<img class="lifterlms-logo" src="<?php echo LLMS()->plugin_url(); ?>/assets/images/lifterlms-logo.png" alt="<?php esc_attr_e( 'LifterLMS Logo', 'lifterlms' ); ?>">

				<?php if ( apply_filters( 'llms_settings_' . $current_tab . '_has_save_button', true ) ) : ?>

					<div class="llms-save">

						<?php do_action( 'llms_before_admin_settings_save_button', $current_tab ); ?>

						<input name="save" class="llms-button-primary" type="submit" value="<?php echo apply_filters( 'llms_admin_settings_submit_button_text', __( 'Save Changes', 'lifterlms' ), $current_tab ); ?>" />

						<?php wp_nonce_field( 'lifterlms-settings' ); ?>

						<?php do_action( 'llms_after_admin_settings_save_button', $current_tab ); ?>

					</div>

				<?php endif; ?>
			</div>
		</header>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary">
			<div class="llms-inside-wrap">

				<?php do_action( 'lifterlms_before_settings_tabs' ); ?>

				<ul class="llms-nav-items">
				<?php
				foreach ( $tabs as $name => $label ) :
					$active = ( $current_tab == $name ) ? ' llms-active' : '';
					?>

					<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo admin_url( 'admin.php?page=llms-settings&tab=' . $name ); ?>"><?php echo $label; ?></a></li>

				<?php endforeach; ?>
				</ul>

				<?php do_action( 'lifterlms_after_settings_tabs' ); ?>

			</div>
		</nav>

		<div class="llms-inside-wrap">

			<h1 class="screen-reader-text"><?php echo $tabs[ $current_tab ]; ?></h1>

			<?php do_action( 'lifterlms_settings_notices' ); ?>

			<?php
				do_action( 'lifterlms_sections_' . $current_tab );
				do_action( 'lifterlms_settings_' . $current_tab );
				do_action( 'lifterlms_settings_tabs_' . $current_tab );
			?>

		</div>

	</form>

</div>
