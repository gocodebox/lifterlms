<div class="wrap lifterlms">

<h1 class="llms-page-header"><?php _e( 'Students', 'lifterlms' ); ?></h1>

	<form method="post" id="llms-form-wrapper">

		<h2 class="llms-nav-tab-wrapper">
			<?php
				$search = LLMS()->session->get( 'llms_students_search' );

			if ( ! empty( $search ) && isset( $search->last_student_viewed ) ) {

				foreach ( $tabs as $name => $label ) {

					if ( $name !== 'dashboard' ) {
						$student_var = '&student=' . $search->last_student_viewed;
					} else {
						$student_var = '';
					}

					echo '<a href="' . admin_url( 'admin.php?page=llms-students&tab=' . $name . $student_var ) . '" class="llms-button-primary llms-nav-tab '
						. ( $current_tab == $name ? 'llms-nav-tab-active' : '' ) . '">' . $label . '</a>';

				}
					do_action( 'lifterlms_students_tabs' );
			} else {
				echo '<a href="' . admin_url( 'admin.php?page=llms-students&tab=dashboard' ) . '" class="llms-button-primary llms-nav-tab '
						. ( $current_tab == 'dashboard' ? 'llms-nav-tab-active' : '' ) . '">Dashboard</a>';
			}


			?>
		</h2>

		<?php
			do_action( 'lifterlms_sections_' . $current_tab );
			do_action( 'lifterlms_students_' . $current_tab );
			do_action( 'lifterlms_students_tabs_' . $current_tab );
		?>

		<input type="hidden" name="subtab" id="last_tab" />
		<?php wp_nonce_field( 'lifterlms-students' ); ?>

	</form>
</div>
