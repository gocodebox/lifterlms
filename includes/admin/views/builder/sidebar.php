<?php
/**
 * Builder sidebar view template
 *
 * @since 3.16.0
 * @since 7.2.0 Added video explainer wrapper element.
 * @version 7.2.0
 */
?>

<script type="text/html" id="tmpl-llms-sidebar-template">

	<div class="llms-elements" id="llms-elements"></div>
	<div class="llms-utilities" id="llms-utilities"></div>
	<div class="llms-video-explainer" id="llms-video-explainer">
		<span class="llms-video-explainer-trigger">
			<a href="https://lifterlms.com/docs/using-course-builder/" target="_blank">
				<img
					src="<?php echo esc_url( plugin_dir_url( LLMS_PLUGIN_FILE ) . 'assets/images/course-builder-video-thumbnail.jpg' ); ?>"
					alt="<?php esc_attr_e( 'How to Build Your Course Outline with the LifterLMS Course Builder', 'lifterlms' ); ?>"
				/>
			</a>
		</span>
	</div>

	<div class="llms-editor" id="llms-editor"></div>

	<footer class="llms-builder-save">

		<button class="llms-button-primary llms-save" data-status="saved" id="llms-save-button" disabled="disabled">
			<i></i><!-- placeholder for LLMS.Spinner -->
			<span class="llms-status-indicator status--saved"><?php esc_html_e( 'Saved', 'lifterlms' ); ?></span>
			<span class="llms-status-indicator status--unsaved"><?php esc_html_e( 'Save changes', 'lifterlms' ); ?></span>
			<span class="llms-status-indicator status--saving"><?php esc_html_e( 'Saving changes...', 'lifterlms' ); ?></span>
			<span class="llms-status-indicator status--error"><?php esc_html_e( 'Error saving changes...', 'lifterlms' ); ?></span>
		</button>

		<button class="llms-button-secondary llms-exit" id="llms-exit-button"><?php esc_html_e( 'Exit', 'lifterlms' ); ?></button>

	</footer class="llms-builder-save">

</script>
