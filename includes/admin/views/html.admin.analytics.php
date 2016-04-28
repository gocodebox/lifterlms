<div class="wrap lifterlms">

<h1 class="llms-page-header"><?php _e( 'Analytics', 'lifterlms' ); ?></h1>

	<form method="post" id="llms-form-wrapper">
<?php
global $submenu;

?>
		<h2 class="llms-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {

				echo '<a href="' . admin_url( 'admin.php?page=llms-analytics&tab=' . $name ) . '" class="llms-button-primary llms-nav-tab '
				. ( $current_tab == $name ? 'llms-nav-tab-active' : '' ) . '">' . $label . '</a>'; }

				do_action( 'lifterlms_analytics_tabs' );
			?>
		</h2>

		<?php
			do_action( 'lifterlms_sections_' . $current_tab );
			do_action( 'lifterlms_analytics_' . $current_tab );
			do_action( 'lifterlms_analytics_tabs_' . $current_tab );
		?>

		<input type="hidden" name="subtab" id="last_tab" />
		<?php wp_nonce_field( 'lifterlms-analytics' ); ?>

	</form>
</div>
