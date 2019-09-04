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
			<a class="llms-utility bulk-toggle" href="#llms-bulk-toggle" data-action="expand" id="llms-expand-all">
				<span class="fa fa-plus-circle"></span>
				<?php _e( 'Expand All', 'lifterlms' ); ?>
			</a>
		</li>
		<li>
			<a class="llms-utility bulk-toggle" href="#llms-bulk-toggle" data-action="collapse" id="llms-collapse-all">
				<span class="fa fa-minus-circle"></span>
				<?php _e( 'Collapse All', 'lifterlms' ); ?>
			</a>
		</li>
	</ul>

</script>
