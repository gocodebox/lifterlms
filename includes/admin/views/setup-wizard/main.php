<?php
/**
 * Setup Wizard main view
 *
 * @since 4.4.4
 * @version 4.4.4
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
				<img src="<?php echo LLMS()->plugin_url(); ?>/assets/images/lifterlms-logo.png" alt="LifterLMS">
			</a>
		</h1>

		<ul class="llms-setup-progress">
			<?php foreach ( $steps as $slug => $name ) : ?>
				<li<?php echo ( $slug === $current ) ? ' class="current"' : ''; ?>><?php echo $name; ?></li>
			<?php endforeach; ?>
		</ul>

		<div class="llms-setup-content">
			<form action="" method="POST">

				<?php echo $step_html; ?>

				<?php if ( is_wp_error( $this->error ) ) : ?>
					<p class="error"><?php echo $this->error->get_error_message(); ?></p>
				<?php endif; ?>

				<p class="llms-setup-actions">
					<?php if ( 'intro' === $current ) : ?>
						<a href="<?php echo esc_url( admin_url() ); ?>" class="llms-button-secondary large"><?php _e( 'Skip setup', 'lifterlms' ); ?></a>
						<a href="<?php echo esc_url( admin_url() . '?page=llms-setup&step=' . $this->get_next_step() ); ?>" class="llms-button-primary large"><?php _e( 'Get Started Now', 'lifterlms' ); ?></a>
					<?php else : ?>
						<?php if ( $prev ) : ?>
							<a class="back-link" href="<?php echo $this->get_step_url( $prev ); ?>"><?php _e( 'Go back', 'lifterlms' ); ?></a>
						<?php endif; ?>
						<?php if ( $next ) : ?>
							<a href="<?php echo $this->get_step_url( $next ); ?>" class="llms-button-secondary large"><?php echo $this->get_skip_text( $current ); ?></a>
						<?php endif; ?>

						<?php if ( 'finish' === $current ) : ?>
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=course' ) ); ?>" class="llms-button-secondary large"><?php _e( 'Start from Scratch', 'lifterlms' ); ?></a>
						<?php endif; ?>

						<button class="llms-button-primary large" type="submit"><?php echo $this->get_save_text( $current ); ?></button>
						<input type="hidden" name="llms_setup_save" value="<?php echo $current; ?>">
						<?php wp_nonce_field( 'llms_setup_save', 'llms_setup_nonce' ); ?>
					<?php endif; ?>
				</p>

			</form>
		</div>

		<?php if ( 'finish' === $current ) : ?>
			<a class="dashboard-return" href="<?php echo admin_url(); ?>"><?php _e( 'Return to the WordPress Dashboard', 'lifterlms' ); ?></a>
		<?php endif; ?>

	</div>

</div>

<?php
