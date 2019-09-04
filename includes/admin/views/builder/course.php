<?php
/**
 * Builder main course view
 *
 * @since   3.16.0
 * @version 3.17.8
 */
?>
<script type="text/html" id="tmpl-llms-course-template">

	<header class="llms-builder-header llms-course-header">

		<h1 class="llms-headline">
			<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required" type="text">{{{ data.get( 'title' ) }}}</span>
		</h1>

		<div class="llms-action-icons static">
			<# if ( data.get_edit_post_link() ) { #>
				<a class="llms-action-icon tip--bottom-right" data-tip="<?php esc_attr_e( 'Open WordPress course editor', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}">
					<i class="fa fa-wordpress" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php _e( 'Open WordPress course editor', 'lifterlms' ); ?></span>
				</a>
			<# } #>
			<a class="llms-action-icon tip--bottom-right" data-tip="<?php esc_attr_e( 'View course', 'lifterlms' ); ?>" href="{{{ data.get( 'permalink' ) }}}"><i class="fa fa-external-link"></i></a>
		</div>

	</header>

	<section class="llms-outline" id="llms-outline">
		<div class="llms-builder-tutorial" id="llms-builder-tutorial"></div>
		<ul class="llms-sections" id="llms-sections"></ul>
	</section>

</script>
