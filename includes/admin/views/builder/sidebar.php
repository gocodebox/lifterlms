<?php
/**
 * Builder sidebar view template
 *
 * @since   3.16.0
 * @version 3.16.7
 */
?>

<script type="text/html" id="tmpl-llms-sidebar-template">

	<div class="llms-elements" id="llms-elements"></div>
	<div class="llms-utilities" id="llms-utilities"></div>

	<div class="llms-editor" id="llms-editor"></div>

	<footer class="llms-builder-save">

		<button class="llms-button-primary llms-save" data-status="saved" id="llms-save-button" disabled="disabled">
			<i></i><!-- placeholder for LLMS.Spinner -->
			<span class="llms-status-indicator status--saved"><?php _e( 'Saved', 'lifterlms' ); ?></span>
			<span class="llms-status-indicator status--unsaved"><?php _e( 'Save changes', 'lifterlms' ); ?></span>
			<span class="llms-status-indicator status--saving"><?php _e( 'Saving changes...', 'lifterlms' ); ?></span>
			<span class="llms-status-indicator status--error"><?php _e( 'Error saving changes...', 'lifterlms' ); ?></span>
		</button>

		<button class="llms-button-secondary llms-exit" id="llms-exit-button"><?php _e( 'Exit', 'lifterlms' ); ?></button>

	</footer class="llms-builder-save">

</script>
