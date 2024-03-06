<?php
/**
 * Builder utilities list view
 *
 * @since   3.16.0
 * @version 3.16.0
 */
?>
<script type="text/html" id="tmpl-llms-utilities-template">

	<ul class="llms-utilities-list">
		<li>
			<button id="llms-expand-all" class="llms-utility bulk-toggle" data-action="expand">
				<span class="fa fa-caret-down"></span>
				<?php esc_html_e( 'Expand Sections', 'lifterlms' ); ?>
			</button>
		</li>
		<li>
			<button id="llms-collapse-all" class="llms-utility bulk-toggle" href="#llms-bulk-toggle" data-action="collapse">
				<span class="fa fa-caret-up"></span>
				<?php esc_html_e( 'Collapse Sections', 'lifterlms' ); ?>
			</button>
		</li>
	</ul>

</script>
