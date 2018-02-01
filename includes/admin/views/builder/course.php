<?php
/**
 * Builder main course view
 * @since   3.16.0
 * @version 3.16.0
 */
?>
<script type="text/html" id="tmpl-llms-course-template">

	<header class="llms-builder-header">

		<h1 class="llms-headline">
			<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="{{{ data.get( 'title' ) }}}" type="text">{{{ data.get( 'title' ) }}}</span>
		</h1>

		<div class="llms-action-icons">
			<# if ( data.get_edit_post_link() ) { #>
				<a class="llms-action-icon" href="{{{ data.get_edit_post_link() }}}"><span class="fa fa-pencil"></span></a>
			<# } #>
			<a class="llms-action-icon" href="{{{ data.get( 'permalink' ) }}}"><span class="fa fa-external-link"></span></a>
		</div>

	</header>

	<section class="llms-outline" id="llms-outline">
		<div class="llms-builder-tutorial" id="llms-builder-tutorial"></div>
		<ul class="llms-sections" id="llms-sections"></ul>
	</section>

</script>
