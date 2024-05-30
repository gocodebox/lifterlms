<?php
/**
 * Setup Wizard main view
 *
 * @package LifterLMS/Views/Admin/SetupWizard
 *
 * @since 4.4.4
 * @since 4.8.0 Unknown.
 * @since 7.4.0 Escape output.
 * @version 7.4.0
 *
 * @property string[]                $steps     Array of setup wizard steps.
 * @property string                  $current   Slug of the current step.
 * @property string|boolean          $prev      Slug of the previous step or `false` if no previous step found.
 * @property string|boolean          $next      Slug of the next step or `false` if no next step found.
 * @property string                  $step_html HTML content for the current step.
 * @property LLMS_Admin_Setup_Wizard $this      Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="llms-setup-wizard">

	<div class="llms-setup-wrapper">

		<h1 id="llms-logo">
			<a href="https://lifterlms.com/" target="_blank">
				<img src="<?php echo esc_url( llms()->plugin_url() . '/assets/images/lifterlms-logo-black.png' ); ?>" alt="LifterLMS">
			</a>
		</h1>

		<ul class="llms-setup-progress">
			<?php foreach ( $steps as $slug => $step ) : ?>
				<li<?php echo ( $slug === $current ) ? ' class="current"' : ''; ?>>
					<a href="<?php echo esc_url( $this->get_step_url( $slug ) ); ?>"><?php echo esc_html( $step['title'] ?? '' ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="llms-setup-content">
			<form action="" method="POST">

				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in template files.
					echo $step_html;
				?>

				<?php if ( is_wp_error( $this->error ) ) : ?>
					<p class="error"><?php echo esc_html( $this->error->get_error_message() ); ?></p>
				<?php endif; ?>

				<p class="llms-setup-actions">
					<?php if ( 'intro' === $current ) : ?>
						<a href="<?php echo esc_url( admin_url() ); ?>" class="llms-button-secondary large"><?php esc_html_e( 'Skip setup', 'lifterlms' ); ?></a>
						<a href="<?php echo esc_url( admin_url() . '?page=llms-setup&step=' . $this->get_next_step() ); ?>" class="llms-button-primary large"><?php esc_html_e( 'Get Started Now', 'lifterlms' ); ?></a>
					<?php else : ?>

						<a class="llms-exit-setup" data-confirm="<?php esc_attr_e( 'The site setup is incomplete! Are you sure you wish to exit?', 'lifterlms' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-settings' ) ); ?>"><?php esc_html_e( 'Exit Setup', 'lifterlms' ); ?></a>

						<?php if ( $next ) : ?>
							<a href="<?php echo esc_url( $this->get_step_url( $next ) ); ?>" class="llms-button-secondary large">
								<?php echo esc_html( $this->get_skip_text( $current ) ); ?>
							</a>
						<?php endif; ?>

						<?php if ( 'finish' === $current ) : ?>
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=course' ) ); ?>" class="llms-button-secondary large">
								<?php esc_html_e( 'Start from Scratch', 'lifterlms' ); ?>
							</a>
						<?php endif; ?>

						<button class="llms-button-primary large" type="submit" id="llms-setup-submit">
							<?php echo esc_html( $this->get_save_text( $current ) ); ?>
						</button>
						<input id="llms-setup-current-step" name="llms_setup_save" type="hidden" value="<?php echo esc_attr( $current ); ?>">
						<?php wp_nonce_field( 'llms_setup_save', 'llms_setup_nonce' ); ?>
					<?php endif; ?>
				</p>

			</form>
		</div>

	</div>

</div>

<?php
