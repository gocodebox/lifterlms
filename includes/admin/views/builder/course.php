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
			<span data-original-content="{{{ data.get( 'title' ) }}}" data-required="required" type="text">{{{ data.get( 'title' ) }}}</span>
		</h1>

		<div class="llms-action-icons static">
			<# if ( data.get_edit_post_link() ) { #>
				<a class="llms-button-secondary small" href="{{{ data.get_edit_post_link() }}}">
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
					<?php esc_html_e( 'Edit Course Page', 'lifterlms' ); ?>
				</a>
			<# } #>
			<a class="llms-button-secondary small" href="{{{ data.get( 'permalink' ) }}}">
				<i class="fa fa-external-link"></i>
				<?php esc_html_e( 'View Course', 'lifterlms' ); ?>
			</a>
		</div>

	</header>

	<section class="llms-outline" id="llms-outline">
		<div class="llms-builder-tutorial" id="llms-builder-tutorial"></div>
		<ul class="llms-sections" id="llms-sections"></ul>
		<button class="llms-button-secondary small new-section">
			<span class="fa fa-file"></span> <?php esc_html_e( 'Add New Section', 'lifterlms' ); ?>
		</button>

	</section>

</script>
