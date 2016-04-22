<div class="wrap lifterlms">

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<h2 class="llms-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {

				echo '<a href="' . admin_url( 'admin.php?page=llms-settings&tab=' . $name ) . '" class="llms-button-primary llms-nav-tab-settings '
				. ( $current_tab == $name ? 'llms-nav-tab-active' : '' ) . '">' . $label . '</a>'; }

				do_action( 'lifterlms_settings_tabs' );
			?>
		</h2>

		<?php
			do_action( 'lifterlms_sections_' . $current_tab );
			do_action( 'lifterlms_settings_' . $current_tab );
			do_action( 'lifterlms_settings_tabs_' . $current_tab );
		?>
		<div id="llms-form-wrapper">
		 <p class="submit">
		    <?php if ( $current_tab != 'general' || ! get_option( 'lifterlms_first_time_setup' ) ) : ?>
			    <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'lifterlms' ); ?>" />
		    <?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'lifterlms-settings' ); ?>
		</p>
		</div>

	</form>
</div>
