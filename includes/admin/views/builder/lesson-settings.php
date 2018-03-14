<?php
/**
 * Builder lesson settings template
 * @since   [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-lesson-settings-template">

	<header class="llms-model-header" id="llms-lesson-header">

		<h3 class="llms-headline llms-model-title">
			<?php _e( 'Title', 'lifterlms' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<label class="llms-switch llms-model-status">
			<span class="llms-label"><?php _e( 'Published', 'lifterlms' ); ?></span>
			<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
			<div class="llms-switch-slider"></div>
		</label>

		<div class="llms-action-icons">

			<# if ( ! data.has_temp_id() ) { #>
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

	<section class="llms-model-settings active">

		<# if ( data.get( 'permalink' ) ) { #>
		<div class="llms-settings-row">
			<div class="llms-editable-toggle-group">
				<div class="llms-editable-toggle-group permalink">
					<span class="llms-label"><?php _e( 'Permalink', 'lifterlms' ); ?>:</span>
						<a target="_blank" href="{{{ data.get( 'permalink' ) }}}">{{{ data.get( 'permalink' ) }}}</a>
						<input class="llms-input permalink" data-attribute="name" data-original-content="{{{ data.get( 'name' ) }}}" data-type="permalink" name="name" type="text" value="{{{ data.get( 'name' ) }}}">
						<a class="llms-action-icon" href="#llms-edit-slug"><i class="fa fa-pencil" aria-hidden="true"></i></a>
				</div>
			</div>
		</div>
		<# } #>

	</section>

</script>
