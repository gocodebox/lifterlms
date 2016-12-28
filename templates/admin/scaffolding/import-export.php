<?php
/**
 * Import & Export LLMS Content
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_admin() ) { exit; }
?>

<div class="wrap lifterlms llms-import-export">

	<h1><?php _e( 'LifterLMS Import / Export', 'lifterlms' ); ?></h1>

	<div class="llms-options-page-contents llms-widget">

		<form action="" enctype="multipart/form-data" method="POST">

			<table class="form-table">

				<tr>
					<th><label><?php _e( 'Import Course(s)', 'lifterlms' ); ?></label></th>
					<td>
						<input accept="application/json" class="widefat" name="llms_import" type="file">
						<br>
						<button class="llms-button-primary small" type="submit"><?php _e( 'Import', 'lifterlms' ); ?></button>
					</td>

			</table>

		</form>



	</div>

</div>
