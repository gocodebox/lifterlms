<?php
/**
 * Builder lesson settings template
 *
 * @since   3.17.0
 * @version 3.17.2
 */
?>
<script type="text/html" id="tmpl-llms-lesson-settings-template">

	<header class="llms-model-header" id="llms-lesson-header">

		<h3 class="llms-headline llms-model-title">
			<?php _e( 'Title', 'lifterlms' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<label class="llms-switch llms-model-status">
			<span class="llms-label"><?php _e( 'Published', 'lifterlms' ); ?></span>
			<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
			<div class="llms-switch-slider"></div>
		</label>

		<div class="llms-action-icons">

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon tip--bottom-left" data-tip="<?php esc_attr_e( 'Open WordPress lesson editor', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}" target="_blank">
					<i class="fa fa-wordpress" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php _e( 'Open WordPress lesson editor', 'lifterlms' ); ?></span>
				</a>

				<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach-model">
					<i class="fa fa-chain-broken" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php _e( 'Detach Lesson', 'lifterlms' ); ?></span>
				</a>
			<# } #>

			<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php _e( 'Delete Lesson', 'lifterlms' ); ?>" href="#llms-trash-model" tabindex="-1">
				<i class="fa fa-trash" aria-hidden="true"></i>
				<span class="screen-reader-text"><?php _e( 'Delete Lesson', 'lifterlms' ); ?></span>
			</a>

		</div>

	</header>

	<div id="llms-lesson-settings-fields"></div>

</script>
