<div class="wrap lifterlms lifterlms-settings">

	<form action="<?php echo admin_url( 'admin.php?page=llms-settings&tab=' . $current_tab ); ?>" method="POST" id="mainform" enctype="multipart/form-data">

		<nav class="llms-nav-tab-wrapper">

			<?php do_action( 'lifterlms_before_settings_tabs' ); ?>

			<ul class="llms-nav-items">
			<?php foreach ( $tabs as $name => $label ) : $active = ( $current_tab == $name ) ? ' llms-active' : ''; ?>

				<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo admin_url( 'admin.php?page=llms-settings&tab=' . $name ); ?>"><?php echo $label; ?></a></li>

			<?php endforeach; ?>
			</ul>

			<?php do_action( 'lifterlms_after_settings_tabs' ); ?>

		</nav>

		<h1 style="display:none;"></h1>

		<?php do_action( 'lifterlms_settings_notices' ); ?>

		<?php
			do_action( 'lifterlms_sections_' . $current_tab );
			do_action( 'lifterlms_settings_' . $current_tab );
			do_action( 'lifterlms_settings_tabs_' . $current_tab );
		?>

		<div id="llms-form-wrapper">

			<input name="save" class="llms-button-primary" type="submit" value="<?php _e( 'Save Changes', 'lifterlms' ); ?>" />

			<?php wp_nonce_field( 'lifterlms-settings' ); ?>

		</div>

	</form>
</div>
