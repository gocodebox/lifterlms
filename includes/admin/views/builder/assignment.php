<?php
/**
 * Assignment template
 *
 * @since   3.17.0
 * @version 3.17.0
 */
?>
<script type="text/html" id="tmpl-llms-assignment-template">

	<# if ( _.isEmpty( data ) ) { #>

		<div class="llms-quiz-empty">

			<p><?php esc_html_e( 'There\'s no assignment associated with this lesson.', 'lifterlms' ); ?></p>

			<button class="llms-element-button" id="llms-new-assignment" type="button">
				<?php esc_html_e( 'Create New Assignment', 'lifterlms' ); ?>
				<i class="fa fa-file" aria-hidden="true"></i>
			</button>

			<br>

			<button class="llms-element-button" id="llms-existing-assignment" type="button">
				<?php esc_html_e( 'Add Existing Assignment', 'lifterlms' ); ?>
				<i class="fa fa-file-text" aria-hidden="true"></i>
			</button>

		</div>

	<# } else { #>

		<?php do_action( 'llms_builder_assignment_settings' ); ?>

	<# } #>

</script>
