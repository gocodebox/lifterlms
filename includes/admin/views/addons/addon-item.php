<?php
/**
 * Single Add-on Item View
 * Used on Add-Ons browser screen
 * @since    [version]
 * @version  [version]
 */
defined( 'ABSPATH' ) || exit;

?>
<li class="llms-add-on-item type--<?php echo esc_attr( $addon->get( 'type' ) ); ?>" id="<?php echo esc_attr( $addon->get( 'id' ) ); ?>">
	<div class="llms-add-on">

		<a class="llms-add-on-link" href="<?php echo esc_url( $addon->get_permalink() ); ?>" target="_blank">

			<header>
				<img alt="<?php echo $addon->get( 'title' ); ?> Banner" src="<?php echo esc_url( $addon->get( 'image' ) ); ?>">
				<h4><?php echo $addon->get( 'title' ); ?></h4>
			</header>

			<section>

				<p><?php echo llms_trim_string( $addon->get( 'description' ), 180 ); ?></p>

				<ul>
					<?php if ( $addon->get( 'author' )['name'] ) : ?>
						<li>
							<?php // Translators: %s = Author Name ?>
							<span><?php printf( __( 'Author: %s', 'lifterlms' ), $addon->get( 'author' )['name'] ); ?></span>
							<?php if ( $addon->get( 'author' )['image'] ) : ?>
								<img alt="<?php echo esc_attr( $addon->get( 'author' )['name'] ); ?> logo" src="<?php echo esc_url( $addon->get( 'author' )['image'] ); ?>">
							<?php endif; ?>
						</li>
					<?php endif; ?>

					<?php if ( $addon->is_installable() ) : ?>
						<?php // Translators: %s = Current Version Number ?>
						<li><?php printf( __( 'Version: %s', 'lifterlms' ), $addon->is_installed() ? $addon->get_installed_version() : $addon->get( 'version' ) ); ?></li>
						<?php if ( $addon->is_installed() && $addon->has_available_update() ) : ?>
							<?php // Translators: %s = Available Version Number ?>
							<li><strong><?php printf( __( 'Update Available: %s', 'lifterlms' ), $addon->get( 'version' ) ); ?></strong></li>
						<?php endif; ?>
					<?php endif; ?>
				</ul>

			</section>

		</a>

		<footer class="llms-actions">

			<?php if ( in_array( $addon->get_type(), array( 'external', 'support' ) ) || ( 'bundle' === $addon->get_type() && ! $addon->is_licensed() ) ) : ?>
				<a href="<?php echo esc_url( $addon->get_permalink() ); ?>" class="llms-status-icon external status--<?php echo esc_attr( $addon->get_license_status() ); ?>" target="_blank">
					<i class="fa fa-info-circle hide-on-hover" aria-hidden="true"></i>
					<i class="fa fa-external-link show-on-hover" aria-hidden="true"></i>
					<span class="llms-status-text"><?php _e( 'Learn more', 'lifterlms' ); ?></span>
				</a>
			<?php else :
				$url = $addon->is_licensed() ? 'https://lifterlms.com/my-account' : $addon->get_permalink(); ?>
				<a href="<?php echo esc_url( $url ); ?>" class="llms-status-icon status--<?php echo esc_attr( $addon->get_license_status() ); ?>" target="_blank">
					<i class="fa fa-key hide-on-hover" aria-hidden="true"></i>
					<i class="fa fa-external-link show-on-hover" aria-hidden="true"></i>
					<span class="llms-status-text"><?php echo $addon->get_license_status( true ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( $addon->is_installable() && ! $addon->is_installed() && $addon->is_licensed() ) : ?>
				<label class="llms-status-icon status--<?php echo esc_attr( $addon->get_install_status() ); ?>" for="<?php echo esc_attr( sprintf( '%s-install', $addon->get( 'id' ) ) ); ?>">
					<input class="llms-bulk-check" data-action="install" name="llms_install[]" id="<?php echo esc_attr( sprintf( '%s-install', $addon->get( 'id' ) ) ); ?>" type="checkbox" value="<?php echo esc_attr( $addon->get( 'id' ) ); ?>">
					<i class="fa fa-check-square-o" aria-hidden="true"></i>
					<i class="fa fa-cloud-download" aria-hidden="true"></i>
					<span class="llms-status-text"><?php _e( 'Install', 'lifterlms' ); ?></span>
				</label>
			<?php endif; ?>

			<?php if ( $addon->is_installable() && $addon->is_installed() ) : ?>
				<?php if ( $addon->is_active() ) : ?>
					<?php if ( 'theme' !== $addon->get_type() ) : ?>
						<label class="llms-status-icon status--<?php echo esc_attr( $addon->get_status() ); ?>" for="<?php echo esc_attr( sprintf( '%s-deactivate', $addon->get( 'id' ) ) ); ?>">
							<input class="llms-bulk-check" data-action="deactivate" name="llms_deactivate[]" id="<?php echo esc_attr( sprintf( '%s-deactivate', $addon->get( 'id' ) ) ); ?>" type="checkbox" value="<?php echo esc_attr( $addon->get( 'id' ) ); ?>">
							<i class="fa fa-check-square-o" aria-hidden="true"></i>
							<i class="fa fa-plug" aria-hidden="true"></i>
							<span class="llms-status-text"><?php _e( 'Deactivate', 'lifterlms' ); ?>
						</label>
					<?php endif; ?>
				<?php else : ?>
					<label class="llms-status-icon status--<?php echo esc_attr( $addon->get_status() ); ?>" for="<?php echo esc_attr( sprintf( '%s-activate', $addon->get( 'id' ) ) ); ?>">
						<input class="llms-bulk-check" data-action="activate" name="llms_activate[]" id="<?php echo esc_attr( sprintf( '%s-activate', $addon->get( 'id' ) ) ); ?>" type="checkbox" value="<?php echo esc_attr( $addon->get( 'id' ) ); ?>">
						<i class="fa fa-check-square-o" aria-hidden="true"></i>
						<i class="fa fa-plug" aria-hidden="true"></i>
						<span class="llms-status-text"><?php _e( 'Activate', 'lifterlms' ); ?>
					</label>
				<?php endif; ?>

				<?php if ( $addon->is_licensed() && $addon->has_available_update() ) : ?>
					<label class="llms-status-icon status--update-available" for="<?php echo esc_attr( sprintf( '%s-update', $addon->get( 'id' ) ) ); ?>">
						<input class="llms-bulk-check" data-action="update" name="llms_update[]" id="<?php echo esc_attr( sprintf( '%s-update', $addon->get( 'id' ) ) ); ?>" type="checkbox" value="<?php echo esc_attr( $addon->get( 'id' ) ); ?>">
						<i class="fa fa-check-square-o" aria-hidden="true"></i>
						<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>
						<span class="llms-status-text"><?php _e( 'Update', 'lifterlms' ); ?>
					</label>
					<a href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $addon->get( 'id' ) . '&section=changelog&TB_iframe=true&width=600&height=800' ); ?>" class="thickbox open-plugin-details-modal tip--bottom-left" data-tip="<?php esc_attr_e( 'View update details', 'lifterlms' ); ?>">
						<i class="fa fa-info-circle" aria-hidden="true"></i>
					</span>
				<?php endif; ?>
			<?php endif; ?>

		</footer>

	</div>
</li>
