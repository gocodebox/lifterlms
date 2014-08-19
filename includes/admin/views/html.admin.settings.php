<div class="wrap lifterlms">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<h2 class="nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label )

					//to do: page does not exist yet. Need to set up the url like below:
					echo '<a href="' . admin_url( 'admin.php?page=llms-settings&tab=' . $name ) . '" class="nav-tab ' 
					. ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

				do_action( 'lifterlms_settings_tabs' );
			?>
		</h2>

		<?php
			do_action( 'lifterlms_sections_' . $current_tab );
			do_action( 'lifterlms_settings_' . $current_tab );
		?>
	</form>
</div>